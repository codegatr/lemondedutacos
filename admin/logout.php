<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
log_activity('logout', $_SESSION['admin_id'] ?? null);
admin_logout();
header('Location: login.php');
