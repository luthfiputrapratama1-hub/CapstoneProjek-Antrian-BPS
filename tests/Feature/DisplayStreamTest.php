<?php

namespace Tests\Feature;

use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayStreamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_display_page_renders_successfully(): void
    {
        $response = $this->get('/display');
        $response->assertStatus(200);
        $response->assertSee('Loket 1');
        $response->assertSee('Loket 2');
    }

    public function test_sse_stream_endpoint_has_event_stream_headers(): void
    {
        $response = $this->get('/display/stream');
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
        $response->assertHeader('Cache-Control', 'no-cache, private');
    }

    public function test_display_page_uses_dark_board_hidden_debug_panel_and_no_cdn_or_emojis(): void
    {
        $response = $this->get('/display');
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('animate-bounce', $html);
        $this->assertStringNotContainsString('animate-ping', $html);
        $this->assertStringNotContainsString('🔊', $html);
        $this->assertStringNotContainsString('📊', $html);
        $this->assertStringNotContainsString('🕒', $html);
        $this->assertStringContainsString('id="audio-debug-panel" class="hidden', $html);
        $this->assertStringContainsString('resetAllCounterCards', $html);
        $this->assertStringContainsString('renderRecentCalls', $html);
    }
}
