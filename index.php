<?php
/**
 * UCMS - Landing Page
 * Step 2: Basic landing page with database connection test
 */

require_once 'config/db.php';

// Test database connection
try {
    $stmt = $pdo->query("SELECT 1");
    $dbConnected = true;
} catch (PDOException $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniBridge - University Course Management System</title>
    <link rel="stylesheet" href="/ucms_new/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="landing-hero">
            <h1>Welcome to UniBridge</h1>
            <p>Manage courses, assignments, attendance, and grades all in one place</p>
            
            <?php if ($dbConnected): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: inline-block;">
                    ✓ Database connected successfully
                </div>
            <?php else: ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: inline-block;">
                    ✗ Database connection failed: <?php echo htmlspecialchars($dbError); ?>
                </div>
            <?php endif; ?>
            
            <div class="landing-actions">
                <a href="#" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem; font-weight: 600;">
                    Login
                </a>
                <a href="#" class="btn btn-outline" style="padding: 0.75rem 2rem; font-size: 1rem; font-weight: 600;">
                    Register
                </a>
            </div>
        </div>
    </div>
</body>
</html>
