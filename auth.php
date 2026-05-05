<?php
require_once __DIR__ . '/db.php';

define('RATE_LIMIT_MAX',    5);    // tentatives avant blocage
define('RATE_LIMIT_WINDOW', 900);  // durée du blocage en secondes (15 min)

function get_client_ip() {
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

function rate_limit_check($ip) {
    try {
        $stmt = db()->prepare('SELECT attempts, last_attempt FROM login_attempts WHERE ip = ?');
        $stmt->execute([$ip]);
        $row = $stmt->fetch();
        if (!$row) return true;
        $elapsed = time() - strtotime($row['last_attempt']);
        // fenêtre expirée : on repart à zéro
        if ($elapsed >= RATE_LIMIT_WINDOW) {
            db()->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
            return true;
        }
        return $row['attempts'] < RATE_LIMIT_MAX;
    } catch (PDOException $e) {
        return true; // si la table n'existe pas encore, on laisse passer
    }
}

function rate_limit_fail($ip) {
    try {
        db()->prepare('
            INSERT INTO login_attempts (ip, attempts, last_attempt)
            VALUES (?, 1, NOW())
            ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()
        ')->execute([$ip]);
    } catch (PDOException $e) {}
}

function rate_limit_reset($ip) {
    try {
        db()->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
    } catch (PDOException $e) {}
}

function rate_limit_remaining_seconds($ip) {
    try {
        $stmt = db()->prepare('SELECT last_attempt FROM login_attempts WHERE ip = ?');
        $stmt->execute([$ip]);
        $row = $stmt->fetch();
        if (!$row) return 0;
        $elapsed = time() - strtotime($row['last_attempt']);
        return max(0, RATE_LIMIT_WINDOW - $elapsed);
    } catch (PDOException $e) {
        return 0;
    }
}

function session_start_safe() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function current_user() {
    session_start_safe();
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return current_user() !== null;
}

function is_admin() {
    $u = current_user();
    return $u !== null && $u['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin() {
    if (!is_admin()) {
        http_response_code(403);
        include __DIR__ . '/header.php';
        echo '<main class="container"><div class="alert alert--error">Accès réservé aux administrateurs.</div></main>';
        include __DIR__ . '/footer.php';
        exit;
    }
}

// Retourne true, false ou 'locked' selon l'état du rate limiting
function login_user($email, $password) {
    $ip = get_client_ip();

    if (!rate_limit_check($ip)) {
        return 'locked';
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        rate_limit_fail($ip);
        return false;
    }

    rate_limit_reset($ip);
    session_start_safe();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'     => $user['id'],
        'nom'    => $user['nom'],
        'prenom' => $user['prenom'],
        'email'  => $user['email'],
        'role'   => $user['role'],
    ];
    return true;
}

function logout_user() {
    session_start_safe();
    session_destroy();
}

function csrf_token() {
    session_start_safe();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify() {
    session_start_safe();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Token CSRF invalide.');
    }
}
