<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (!function_exists('shopEnsureCartStorage')) {
    function shopEnsureCartStorage(): void
    {
        ensureSessionStarted();
        if (!isset($_SESSION['shop_cart']) || !is_array($_SESSION['shop_cart'])) {
            $_SESSION['shop_cart'] = [];
        }
    }
}

if (!function_exists('shopGetCart')) {
    function shopGetCart(): array
    {
        shopEnsureCartStorage();
        $cart = [];
        foreach ((array)$_SESSION['shop_cart'] as $carId => $qty) {
            $id = (int)$carId;
            $q = (int)$qty;
            if ($id > 0 && $q > 0) {
                $cart[$id] = $q;
            }
        }
        $_SESSION['shop_cart'] = $cart;
        return $cart;
    }
}

if (!function_exists('shopSetCartItem')) {
    function shopSetCartItem(int $carId, int $qty): void
    {
        if ($carId <= 0) {
            return;
        }
        shopEnsureCartStorage();
        if ($qty <= 0) {
            unset($_SESSION['shop_cart'][(string)$carId]);
            unset($_SESSION['shop_cart'][$carId]);
            return;
        }
        $_SESSION['shop_cart'][(string)$carId] = $qty;
    }
}

if (!function_exists('shopAddCartItem')) {
    function shopAddCartItem(int $carId, int $qty = 1, bool $replace = false): void
    {
        if ($carId <= 0) {
            return;
        }
        $qty = max(1, $qty);
        $cart = shopGetCart();
        if ($replace) {
            shopSetCartItem($carId, $qty);
            return;
        }
        $current = (int)($cart[$carId] ?? 0);
        shopSetCartItem($carId, $current + $qty);
    }
}

if (!function_exists('shopRemoveCartItem')) {
    function shopRemoveCartItem(int $carId): void
    {
        shopSetCartItem($carId, 0);
    }
}

if (!function_exists('shopClearCart')) {
    function shopClearCart(): void
    {
        shopEnsureCartStorage();
        $_SESSION['shop_cart'] = [];
    }
}

if (!function_exists('shopCartItemCount')) {
    function shopCartItemCount(): int
    {
        $count = 0;
        foreach (shopGetCart() as $qty) {
            $count += (int)$qty;
        }
        return $count;
    }
}

if (!function_exists('shopEnsureCarsStockColumn')) {
    function shopEnsureCarsStockColumn(PDO $pdo): void
    {
        $check = $pdo->query("SHOW COLUMNS FROM cars LIKE 'stock_quantity'");
        if (!$check->fetch()) {
            $pdo->exec("ALTER TABLE cars ADD COLUMN stock_quantity INT UNSIGNED NOT NULL DEFAULT 1 AFTER price");
        }
    }
}

if (!function_exists('shopEnsureCustomersBalanceColumn')) {
    function shopEnsureCustomersBalanceColumn(PDO $pdo): void
    {
        $check = $pdo->query("SHOW COLUMNS FROM customers LIKE 'balance'");
        if (!$check->fetch()) {
            $pdo->exec("ALTER TABLE customers ADD COLUMN balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER tier");
        }
    }
}

