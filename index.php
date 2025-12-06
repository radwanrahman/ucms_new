<?php
/**
 * UCMS - Landing Page
 * Step 1: Basic landing page with navigation to login/register
 */

require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniBridge - University Course Management System</title>
    <link rel="stylesheet" href="/UCMS-Step1/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="landing-hero">
            <h1>Welcome to UniBridge</h1>
            <p>Manage courses, assignments, attendance, and grades all in one place</p>
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

