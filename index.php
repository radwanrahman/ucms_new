<?php
/**
 * UCMS - Landing Page
 * Step 6: Updated to use templates
 */

require_once 'config/db.php';
require_once 'src/Auth.php';

$auth = new Auth($pdo);

if ($auth->isLoggedIn()) {
    $user = $auth->getUser();
    header("Location: /ucms_new/views/dashboard/" . $user['role'] . ".php");
} else {
    // Landing page content
    include 'templates/header.php';
    ?>
    <div class="landing-hero">
        <h1>Welcome to UniBridge</h1>
        <p>Manage courses, assignments, attendance, and grades all in one place</p>
        <div class="landing-actions">
            <a href="/ucms_new/views/auth/login.php" class="btn btn-primary"
                style="padding: 0.75rem 2rem; font-size: 1rem; font-weight: 600;">Login</a>
            <a href="/ucms_new/views/auth/register.php" class="btn btn-outline"
                style="padding: 0.75rem 2rem; font-size: 1rem; font-weight: 600;">Register</a>
        </div>
    </div>
    <?php
    include 'templates/footer.php';
}
?>
