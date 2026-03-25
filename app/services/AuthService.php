<?php
class AuthService
{
    private PDO $pdo;
    private string $statusSessionKey = 'STATUS';
    private int $maxAttempts = 5;
    private int $lockoutSeconds = 300; // 5 minutes

    // Role redirects (easily maintainable)
    private array $roleRedirects = [
        'admin' => '../../admin/index.php',
        'staff' => '../../staff/index.php',
        'student' => '../../students/student_dashboard.php'
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Login a user
     */
    public function login(string $email, string $password, bool $remember = false): void
    {
        $email = strtolower(trim($email));
        if (empty($email) || empty($password)) {
            $this->fail("EMPTY_FIELDS");
        }

        $this->checkLockout($email);

        $user = $this->findUser($email);

        if (!$user) {
            $this->recordFailedAttempt($email);
            $this->fail("EMAIL_NONE_EXISTENCE");
        }

        $this->verifyUser($user, $password);

        session_regenerate_id(true);

        $this->clearFailedAttempts($email);

        $this->setSession($user);

        if ($remember) {
            $this->setRememberMe($user);
        }

        $this->redirectByRole($user['role']);

        exit;
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        header("Location: ../../login/index.php");
        exit;
    }

    /**
     * Find user by email
     */
    private function findUser(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Verify password and status
     */
    private function verifyUser(array $user, string $password): void
    {
        if (strtolower(trim($user['status'])) !== 'active') {
            $this->fail("INACTIVE_ACCOUNT");
        }

        if (!password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($user['email']);
            $this->fail("LOGIN_ERROR");
        }
    }

    /**
     * Set session variables
     */
    private function setSession(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['role'];
        $_SESSION['name'] = $user['fullName'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

        // Update last login
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
    }

    /**
     * Redirect based on role
     */
    private function redirectByRole(string $role): void
    {
        if (!isset($this->roleRedirects[$role])) {
            $this->fail("UNKNOWN_ROLE");
        }

        $_SESSION[$this->statusSessionKey] = strtoupper($role) . "_LOGIN_SUCCESSFUL";

        header("Location: " . $this->roleRedirects[$role]);
        exit;
    }

    /**
     * Remember me (set cookie)
     */
    private function setRememberMe(array $user): void
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->pdo->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);
        setcookie('remember_me', $token, time() + (86400 * 30), "/", "", false, true); // 30 days
    }

    /**
     * Check lockout status
     */
    private function checkLockout(string $email): void
    {
        $stmt = $this->pdo->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $attempt = $stmt->fetch();

        if ($attempt && $attempt['attempts'] >= $this->maxAttempts) {
            $lastAttempt = strtotime($attempt['last_attempt']);
            if (time() - $lastAttempt < $this->lockoutSeconds) {
                $this->fail("TOO_MANY_ATTEMPTS");
            }
        }
    }

    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt(string $email): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO login_attempts (email, attempts, last_attempt) 
            VALUES (?, 1, NOW()) 
            ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
        $stmt->execute([$email]);
    }

    /**
     * Clear failed attempts after successful login
     */
    private function clearFailedAttempts(string $email): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
    }

    /**
     * Fail helper
     */
    private function fail(string $message): void
    {
        $_SESSION[$this->statusSessionKey] = $message;
        header("Location: ../../login/index.php");
        exit;
    }
}
