<?php
/**
 * Step 19: Logout Script
 */
require_once 'config/db.php';
require_once 'src/Auth.php';

$auth = new Auth($pdo);
$auth->logout();
?>