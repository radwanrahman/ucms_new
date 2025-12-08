<?php
/**
 * Authentication Class
 * Step 3: User authentication system with login, register, and session management
 */

class Auth
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Register a new user
     */
    public function register($name, $email, $password, $role)
    {
        // Basic validation
        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        // Check if email exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $role]);
            return ['success' => true, 'message' => 'Registration successful! You can now login.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    public function login($email, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            return ['success' => true, 'message' => 'Login successful!'];
        }

        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    public function logout()
    {
        session_destroy();
        header("Location: /ucms_new/index.php");
        exit;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function getUser()
    {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header("Location: /ucms_new/views/auth/login.php");
            exit;
        }
    }
}
?>

