<?php
// ============================================================
// EduLib — Fonctions utilitaires
// ============================================================

function e($s) {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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

function get_categories() {
    static $cats = null;
    if ($cats === null) {
        $cats = db()->query('SELECT * FROM categories ORDER BY nom')->fetchAll();
    }
    return $cats;
}

function format_date($date) {
    $d = new DateTimeImmutable($date);
    return $d->format('d/m/Y');
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

function nl2p($text) {
    $text = e($text);
    $paragraphs = array_filter(array_map('trim', explode("\n\n", $text)));
    $out = '';
    foreach ($paragraphs as $p) {
        $out .= '<p>' . nl2br($p) . '</p>';
    }
    return $out ?: '<p>' . nl2br($text) . '</p>';
}
