<?php
// ============================================================
// EduLib — Fonctions d'authentification
// ============================================================

require_once __DIR__ . '/db.php';

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

function login_user($email, $password) {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }
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
