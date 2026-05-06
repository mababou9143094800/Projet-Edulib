<?php

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function flash_set($type, $message) {
    session_start_safe();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get() {
    session_start_safe();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function render_flash() {
    $flash = flash_get();
    if ($flash) {
        $type = $flash['type'] === 'success' ? 'alert--success' : 'alert--error';
        echo '<div class="alert ' . $type . '">' . e($flash['message']) . '</div>';
    }
}

// Met en cache le résultat pour éviter plusieurs requêtes par page
function get_categories() {
    static $cats = null;
    if ($cats === null) {
        $cats = db()->query('SELECT * FROM categories ORDER BY nom')->fetchAll();
    }
    return $cats;
}

function format_date($date) {
    return (new DateTimeImmutable($date))->format('d/m/Y');
}

function format_date_full($date) {
    $d = new DateTimeImmutable($date);
    $months = ['jan.','fév.','mars','avr.','mai','juin','juil.','août','sep.','oct.','nov.','déc.'];
    return $d->format('j') . ' ' . $months[(int)$d->format('n') - 1] . ' ' . $d->format('Y');
}

function excerpt($text, $length = 150) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '…';
}

// Convertit les doubles sauts de ligne en balises <p>
function nl2p($text) {
    $text = e($text);
    $paragraphs = array_filter(array_map('trim', explode("\n\n", $text)));
    $out = '';
    foreach ($paragraphs as $p) {
        $out .= '<p>' . nl2br($p) . '</p>';
    }
    return $out ?: '<p>' . nl2br($text) . '</p>';
}

function app_log($level, $message, array $context = []) {
    if (!defined('LOG_DIR')) return;
    if (!is_dir(LOG_DIR)) {
        @mkdir(LOG_DIR, 0755, true);
        // empêche git de versionner les logs
        @file_put_contents(LOG_DIR . '/.gitignore', "*\n!.gitignore\n");
    }
    $line = sprintf(
        "[%s] %s: %s %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $message,
        $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
    );
    @file_put_contents(LOG_DIR . '/app.log', $line, FILE_APPEND | LOCK_EX);
}

function log_info($msg, $ctx = [])  { app_log('info',  $msg, $ctx); }
function log_warn($msg, $ctx = [])  { app_log('warn',  $msg, $ctx); }
function log_error($msg, $ctx = []) { app_log('error', $msg, $ctx); }

set_exception_handler(function(Throwable $e) {
    log_error('Exception non gérée : ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    if (!headers_sent()) http_response_code(500);
    echo '<div style="padding:2rem;font-family:sans-serif;color:#c0392b;">'
       . '<strong>Une erreur est survenue.</strong> Veuillez réessayer.</div>';
});

function send_notification($to, $subject, $body) {
    if (!MAIL_FROM) return false; // MAIL_FROM vide = notifications désactivées
    $headers = "From: " . MAIL_FROM . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    $sent = @mail($to, $subject, $body, $headers);
    if (!$sent) {
        log_warn('Email non envoyé', ['to' => $to, 'subject' => $subject]);
    }
    return $sent;
}
