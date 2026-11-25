<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
        $this->admin = User::where('role', 'admin')->first();
        $this->petugas = User::where('role', 'petugas')->first();
    }

    public function test_admin_can_manage_services(): void
    {
        $this->actingAs($this->admin);

        // 1. Buka halaman index
        $res = $this->get('/admin/services');
        $res->assertStatus(200)->assertSee('Konsultasi Statistik');

        // 2. Tambah layanan baru
        $storeRes = $this->post('/admin/services', [
            'name' => 'Layanan Pojok Statistik',
            'code_prefix' => 'E',
            'description' => 'Layanan edukasi statistik di perguruan tinggi',
            'order_priority' => 5,
            'is_active' => true,
        ]);
        $storeRes->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseHas('services', ['code_prefix' => 'E', 'name' => 'Layanan Pojok Statistik']);

        $serviceE = Service::where('code_prefix', 'E')->first();

        // 3. Edit layanan
        $updateRes = $this->put("/admin/services/{$serviceE->id}", [
            'name' => 'Pojok Statistik Kampus',
            'code_prefix' => 'E',
            'description' => 'Diperbarui',
            'order_priority' => 5,
            'is_active' => true,
        ]);
        $updateRes->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseHas('services', ['name' => 'Pojok Statistik Kampus']);

        // 4. Toggle aktif/nonaktif
        $toggleRes = $this->patch("/admin/services/{$serviceE->id}/toggle");
        $toggleRes->assertRedirect(route('admin.services.index'));
        $serviceE->refresh();
        $this->assertFalse($serviceE->is_active);
    }

    public function test_admin_can_manage_counters_and_service_assignments(): void
    {
        $this->actingAs($this->admin);

        $srvA = Service::where('code_prefix', 'A')->first();
        $srvB = Service::where('code_prefix', 'B')->first();

        // 1. Index loket
        $countersRes = $this->get('/admin/counters')->assertStatus(200)->assertSee('Loket 1');
        $countersHtml = $countersRes->getContent();
        $this->assertStringContainsString('Edit Meja Loket &amp; Penugasan', $countersHtml);
        $this->assertStringNotContainsString('&amp;amp;', $countersHtml);

        // 2. Tambah loket baru dengan penugasan layanan A dan B
        $storeRes = $this->post('/admin/counters', [
            'name' => 'Loket Khusus Mahasiswa',
            'counter_number' => 3,
            'service_ids' => [$srvA->id, $srvB->id],
            'is_active' => true,
        ]);
        $storeRes->assertRedirect(route('admin.counters.index'));

        $counter3 = Counter::where('counter_number', 3)->first();
        $this->assertNotNull($counter3);
        $this->assertEquals(2, $counter3->services()->count());

        // 3. Update penugasan loket (hanya layanan A)
        $this->put("/admin/counters/{$counter3->id}", [
            'name' => 'Loket Khusus Mahasiswa Revisi',
            'counter_number' => 3,
            'service_ids' => [$srvA->id],
            'is_active' => true,
        ]);
        $counter3->refresh();
        $this->assertEquals(1, $counter3->services()->count());

        // 4. Toggle status loket
        $this->patch("/admin/counters/{$counter3->id}/toggle");
        $counter3->refresh();
        $this->assertFalse($counter3->is_active);
    }

    public function test_admin_can_manage_users_and_reset_passwords(): void
    {
        $this->actingAs($this->admin);

        // 1. Index pengguna
        $this->get('/admin/users')->assertStatus(200)->assertSee('petugas1');

        // 2. Tambah petugas baru
        $storeRes = $this->post('/admin/users', [
            'name' => 'Siti Aminah, S.Stat.',
            'username' => 'petugas2',
            'email' => 'petugas2@bps.go.id',
            'role' => 'petugas',
            'password' => 'petugas123',
        ]);
        $storeRes->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['username' => 'petugas2', 'role' => 'petugas']);

        $petugas2 = User::where('username', 'petugas2')->first();

        // 3. Nonaktifkan akun petugas2
        $this->patch("/admin/users/{$petugas2->id}/toggle");
        $petugas2->refresh();
        $this->assertFalse($petugas2->is_active);

        // 4. Admin dicegah menonaktifkan akun sendiri
        $selfToggleRes = $this->patch("/admin/users/{$this->admin->id}/toggle");
        $selfToggleRes->assertSessionHasErrors(['error']);
        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);

        // 5. Reset password petugas2
        $this->post("/admin/users/{$petugas2->id}/reset-password", [
            'new_password' => 'rahasiaBaru123',
        ]);
        $petugas2->refresh();
        $this->assertTrue(Hash::check('rahasiaBaru123', $petugas2->password));
    }

    public function test_petugas_cannot_access_any_management_routes(): void
    {
        $this->actingAs($this->petugas);

        $this->get('/admin/services')->assertStatus(403);
        $this->post('/admin/services', [])->assertStatus(403);
        $this->get('/admin/counters')->assertStatus(403);
        $this->post('/admin/counters', [])->assertStatus(403);
        $this->get('/admin/users')->assertStatus(403);
        $this->post('/admin/users', [])->assertStatus(403);
    }

    public function test_admin_crud_pages_use_native_dialog_json_payloads_and_consistent_workspace_header(): void
    {
        $this->actingAs($this->admin);

        foreach (['/admin/services', '/admin/counters', '/admin/users'] as $url) {
            $html = $this->get($url)->assertStatus(200)->getContent();
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
            $this->assertStringNotContainsString('addslashes', $html);
            $this->assertStringContainsString('<dialog', $html);
            $this->assertStringContainsString('data-payload=', $html);
            $this->assertStringContainsString(route('password.change'), $html);
            $this->assertStringContainsString('aria-current="page"', $html);
        }
    }
}
