<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniBridge - Course Management</title>
    <link rel="stylesheet" href="/ucms_new/public/css/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar">
        <div class="container">
            <a href="/ucms_new/views/dashboard/<?php echo $_SESSION['user_role']; ?>.php" class="logo">UniBridge</a>
            <ul class="nav-links">
                <li><a href="/ucms_new/views/dashboard/<?php echo $_SESSION['user_role']; ?>.php">Dashboard</a></li>
                <li>
                    <span style="font-size: 0.875rem; color: var(--text-secondary);">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                </li>
                <li>
                    <span class="user-badge"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
                </li>
                <li>
                    <a href="/ucms_new/logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
    <div class="main-content container">

