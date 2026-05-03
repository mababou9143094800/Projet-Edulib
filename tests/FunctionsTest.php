<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    // ── e() ─────────────────────────────────────────────────────

    public function test_e_escapes_html(): void
    {
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>'));
    }

    public function test_e_escapes_quotes(): void
    {
        $this->assertSame('&quot;hello&quot;', e('"hello"'));
    }

    public function test_e_returns_empty_string_for_empty_input(): void
    {
        $this->assertSame('', e(''));
    }

    // ── excerpt() ───────────────────────────────────────────────

    public function test_excerpt_returns_short_text_unchanged(): void
    {
        $this->assertSame('Hello', excerpt('Hello', 150));
    }

    public function test_excerpt_truncates_long_text(): void
    {
        $long = str_repeat('a', 200);
        $result = excerpt($long, 150);
        $this->assertSame(mb_strlen('…') + 150, mb_strlen($result));
        $this->assertStringEndsWith('…', $result);
    }

    public function test_excerpt_strips_html_tags(): void
    {
        $this->assertSame('Hello world', excerpt('<p>Hello world</p>', 150));
    }

    // ── nl2p() ──────────────────────────────────────────────────

    public function test_nl2p_wraps_single_paragraph(): void
    {
        $result = nl2p('Hello world');
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('Hello world', $result);
    }

    public function test_nl2p_escapes_html_entities(): void
    {
        $result = nl2p('<b>Bold</b>');
        $this->assertStringContainsString('&lt;b&gt;', $result);
    }

    public function test_nl2p_creates_multiple_paragraphs(): void
    {
        $result = nl2p("Para one\n\nPara two");
        $this->assertSame(2, substr_count($result, '<p>'));
    }

    // ── format_date() ───────────────────────────────────────────

    public function test_format_date_returns_expected_format(): void
    {
        $this->assertSame('01/01/2026', format_date('2026-01-01 12:00:00'));
    }

    public function test_format_date_full_returns_readable_date(): void
    {
        $result = format_date_full('2026-05-02 10:00:00');
        $this->assertStringContainsString('2026', $result);
        $this->assertStringContainsString('mai', $result);
    }

    // ── app_log() ───────────────────────────────────────────────

    public function test_log_writes_to_file(): void
    {
        $logFile = LOG_DIR . '/app.log';
        if (file_exists($logFile)) unlink($logFile);

        log_info('Test message', ['key' => 'value']);

        $this->assertFileExists($logFile);
        $content = file_get_contents($logFile);
        $this->assertStringContainsString('INFO', $content);
        $this->assertStringContainsString('Test message', $content);
    }
}
