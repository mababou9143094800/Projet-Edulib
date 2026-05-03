<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    // ── Constantes rate limiting ─────────────────────────────────
    // Les fonctions rate_limit_* dépendent de la BDD ; on teste la logique métier
    // uniquement via les constantes définies dans auth.php.

    public function test_rate_limit_max_is_positive(): void
    {
        $this->assertGreaterThan(0, RATE_LIMIT_MAX);
    }

    public function test_rate_limit_window_is_positive(): void
    {
        $this->assertGreaterThan(0, RATE_LIMIT_WINDOW);
    }

    // ── get_client_ip() ─────────────────────────────────────────

    public function test_get_client_ip_returns_string(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->assertIsString(get_client_ip());
    }

    public function test_get_client_ip_prefers_forwarded_header(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';
        $_SERVER['REMOTE_ADDR']          = '127.0.0.1';
        $this->assertSame('10.0.0.1', get_client_ip());
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }
}
