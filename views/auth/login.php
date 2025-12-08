<?php
/**
 * Step 4: Login Page
 */
require_once '../../config/db.php';
require_once '../../src/Auth.php';

$auth = new Auth($pdo);

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header("Location: /ucms_new/index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = $auth->login($email, $password);
    if ($result['success']) {
        header("Location: /ucms_new/index.php");
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UniBridge</title>
    <link rel="stylesheet" href="/ucms_new/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to bottom right, var(--bg-gradient-start), var(--bg-gradient-end));
            padding: 1rem;
        }
        .auth-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            width: 100%;
            max-width: 420px;
        }
        .auth-card h1 {
            margin: 0 0 0.5rem 0;
            font-size: 1.75rem;
            color: var(--text-color);
        }
        .auth-card p {
            margin: 0 0 1.5rem 0;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.35rem;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.95rem;
            font-family: var(--font-family);
        }
        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 0.75rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .switch-auth {
            margin-top: 1rem;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .switch-auth a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        .switch-auth a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Welcome Back</h1>
            <p>Login to continue to UniBridge</p>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="your.email@university.edu">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 600;">Login</button>
            </form>

            <div class="switch-auth">
                Don't have an account? <a href="/ucms_new/views/auth/register.php">Register</a>
            </div>
        </div>
    </div>
</body>
</html>

