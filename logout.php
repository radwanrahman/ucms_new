<?php
/**
 * Step 6: Logout functionality
 */
require_once 'config/db.php';
require_once 'src/Auth.php';

$auth = new Auth($pdo);
$auth->logout();
?>

