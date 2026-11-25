<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class UiComponentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_core_blade_components_render_clean_svg_and_native_dialog(): void
    {
        $brandHtml = Blade::render('<x-bps-brand subtitle="Kota Tasikmalaya" />');
        $this->assertStringContainsString('<svg', $brandHtml);
        $this->assertStringContainsString('Kota Tasikmalaya', $brandHtml);

        $iconHtml = Blade::render('<x-icon name="ticket" class="w-5 h-5" />');
        $this->assertStringContainsString('<svg', $iconHtml);
        $this->assertStringContainsString('aria-hidden="true"', $iconHtml);

        $sizedIconHtml = Blade::render('<x-icon name="close" class="w-4 h-4" />');
        $this->assertStringContainsString('w-4 h-4', $sizedIconHtml);
        $this->assertStringNotContainsString('w-5', $sizedIconHtml);
        $this->assertStringNotContainsString('h-5', $sizedIconHtml);

        $modalHtml = Blade::render('<x-modal id="test-modal" title="Judul Modal"><p>Isi</p></x-modal>');
        $this->assertStringContainsString('<dialog', $modalHtml);
        $this->assertStringContainsString('closedby="any"', $modalHtml);
        $this->assertStringContainsString('<h2 id="test-modal-title"', $modalHtml);
        $this->assertStringNotContainsString('<h1', $modalHtml);
        $this->assertStringNotContainsString('role="dialog"', $modalHtml);
        $this->assertStringNotContainsString('aria-modal', $modalHtml);
    }

    public function test_supporting_blade_components_render_expected_accessibility_attributes(): void
    {
        $badgeHtml = Blade::render('<x-badge tone="cobalt">Aktif</x-badge>');
        $this->assertStringContainsString('Aktif', $badgeHtml);
        $this->assertStringContainsString('text-[#0B4F9C]', $badgeHtml);

        $tableHtml = Blade::render('<x-table-wrapper labelled-by="tbl-heading"><table></table></x-table-wrapper>');
        $this->assertStringContainsString('role="region"', $tableHtml);
        $this->assertStringContainsString('tabindex="0"', $tableHtml);
        $this->assertStringContainsString('aria-labelledby="tbl-heading"', $tableHtml);

        $confirmHtml = Blade::render('<x-confirm-dialog />');
        $this->assertStringContainsString('id="pst-confirm-dialog"', $confirmHtml);
        $this->assertStringContainsString('closedby="closerequest"', $confirmHtml);

        $toastHtml = Blade::render('<x-toast-region />');
        $this->assertStringContainsString('id="pst-toast-stack"', $toastHtml);
        $this->assertStringContainsString('id="pst-toast-announcer"', $toastHtml);
        $this->assertStringContainsString('aria-live="polite"', $toastHtml);

        view()->share('errors', new ViewErrorBag());
        $emptyFlashHtml = Blade::render('<x-flash-alerts />');
        $this->assertSame('', trim($emptyFlashHtml));

        session()->flash('success', 'Perubahan berhasil disimpan.');
        $errorBag = new ViewErrorBag();
        $errorBag->put('default', new MessageBag(['Kolom wajib diisi.']));
        view()->share('errors', $errorBag);
        $populatedFlashHtml = Blade::render('<x-flash-alerts />');
        $this->assertStringContainsString('Perubahan berhasil disimpan.', $populatedFlashHtml);
        $this->assertStringContainsString('Kolom wajib diisi.', $populatedFlashHtml);
        $this->assertStringContainsString('role="status"', $populatedFlashHtml);
        $this->assertStringContainsString('role="alert"', $populatedFlashHtml);
    }

    public function test_workspace_layout_renders_single_nav_with_aria_current_and_password_link(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $html = view('layouts.workspace', [
            'activeNav' => 'reports',
            'pageTitle' => 'Rekap Laporan',
            'pageSubtitle' => 'PST BPS Kota Tasikmalaya',
        ])->render();

        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString(route('password.change'), $html);
        $this->assertStringContainsString('id="pst-toast-stack"', $html);
        $this->assertStringContainsString('id="pst-confirm-dialog"', $html);

        $petugas = User::where('role', 'petugas')->first();
        $this->actingAs($petugas);

        $petugasHtml = view('layouts.workspace', [
            'activeNav' => 'loket',
            'pageTitle' => 'Meja Kerja Pelayanan Loket',
            'pageSubtitle' => 'PST BPS Kota Tasikmalaya',
        ])->render();

        $this->assertStringNotContainsString(route('admin.dashboard'), $petugasHtml);
        $this->assertStringContainsString(route('counter.index'), $petugasHtml);
    }

    public function test_portal_and_screen_layouts_and_pagination_render_cleanly(): void
    {
        $portalHtml = view('layouts.portal')->render();
        $this->assertStringContainsString('Pelayanan Statistik Terpadu (PST)', $portalHtml);
        $this->assertStringContainsString('id="pst-toast-stack"', $portalHtml);

        $darkScreenHtml = view('layouts.screen', ['screenTheme' => 'dark'])->render();
        $this->assertStringContainsString('bg-[#090E17]', $darkScreenHtml);

        $lightScreenHtml = view('layouts.screen')->render();
        $this->assertStringContainsString('bg-[#F4F6F9]', $lightScreenHtml);

        $paginator = new LengthAwarePaginator(
            collect(['a', 'b']),
            25,
            10,
            2,
            ['path' => '/admin/reports']
        );
        $paginationHtml = $paginator->links('vendor.pagination.tailwind')->render();
        $this->assertStringContainsString('aria-current="page"', $paginationHtml);
        $this->assertStringContainsString('Menampilkan', $paginationHtml);
        $this->assertStringContainsString('25', $paginationHtml);
    }
}
