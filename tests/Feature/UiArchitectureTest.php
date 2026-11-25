<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiArchitectureTest extends TestCase
{
    public function test_vite_config_does_not_reference_external_font_cdn(): void
    {
        $viteConfig = file_get_contents(base_path('vite.config.js'));

        $this->assertNotFalse($viteConfig);
        $this->assertStringNotContainsString('bunny', $viteConfig);
        $this->assertStringContainsString('resources/css/app.css', $viteConfig);
        $this->assertStringContainsString('resources/js/app.js', $viteConfig);
    }

    public function test_app_css_defines_civic_institutional_tokens_and_dialog_print_rules(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('--color-canvas: #F4F6F9;', $css);
        $this->assertStringContainsString('--color-bps-navy: #0A2540;', $css);
        $this->assertStringContainsString('--color-bps-cobalt: #0B4F9C;', $css);
        $this->assertStringContainsString('--color-tv-canvas: #090E17;', $css);
        $this->assertStringContainsString('dialog:modal', $css);
        $this->assertStringContainsString('overscroll-behavior: contain;', $css);
        $this->assertStringContainsString('.print-only', $css);
        $this->assertStringContainsString('#ticket-print-area', $css);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $css);
    }

    public function test_app_js_defines_shared_ui_primitives(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertNotFalse($js);
        $this->assertStringContainsString('window.PstUi', $js);
        $this->assertStringContainsString('showToast', $js);
        $this->assertStringContainsString('showConfirm', $js);
        $this->assertStringContainsString('closedBy', $js);
        $this->assertStringContainsString('renderFormErrors', $js);
    }

    public function test_vite_build_manifest_exists_with_compiled_assets(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('resources/css/app.css', $manifest);
        $this->assertArrayHasKey('resources/js/app.js', $manifest);
    }
}
