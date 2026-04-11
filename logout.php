<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap/auth.php';

logoutCurrentUser();
header('Location: index.php');
exit;
