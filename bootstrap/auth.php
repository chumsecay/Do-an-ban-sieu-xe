<?php

declare(strict_types=1);

if (!function_exists('ensureSessionStarted')) {
    function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}

if (!function_exists('isUserLoggedIn')) {
    function isUserLoggedIn(): bool
    {
        ensureSessionStarted();
        return !empty($_SESSION['is_logged_in']);
    }
}

if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn(): bool
    {
        ensureSessionStarted();
        return !empty($_SESSION['is_logged_in']) && ($_SESSION['user_role'] ?? '') === 'admin';
    }
}

if (!function_exists('loginAsAdmin')) {
    function loginAsAdmin(array $admin): void
    {
        ensureSessionStarted();

        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_name'] = $admin['full_name'] ?? ($admin['username'] ?? 'Admin');
        $_SESSION['user_email'] = $admin['email'] ?? '';
        $_SESSION['auth_provider'] = 'password';

        // Backward-compatible keys used by existing code.
        $_SESSION['is_admin_logged_in'] = true;
        $_SESSION['admin_id'] = (int)($admin['id'] ?? 0);
        $_SESSION['admin_name'] = $admin['full_name'] ?? ($admin['username'] ?? 'Admin');
        $_SESSION['admin_role'] = $admin['role'] ?? 'super_admin';
    }
}

if (!function_exists('loginAsUser')) {
    function loginAsUser(string $name, string $email, string $provider = 'google'): void
    {
        ensureSessionStarted();

        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_role'] = 'user';
        $_SESSION['user_name'] = $name !== '' ? $name : $email;
        $_SESSION['user_email'] = $email;
        $_SESSION['auth_provider'] = $provider;

        $_SESSION['is_admin_logged_in'] = false;
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
    }
}

if (!function_exists('logoutCurrentUser')) {
    function logoutCurrentUser(): void
    {
        ensureSessionStarted();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
