<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT UserID, Username, Email, FirstName, LastName,
                Role, AccountStatus
         FROM users WHERE UserID = ?'
    );

    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['AccountStatus'] !== 'Active') {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

function require_login(): array
{
    $user = current_user();

    if (!$user) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

function require_admin(): array
{
    $user = require_login();

    if ($user['Role'] !== 'Admin') {
        http_response_code(403);
        exit('Access denied: administrators only.');
    }

    return $user;
}

function login_user(string $username, string $password): bool
{
    $stmt = db()->prepare(
        'SELECT * FROM users
         WHERE (Username = ? OR Email = ?)
         AND AccountStatus = ?'
    );

    $stmt->execute([$username, $username, 'Active']);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['PasswordHash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['UserID'];
    $_SESSION['role'] = strtolower($user['Role']);

    return true;
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    if (
        !isset($_POST['csrf_token']) ||
        !is_string($_POST['csrf_token']) ||
        !hash_equals(csrf_token(), $_POST['csrf_token'])
    ) {
        http_response_code(403);
        exit('Invalid security token.');
    }
}
