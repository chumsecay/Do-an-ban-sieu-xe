<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/env.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../bootstrap/shop.php';
require_once __DIR__ . '/../config/database.php';

ensureSessionStarted();

if (!isUserLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentPage = 'cart';
$appName = env('APP_NAME', 'FLCar');
$pdo = getDBConnection();

$msg = (string)($_GET['msg'] ?? '');

function redirectCart(string $msg = ''): void
{
    $url = 'cart.php';
    if ($msg !== '') {
        $url .= '?msg=' . urlencode($msg);
    }
    header('Location: ' . $url);
    exit;
}

$action = (string)($_POST['action'] ?? ($_GET['action'] ?? ''));

if ($action !== '') {
    $carId = (int)($_POST['car_id'] ?? ($_GET['car_id'] ?? 0));

    if ($action === 'add') {
        if ($carId <= 0) {
            redirectCart('invalid_item');
        }
        $qty = max(1, (int)($_POST['qty'] ?? ($_GET['qty'] ?? 1)));
        shopAddCartItem($carId, $qty);
        redirectCart('added');
    }

    if ($action === 'quick_buy') {
        if ($carId <= 0) {
            redirectCart('invalid_item');
        }
        shopClearCart();
        shopAddCartItem($carId, 1);
        redirectCart('quick_buy_ready');
    }

    if ($action === 'update_qty') {
        if ($carId <= 0) {
            redirectCart('invalid_item');
        }
        $qty = max(0, (int)($_POST['qty'] ?? 0));
        if ($qty <= 0) {
            shopRemoveCartItem($carId);
        } else {
            shopSetCartItem($carId, $qty);
        }
        redirectCart('qty_updated');
    }

    if ($action === 'remove') {
        if ($carId > 0) {
            shopRemoveCartItem($carId);
        }
        redirectCart('item_removed');
    }

    if ($action === 'clear') {
        shopClearCart();
        redirectCart('cleared');
    }

    if ($action === 'checkout') {
        $paymentMethod = (string)($_POST['payment_method'] ?? 'cod');
        $shippingInput = [
            'full_name' => trim((string)($_POST['shipping_full_name'] ?? '')),
            'phone' => trim((string)($_POST['shipping_phone'] ?? '')),
            'address' => trim((string)($_POST['shipping_address'] ?? '')),
            'note' => trim((string)($_POST['shipping_note'] ?? '')),
        ];
        $result = shopCheckoutCart($pdo, $paymentMethod, $shippingInput);
        if (!$result['ok']) {
            redirectCart((string)($result['code'] ?? 'db_error'));
        }
        header('Location: orders.php?msg=checkout_success');
        exit;
    }
}

$customer = shopGetCurrentCustomer($pdo, true);
$cartData = shopBuildCartDetail($pdo);
$cartItems = $cartData['items'];
$subtotal = (float)$cartData['subtotal'];
$totalQty = (int)$cartData['total_qty'];
$cartHasIssue = (bool)$cartData['has_issue'];
$userBalance = (float)($_SESSION['user_balance'] ?? 0);
$defaultShippingName = trim((string)($customer['full_name'] ?? ($_SESSION['user_name'] ?? '')));
$defaultShippingPhone = trim((string)($customer['phone'] ?? ''));

$alertMap = [
    'added' => ['success', 'Đã thêm sản phẩm vào giỏ hàng.'],
    'quick_buy_ready' => ['success', 'Đã sẵn sàng mua ngày. Bạn có thể checkout bên dưới.'],
    'qty_updated' => ['info', 'Đã cập nhật số lượng trong giỏ hàng.'],
    'item_removed' => ['warning', 'Đã xóa sản phẩm khỏi giỏ hàng.'],
    'cleared' => ['warning', 'Đã xóa toàn bộ giỏ hàng.'],
    'invalid_item' => ['danger', 'Sản phẩm không hợp lệ.'],
    'empty_cart' => ['danger', 'Giỏ hàng đang trống.'],
    'item_unavailable' => ['danger', 'Có sản phẩm không còn sẵn sàng bán.'],
    'invalid_qty' => ['danger', 'Số lượng không hợp lệ với tồn kho hiện tại.'],
    'insufficient_balance' => ['danger', 'Số dư không đủ để thanh toán bằng ví.'],
    'stock_changed' => ['danger', 'Tồn kho đã thay đổi, vui lòng kiểm tra lại giỏ hàng.'],
    'customer_missing' => ['danger', 'Không tìm thấy hồ sơ khách hàng để tạo đơn hàng.'],
    'shipping_name_required' => ['danger', 'Vui long nhap ten nguoi nhan.'],
    'shipping_phone_required' => ['danger', 'Vui long nhap so dien thoai nguoi nhan.'],
    'shipping_phone_invalid' => ['danger', 'So dien thoai nguoi nhan khong hop le.'],
    'shipping_address_required' => ['danger', 'Vui long nhap dia chi giao xe.'],
    'shipping_address_too_long' => ['danger', 'Dia chi giao xe qua dai (toi da 255 ky tu).'],
    'shipping_note_too_long' => ['danger', 'Ghi chu giao hang qua dai (toi da 255 ky tu).'],
    'db_error' => ['danger', 'Có lỗi CSDL khi xử lý giỏ hàng.'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Giỏ Hàng - <?php echo htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/flcar-common.css?v=12" rel="stylesheet">
<link rel="icon" href="../img/logo.png" type="image/png">
<style>
  .cart-thumb { width: 84px; height: 62px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; }
  .summary-card { border-radius: 16px; background: #fff; border: 1px solid #e2e8f0; }
  .status-pill { font-size: .78rem; font-weight: 700; padding: .3rem .65rem; border-radius: 999px; }
</style>
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-banner">
  <div class="container">
    <h1 class="display-5 fw-bold">Giỏ Hàng</h1>
    <p class="lead mb-0" style="opacity:.85">Quản lý xe muốn mua và tiến hành đặt hàng online</p>
  </div>
</section>

<section style="padding:60px 0; background:#f8fafc;">
  <div class="container">
    <?php if ($msg !== '' && isset($alertMap[$msg])): $a = $alertMap[$msg]; ?>
      <div class="alert alert-<?php echo $a[0]; ?> border-0 rounded-3"><?php echo $a[1]; ?></div>
    <?php endif; ?>

    <?php if (!$customer): ?>
      <div class="alert alert-warning border-0 rounded-3">Không thể xác định hồ sơ khách hàng để mua hàng. Vui lòng đăng nhập lại bằng tài khoản người dùng.</div>
    <?php endif; ?>

    <?php if (!$cartItems): ?>
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-5 text-center">
          <h3 class="h4 fw-bold mb-3">Giỏ hàng đang trống</h3>
          <p class="text-muted mb-4">Bạn có thể thêm xe từ trang Showroom để theo dõi và mua online.</p>
          <a href="showroom.php" class="btn btn-primary px-4">Đi đến Showroom</a>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr class="text-uppercase text-secondary bg-light" style="font-size:.75rem;letter-spacing:.5px;">
                      <th class="ps-4">Sản phẩm</th>
                      <th>Đơn giá</th>
                      <th>Số lượng</th>
                      <th>Thành tiền</th>
                      <th class="text-end pe-4">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($cartItems as $item): ?>
                      <?php
                        $img = $item['cover_image'] !== '' ? htmlspecialchars($item['cover_image'], ENT_QUOTES, 'UTF-8') : '../img/bmwx5.jpg';
                        $isAvailable = (bool)$item['is_available'];
                      ?>
                      <tr>
                        <td class="ps-4">
                          <div class="d-flex align-items-center gap-3">
                            <img src="<?php echo $img; ?>" class="cart-thumb" alt="car">
                            <div>
                              <div class="fw-bold"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                              <div class="small text-secondary">
                                <?php echo htmlspecialchars($item['brand_name'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo (int)$item['model_year']; ?>
                              </div>
                              <div class="small text-secondary">Tồn kho: <?php echo (int)$item['stock_quantity']; ?></div>
                              <?php if (!$isAvailable): ?>
                                <span class="status-pill bg-secondary-subtle text-secondary border border-secondary-subtle">Hết hàng / không bán</span>
                              <?php elseif ($item['has_issue']): ?>
                                <span class="status-pill bg-warning-subtle text-warning border border-warning-subtle">Số lượng vượt tồn kho</span>
                              <?php else: ?>
                                <span class="status-pill bg-success-subtle text-success border border-success-subtle">Sẵn sàng mua</span>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                        <td class="fw-semibold">$<?php echo number_format((float)$item['price'], 2); ?></td>
                        <td>
                          <form method="POST" class="d-flex gap-2 align-items-center">
                            <input type="hidden" name="action" value="update_qty">
                            <input type="hidden" name="car_id" value="<?php echo (int)$item['car_id']; ?>">
                            <input type="number" min="0" max="<?php echo max(0, (int)$item['stock_quantity']); ?>" name="qty" value="<?php echo (int)$item['requested_qty']; ?>" class="form-control form-control-sm" style="width:90px;">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Lưu</button>
                          </form>
                        </td>
                        <td class="fw-bold">$<?php echo number_format((float)$item['line_total'], 2); ?></td>
                        <td class="text-end pe-4">
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="car_id" value="<?php echo (int)$item['car_id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="mt-3 d-flex gap-2">
            <a href="showroom.php" class="btn btn-outline-secondary">Tiếp tục mua</a>
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="clear">
              <button type="submit" class="btn btn-outline-danger">Xóa toàn bộ giỏ</button>
            </form>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="summary-card p-4 shadow-sm">
            <h4 class="h5 fw-bold mb-3">Tổng thanh toán</h4>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-secondary">Tổng số lượng</span>
              <strong><?php echo $totalQty; ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-secondary">Tạm tính</span>
              <strong>$<?php echo number_format($subtotal, 2); ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span class="text-secondary">Số dư ví</span>
              <strong>$<?php echo number_format($userBalance, 2); ?></strong>
            </div>
            <hr>
            <form method="POST" class="d-grid gap-3">
              <input type="hidden" name="action" value="checkout">
              <div>
                <label class="form-label small fw-bold text-secondary">Nguoi nhan</label>
                <input type="text" name="shipping_full_name" class="form-control bg-light border-0" required
                       value="<?php echo htmlspecialchars($defaultShippingName, ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div>
                <label class="form-label small fw-bold text-secondary">So dien thoai nhan hang</label>
                <input type="text" name="shipping_phone" class="form-control bg-light border-0" required
                       value="<?php echo htmlspecialchars($defaultShippingPhone, ENT_QUOTES, 'UTF-8'); ?>">
              </div>
              <div>
                <label class="form-label small fw-bold text-secondary">Dia chi giao xe</label>
                <input type="text" name="shipping_address" class="form-control bg-light border-0" required
                       placeholder="So nha, duong, phuong/xa, quan/huyen, tinh/thanh">
              </div>
              <div>
                <label class="form-label small fw-bold text-secondary">Ghi chu giao hang</label>
                <textarea name="shipping_note" class="form-control bg-light border-0" rows="2" maxlength="255"
                          placeholder="Thoi gian mong muon, ghi chu them (khong bat buoc)"></textarea>
              </div>
              <div>
                <label class="form-label small fw-bold text-secondary">Phương thức thanh toán</label>
                <select name="payment_method" class="form-select bg-light border-0">
                  <option value="cod">Thanh toán khi xác nhận đơn</option>
                  <option value="wallet">Trừ trực tiếp số dư ví</option>
                </select>
              </div>

              <?php if ($cartHasIssue): ?>
                <div class="alert alert-warning mb-0">
                  Giỏ hàng đang có sản phẩm chưa hợp lệ với tồn kho. Vui lòng chỉnh lại trước khi đặt hàng.
                </div>
              <?php endif; ?>

              <button type="submit" class="btn btn-primary fw-bold py-2" <?php echo $cartHasIssue ? 'disabled' : ''; ?>>
                Đặt hàng ngày
              </button>
            </form>
            <div class="small text-secondary mt-3">
              Sau khi đặt hàng, đơn sẽ hiển thị trong mục <a href="orders.php" class="text-decoration-none">Đơn mua</a>.
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
