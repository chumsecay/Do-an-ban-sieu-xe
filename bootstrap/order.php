<?php
declare(strict_types=1);

require_once __DIR__ . '/text.php';

if (!function_exists('orderStatusMap')) {
    function orderStatusMap(): array
    {
        return [
            'pending' => [
                'label' => 'Chờ duyệt',
                'badge' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            ],
            'confirmed' => [
                'label' => 'Đã xác nhận',
                'badge' => 'bg-primary-subtle text-primary border border-primary-subtle',
            ],
            'processing' => [
                'label' => 'Đang xử lý',
                'badge' => 'bg-warning-subtle text-warning border border-warning-subtle',
            ],
            'shipping' => [
                'label' => 'Đang giao xe',
                'badge' => 'bg-info-subtle text-info border border-info-subtle',
            ],
            'delivered' => [
                'label' => 'Đã giao xe',
                'badge' => 'bg-success-subtle text-success border border-success-subtle',
            ],
            'completed' => [
                'label' => 'Hoàn tất',
                'badge' => 'bg-success-subtle text-success border border-success-subtle',
            ],
            'cancelled' => [
                'label' => 'Đã hủy',
                'badge' => 'bg-danger-subtle text-danger border border-danger-subtle',
            ],
            'refunded' => [
                'label' => 'Đã hoàn tiền',
                'badge' => 'bg-dark-subtle text-dark border border-dark-subtle',
            ],
        ];
    }
}

if (!function_exists('orderAllowedStatuses')) {
    function orderAllowedStatuses(): array
    {
        return array_keys(orderStatusMap());
    }
}

if (!function_exists('normalizeOrderStatus')) {
    function normalizeOrderStatus(string $status, string $default = 'pending'): string
    {
        $status = trim($status);
        $allowed = orderAllowedStatuses();
        if (in_array($status, $allowed, true)) {
            return $status;
        }

        return in_array($default, $allowed, true) ? $default : 'pending';
    }
}

if (!function_exists('orderStatusLabel')) {
    function orderStatusLabel(string $status): string
    {
        $status = normalizeOrderStatus($status);
        $map = orderStatusMap();
        return (string)($map[$status]['label'] ?? $status);
    }
}

if (!function_exists('orderStatusBadgeClass')) {
    function orderStatusBadgeClass(string $status): string
    {
        $status = normalizeOrderStatus($status);
        $map = orderStatusMap();
        return (string)($map[$status]['badge'] ?? 'bg-secondary-subtle text-secondary border border-secondary-subtle');
    }
}

if (!function_exists('orderPendingStatuses')) {
    function orderPendingStatuses(): array
    {
        return ['pending', 'confirmed', 'processing', 'shipping'];
    }
}

if (!function_exists('orderRevenueStatuses')) {
    function orderRevenueStatuses(): array
    {
        return ['confirmed', 'processing', 'shipping', 'delivered', 'completed'];
    }
}

if (!function_exists('orderFulfilledStatuses')) {
    function orderFulfilledStatuses(): array
    {
        return ['delivered', 'completed'];
    }
}

if (!function_exists('orderParseEnumValues')) {
    function orderParseEnumValues(string $columnType): array
    {
        if (!preg_match('/^enum\((.*)\)$/i', trim($columnType), $m)) {
            return [];
        }

        $list = $m[1] ?? '';
        if ($list === '') {
            return [];
        }

        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $list, $matches);
        if (empty($matches[1])) {
            return [];
        }

        return array_map(
            static fn(string $value): string => str_replace("\\'", "'", $value),
            $matches[1]
        );
    }
}

if (!function_exists('ensureOrderStatusEnumColumn')) {
    function ensureOrderStatusEnumColumn(PDO $pdo, string $table, string $column, bool $nullable, string $default = 'pending'): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return;
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE " . $pdo->quote($column));
        $col = $stmt ? $stmt->fetch() : false;
        if (!$col) {
            return;
        }

        $currentValues = orderParseEnumValues((string)($col['Type'] ?? ''));
        if (!$currentValues) {
            return;
        }

        $mergedValues = $currentValues;
        foreach (orderAllowedStatuses() as $status) {
            if (!in_array($status, $mergedValues, true)) {
                $mergedValues[] = $status;
            }
        }

        if ($mergedValues === $currentValues) {
            return;
        }

        $enumSql = implode(
            ', ',
            array_map(
                static fn(string $value): string => "'" . str_replace("'", "''", $value) . "'",
                $mergedValues
            )
        );

        $nullSql = $nullable ? 'NULL' : 'NOT NULL';
        if ($nullable) {
            $defaultSql = 'DEFAULT NULL';
        } else {
            $defaultValue = normalizeOrderStatus((string)($col['Default'] ?? ''), $default);
            $defaultSql = "DEFAULT '" . str_replace("'", "''", $defaultValue) . "'";
        }

        $pdo->exec("ALTER TABLE `$table` MODIFY COLUMN `$column` ENUM($enumSql) $nullSql $defaultSql");
    }
}

if (!function_exists('ensureOrderStatusSchema')) {
    function ensureOrderStatusSchema(PDO $pdo): void
    {
        try {
            ensureOrderStatusEnumColumn($pdo, 'orders', 'status', false, 'pending');
            ensureOrderStatusEnumColumn($pdo, 'order_status_logs', 'old_status', true, 'pending');
            ensureOrderStatusEnumColumn($pdo, 'order_status_logs', 'new_status', false, 'pending');
        } catch (Throwable $ignored) {
            // Keep pages functional when DB account has no ALTER privilege.
        }
    }
}