if (!function_exists('shopGetCurrentCustomer')) {
    function shopGetCurrentCustomer(PDO $pdo, bool $autoCreate = true): ?array
    {
        ensureSessionStarted();
        if ((string)($_SESSION['user_role'] ?? '') !== 'user') {
            return null;
        }

        shopEnsureCustomersBalanceColumn($pdo);

        $customerId = (int)($_SESSION['customer_id'] ?? 0);
        if ($customerId > 0) {
            $stmt = $pdo->prepare('SELECT id, full_name, email, balance FROM customers WHERE id = ? LIMIT 1');
            $stmt->execute([$customerId]);
            $row = $stmt->fetch();
            if ($row) {
                $_SESSION['user_balance'] = (float)($row['balance'] ?? 0);
                return $row;
            }
        }

        $email = trim((string)($_SESSION['user_email'] ?? ''));
        $fullName = trim((string)($_SESSION['user_name'] ?? 'Khách hàng'));
        if ($email === '') {
            return null;
        }

        $find = $pdo->prepare('SELECT id, full_name, email, balance FROM customers WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $find->execute([$email]);
        $row = $find->fetch();
        if ($row) {
            $_SESSION['customer_id'] = (int)$row['id'];
            $_SESSION['user_balance'] = (float)($row['balance'] ?? 0);
            return $row;
        }

        if (!$autoCreate) {
            return null;
        }

        $insert = $pdo->prepare('INSERT INTO customers (full_name, email, tier, balance) VALUES (?, ?, ?, 0)');
            $insert->execute([$fullName !== '' ? $fullName : 'Khách hàng', $email, 'new']);
        $newId = (int)$pdo->lastInsertId();

        $_SESSION['customer_id'] = $newId;
        $_SESSION['user_balance'] = 0.0;

        return [
            'id' => $newId,
                'full_name' => $fullName !== '' ? $fullName : 'Khách hàng',
            'email' => $email,
            'balance' => 0.0,
        ];
    }
}

if (!function_exists('shopNextOrderNo')) {
    function shopNextOrderNo(PDO $pdo): string
    {
        $row = $pdo->query('SELECT order_no FROM orders WHERE order_no LIKE "DH-%" ORDER BY id DESC LIMIT 1')->fetch();
        if (!$row || empty($row['order_no'])) {
            return 'DH-000001';
        }
        $num = (int)preg_replace('/[^0-9]/', '', (string)$row['order_no']);
        return 'DH-' . str_pad((string)($num + 1), 6, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('shopBuildCartDetail')) {
    function shopBuildCartDetail(PDO $pdo): array
    {
        shopEnsureCarsStockColumn($pdo);
        $cart = shopGetCart();
        if (!$cart) {
            return [
                'items' => [],
                'subtotal' => 0.0,
                'total_qty' => 0,
                'has_issue' => false,
            ];
        }

        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
            SELECT
                c.id, c.name, c.model_year, c.price, c.status, c.stock_quantity,
                b.name AS brand_name,
                (SELECT image_url FROM car_images ci WHERE ci.car_id = c.id AND ci.is_cover = 1 ORDER BY ci.id ASC LIMIT 1) AS cover_image
            FROM cars c
            LEFT JOIN brands b ON b.id = c.brand_id
            WHERE c.id IN ($placeholders)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();
        $cars = [];
        foreach ($rows as $r) {
            $cars[(int)$r['id']] = $r;
        }

        $items = [];
        $subtotal = 0.0;
        $totalQty = 0;
        $hasIssue = false;

        foreach ($cart as $carId => $qty) {
            $car = $cars[$carId] ?? null;
            if (!$car) {
                $hasIssue = true;
                continue;
            }

            $stock = max(0, (int)($car['stock_quantity'] ?? 0));
            $status = (string)($car['status'] ?? 'sold');
            $isAvailable = $status === 'available' && $stock > 0;
            $effectiveQty = min($qty, max($stock, 1));
            $lineTotal = $isAvailable ? ((float)$car['price'] * $effectiveQty) : 0.0;
            $issue = !$isAvailable || $effectiveQty !== $qty;

            if ($issue) {
                $hasIssue = true;
            }

            $subtotal += $lineTotal;
            $totalQty += $isAvailable ? $effectiveQty : 0;

            $items[] = [
                'car_id' => (int)$car['id'],
                'name' => (string)$car['name'],
                'brand_name' => (string)($car['brand_name'] ?? ''),
                'model_year' => (int)($car['model_year'] ?? 0),
                'price' => (float)$car['price'],
                'status' => $status,
                'stock_quantity' => $stock,
                'requested_qty' => (int)$qty,
                'effective_qty' => (int)$effectiveQty,
                'line_total' => $lineTotal,
                'cover_image' => (string)($car['cover_image'] ?? ''),
                'is_available' => $isAvailable,
                'has_issue' => $issue,
            ];
        }

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'total_qty' => $totalQty,
            'has_issue' => $hasIssue,
        ];
    }
}

if (!function_exists('shopCheckoutCart')) {
    function shopCheckoutCart(PDO $pdo, string $paymentMethod = 'cod'): array
    {
        $paymentMethod = in_array($paymentMethod, ['wallet', 'cod'], true) ? $paymentMethod : 'cod';
        $cart = shopBuildCartDetail($pdo);
        $items = $cart['items'];
        if (!$items) {
            return ['ok' => false, 'code' => 'empty_cart', 'message' => 'Giỏ hàng đang trống.'];
        }

        foreach ($items as $item) {
            if (!$item['is_available']) {
                return ['ok' => false, 'code' => 'item_unavailable', 'message' => 'Có sản phẩm đã hết hàng/không sẵn sàng bán.'];
            }
            if ($item['effective_qty'] <= 0 || $item['effective_qty'] !== $item['requested_qty']) {
                return ['ok' => false, 'code' => 'invalid_qty', 'message' => 'Số lượng sản phẩm trong giỏ hàng không hợp lệ với tồn kho hiện tại.'];
            }
        }

        $customer = shopGetCurrentCustomer($pdo, true);
        if (!$customer) {
            return ['ok' => false, 'code' => 'customer_missing', 'message' => 'Không tìm thấy tài khoản khách hàng để tạo đơn.'];
        }

        $totalAmount = (float)$cart['subtotal'];
        $createdOrders = 0;

        try {
            $pdo->beginTransaction();

            if ($paymentMethod === 'wallet') {
                $lockCustomer = $pdo->prepare('SELECT id, balance FROM customers WHERE id = ? FOR UPDATE');
                $lockCustomer->execute([(int)$customer['id']]);
                $customerRow = $lockCustomer->fetch();
                if (!$customerRow) {
                    $pdo->rollBack();
                    return ['ok' => false, 'code' => 'customer_missing', 'message' => 'Không tìm thấy tài khoản khách hàng.'];
                }
                $currentBalance = (float)($customerRow['balance'] ?? 0);
                if ($currentBalance < $totalAmount) {
                    $pdo->rollBack();
                    return ['ok' => false, 'code' => 'insufficient_balance', 'message' => 'Số dư không đủ để thanh toán đơn hàng.'];
                }
            }

            foreach ($items as $item) {
                $carLock = $pdo->prepare('SELECT id, price, status, stock_quantity FROM cars WHERE id = ? FOR UPDATE');
                $carLock->execute([(int)$item['car_id']]);
                $carRow = $carLock->fetch();
                if (!$carRow) {
                    $pdo->rollBack();
                    return ['ok' => false, 'code' => 'car_missing', 'message' => 'Xe trong giỏ hàng không còn tồn tại.'];
                }

                $stock = (int)($carRow['stock_quantity'] ?? 0);
                $status = (string)($carRow['status'] ?? 'sold');
                $qty = (int)$item['effective_qty'];
                if ($status !== 'available' || $stock < $qty || $qty <= 0) {
                    $pdo->rollBack();
                    return ['ok' => false, 'code' => 'stock_changed', 'message' => 'Tồn kho đã thay đổi. Vui lòng cập nhật giỏ hàng và thử lại.'];
                }

                $unitPrice = (float)($carRow['price'] ?? 0);
                $lineTotal = round($unitPrice * $qty, 2);
                $orderNo = shopNextOrderNo($pdo);
                $orderStatus = $paymentMethod === 'wallet' ? 'confirmed' : 'pending';
                $paymentStatus = $paymentMethod === 'wallet' ? 'paid' : 'unpaid';

                $insertOrder = $pdo->prepare('
                    INSERT INTO orders
                    (order_no, customer_id, car_id, order_type, quantity, unit_price, total_amount, status, payment_status, note, created_by_admin_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)
                ');
                $insertOrder->execute([
                    $orderNo,
                    (int)$customer['id'],
                    (int)$item['car_id'],
                    'purchase',
                    $qty,
                    $unitPrice,
                    $lineTotal,
                    $orderStatus,
                    $paymentStatus,
                    'Checkout tu website',
                ]);

                $newStock = max(0, $stock - $qty);
                $newStatus = $newStock <= 0 ? 'sold' : 'available';
                $updateCar = $pdo->prepare('UPDATE cars SET stock_quantity = ?, status = ? WHERE id = ?');
                $updateCar->execute([$newStock, $newStatus, (int)$item['car_id']]);

                $createdOrders++;
            }

            if ($paymentMethod === 'wallet') {
                $deduct = $pdo->prepare('UPDATE customers SET balance = balance - ? WHERE id = ?');
                $deduct->execute([$totalAmount, (int)$customer['id']]);
                $_SESSION['user_balance'] = max(0.0, (float)($_SESSION['user_balance'] ?? 0) - $totalAmount);
            }

            $pdo->commit();
            shopClearCart();

            return [
                'ok' => true,
                'code' => 'success',
                'message' => 'Tạo đơn hàng thành công.',
                'created_orders' => $createdOrders,
                'total_amount' => $totalAmount,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'code' => 'db_error', 'message' => 'Không thể checkout do lỗi CSDL.'];
        }
    }
}

