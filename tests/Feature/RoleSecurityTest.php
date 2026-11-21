<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleSecurityTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/loket')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/admin/reports')->assertRedirect('/login');
    }

    public function test_petugas_cannot_access_admin_dashboard_or_reports(): void
    {
        $this->actingAs($this->petugas);

        $this->get('/admin/dashboard')->assertStatus(403);
        $this->get('/admin/reports')->assertStatus(403);
        $this->get('/admin/reports/export-csv')->assertStatus(403);
    }

    public function test_admin_can_access_both_admin_and_loket_pages(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin/dashboard')->assertStatus(200);
        $this->get('/admin/reports')->assertStatus(200);
        $this->get('/loket')->assertStatus(200);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->petugas->update(['is_active' => false]);

        $response = $this->post('/login', [
            'username' => 'petugas1',
            'password' => 'petugas123',
        ]);

        $response->assertSessionHasErrors(['username']);
        $this->assertGuest();
    }

    public function test_login_redirects_according_to_role(): void
    {
        $resAdmin = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);
        $resAdmin->assertRedirect(route('admin.dashboard'));

        $this->post('/logout');

        $resPetugas = $this->post('/login', [
            'username' => 'petugas1',
            'password' => 'petugas123',
        ]);
        $resPetugas->assertRedirect(route('counter.index'));
    }

    public function test_authenticated_user_can_change_own_password(): void
    {
        $this->actingAs($this->petugas);

        // Percobaan gagal dengan password lama yang keliru
        $failRes = $this->post('/password/change', [
            'current_password' => 'wrongpassword',
            'password' => 'baru123456',
            'password_confirmation' => 'baru123456',
        ]);
        $failRes->assertSessionHasErrors(['current_password']);

        // Percobaan berhasil
        $successRes = $this->post('/password/change', [
            'current_password' => 'petugas123',
            'password' => 'baru123456',
            'password_confirmation' => 'baru123456',
        ]);
        $successRes->assertSessionHas('success');

        $this->petugas->refresh();
        $this->assertTrue(Hash::check('baru123456', $this->petugas->password));
    }
}
