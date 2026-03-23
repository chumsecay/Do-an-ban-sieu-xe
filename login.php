<?php
session_start();

$_SESSION['is_logged_in'] = true;
$_SESSION['user_name'] = 'Kh�ch';

header('Location: index.php?login=ok');
exit;
