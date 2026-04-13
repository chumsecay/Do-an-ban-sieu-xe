<?php
declare(strict_types=1);

if (!function_exists('supportStatusMap')) {
    function supportStatusMap(): array
    {
        return [
            'new' => [
                'label' => 'Moi',
                'badge' => 'bg-primary-subtle text-primary border border-primary-subtle',
            ],
            'read' => [
                'label' => 'Da doc',
                'badge' => 'bg-info-subtle text-info border border-info-subtle',
            ],
            'replied' => [
                'label' => 'Da phan hoi',
                'badge' => 'bg-success-subtle text-success border border-success-subtle',
            ],
            'spam' => [
                'label' => 'Spam',
                'badge' => 'bg-danger-subtle text-danger border border-danger-subtle',
            ],
        ];
    }
}

if (!function_exists('supportNormalizeStatus')) {
    function supportNormalizeStatus(string $status, string $default = 'new'): string
    {
        $status = trim($status);
        $allowed = array_keys(supportStatusMap());
        if (in_array($status, $allowed, true)) {
            return $status;
        }
        return in_array($default, $allowed, true) ? $default : 'new';
    }
}

if (!function_exists('supportStatusLabel')) {
    function supportStatusLabel(string $status): string
    {
        $status = supportNormalizeStatus($status);
        $map = supportStatusMap();
        return (string)($map[$status]['label'] ?? $status);
    }
}

if (!function_exists('supportStatusBadgeClass')) {
    function supportStatusBadgeClass(string $status): string
    {
        $status = supportNormalizeStatus($status);
        $map = supportStatusMap();
        return (string)($map[$status]['badge'] ?? 'bg-secondary-subtle text-secondary border border-secondary-subtle');
    }
}

if (!function_exists('supportEnsureColumn')) {
    function supportEnsureColumn(PDO $pdo, string $table, string $column, string $definition): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return;
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE " . $pdo->quote($column));
        if ($stmt && $stmt->fetch()) {
            return;
        }
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

if (!function_exists('supportEnsureIndex')) {
    function supportEnsureIndex(PDO $pdo, string $table, string $indexName, string $indexSql): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $indexName)) {
            return;
        }
        $stmt = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name = " . $pdo->quote($indexName));
        if ($stmt && $stmt->fetch()) {
            return;
        }
        $pdo->exec("ALTER TABLE `$table` ADD INDEX `$indexName` $indexSql");
    }
}

if (!function_exists('supportEnsureSchema')) {
    function supportEnsureSchema(PDO $pdo): void
    {
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS contact_messages (
                  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  customer_id BIGINT UNSIGNED NULL,
                  full_name VARCHAR(150) NOT NULL,
                  email VARCHAR(150) NOT NULL,
                  phone VARCHAR(30) NULL,
                  subject VARCHAR(180) NULL,
                  message TEXT NOT NULL,
                  status ENUM('new','read','replied','spam') NOT NULL DEFAULT 'new',
                  admin_reply TEXT NULL,
                  replied_at DATETIME NULL,
                  replied_by_admin_id BIGINT UNSIGNED NULL,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  KEY idx_contact_messages_status (status),
                  KEY idx_contact_messages_created_at (created_at),
                  KEY idx_contact_messages_customer (customer_id)
                ) ENGINE=InnoDB
            ");

            supportEnsureColumn($pdo, 'contact_messages', 'customer_id', 'BIGINT UNSIGNED NULL AFTER id');
            supportEnsureColumn($pdo, 'contact_messages', 'admin_reply', 'TEXT NULL AFTER status');
            supportEnsureColumn($pdo, 'contact_messages', 'replied_at', 'DATETIME NULL AFTER admin_reply');
            supportEnsureColumn($pdo, 'contact_messages', 'replied_by_admin_id', 'BIGINT UNSIGNED NULL AFTER replied_at');
            supportEnsureIndex($pdo, 'contact_messages', 'idx_contact_messages_customer', '(customer_id)');
        } catch (Throwable $ignored) {
            // Keep app usable when DB account has no ALTER privilege.
        }
    }
}

