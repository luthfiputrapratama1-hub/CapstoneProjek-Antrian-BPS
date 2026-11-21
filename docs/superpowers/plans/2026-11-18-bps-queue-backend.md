# Sistem Antrean PST BPS Backend Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun sistem backend informasi antrean terintegrasi berbasis Laravel untuk Pelayanan Statistik Terpadu (PST) BPS, mencakup anjungan tiket Kiosk mandiri, Layar Display TV dengan Server-Sent Events (SSE) dan Sound Announcer audio chunks lokal, modul operasional loket dengan Buku Tamu Digital terpadu, serta Mini Dashboard rekapitulasi data pengunjung.

**Architecture:** Laravel Monolith (Blade + Tailwind CSS + Vanilla JS/Alpine.js) dengan endpoint internal JSON/SSE. Alur antrean diatur melalui State Machine ketat (`waiting` → `calling` → `serving` → `completed` / `skipped`) dengan penguncian transaksi atomik (*pessimistic locking*) dan penyimpanan durasi pelayanan untuk evaluasi kinerja.

**Tech Stack:** PHP 8.2+ (PHP 8.5 di Distrobox `my-distrobox`), Laravel 11/12, SQLite (fase development lokal) & MySQL (fase deployment), Server-Sent Events (SSE), HTML5 Web Audio API, Chart.js.

## Global Constraints
- Menjalankan seluruh perintah runtime PHP/Composer di dalam container Distrobox: `distrobox enter my-distrobox -- <command>`.
- Menggunakan basis data SQLite pada pengembangan lokal (`DB_CONNECTION=sqlite`, `database/database.sqlite`).
- Tidak menggunakan dependensi WebSocket daemon eksternal (menggunakan Server-Sent Events / SSE murni).
- Menyediakan opsi simulasi perangkat keras (`KIOSK_MOCK_PRINT=true`) agar seluruh alur Kiosk dan Audio dapat diuji 100% tanpa printer fisik.
- Menjaga konvensi kode standar PSR-12 dan TDD dengan PHPUnit/Pest.

---

### Task 1: Inisialisasi Proyek Laravel & Konfigurasi Lingkungan

**Files:**
- Create: Berkas proyek Laravel di root repositori (`composer.json`, `.env.example`, `.env`, `artisan`, dll.)
- Modify: `.env:1-40`
- Test: `tests/Feature/ExampleTest.php`

**Interfaces:**
- Consumes: PHP 8.5, Composer 2.11 di `my-distrobox`
- Produces: Proyek Laravel terkonfigurasi dengan SQLite dan `KIOSK_MOCK_PRINT=true`

- [ ] **Step 1: Inisialisasi proyek Laravel baru di direktori saat ini**

Jalankan perintah instalasi Laravel di Distrobox tanpa menimpa berkas yang sudah ada:
```bash
distrobox enter my-distrobox -- composer create-project laravel/laravel . --prefer-dist
```

- [ ] **Step 2: Konfigurasi berkas `.env` untuk SQLite dan variabel sistem antrean**

Pastikan konfigurasi `.env` menggunakan SQLite dan menyertakan flag Kiosk:
```ini
APP_NAME="Sistem Antrean PST BPS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_HOST, DB_PORT, DB_DATABASE tidak diperlukan untuk SQLite default

KIOSK_MOCK_PRINT=true
```

Buat berkas basis data SQLite jika belum ada:
```bash
touch database/database.sqlite
distrobox enter my-distrobox -- php artisan key:generate
```

- [ ] **Step 3: Jalankan tes dasar untuk memverifikasi kesehatan framework**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test
```
Expected: PASS (Semua tes bawaan `ExampleTest` berhasil).

- [ ] **Step 4: Commit konfigurasi awal proyek**

```bash
git add .
git commit -m "chore: scaffold laravel project with sqlite and kiosk mock config"
```

---

### Task 2: Skema Basis Data, Migrasi & Model Eloquent

**Files:**
- Create:
  - `database/migrations/2026_11_18_000001_create_services_table.php`
  - `database/migrations/2026_11_18_000002_create_counters_table.php`
  - `database/migrations/2026_11_18_000003_create_counter_services_table.php`
  - `database/migrations/2026_11_18_000004_create_queues_table.php`
  - `database/migrations/2026_11_18_000005_create_guest_books_table.php`
  - `database/migrations/2026_11_18_000006_add_role_and_active_to_users_table.php`
  - `app/Models/Service.php`
  - `app/Models/Counter.php`
  - `app/Models/Queue.php`
  - `app/Models/GuestBook.php`
  - `database/seeders/PstBpsSeeder.php`
- Test: `tests/Feature/DatabaseSchemaTest.php`

**Interfaces:**
- Consumes: SQLite schema builder
- Produces: Relasi model:
  - `Service::hasMany(Queue::class)`
  - `Counter::belongsToMany(Service::class, 'counter_services')`
  - `Counter::hasMany(Queue::class)`
  - `Queue::belongsTo(Service::class)`, `Queue::belongsTo(Counter::class)`, `Queue::hasOne(GuestBook::class)`
  - `GuestBook::belongsTo(Queue::class)`

- [ ] **Step 1: Tulis tes unit untuk skema basis data dan relasi model**

Buat berkas `tests/Feature/DatabaseSchemaTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\GuestBook;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_seed_and_relate_all_models(): void
    {
        $this->seed(\Database\Seeders\PstBpsSeeder::class);

        $this->assertDatabaseCount('services', 4);
        $this->assertDatabaseCount('counters', 2);
        $this->assertDatabaseHas('users', ['username' => 'admin', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['username' => 'petugas1', 'role' => 'petugas']);

        $service = Service::where('code_prefix', 'A')->first();
        $counter = Counter::where('counter_number', 1)->first();
        $user = User::where('role', 'petugas')->first();

        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $service->id,
            'counter_id' => $counter->id,
            'user_id' => $user->id,
            'status' => 'serving',
            'called_at' => now(),
            'served_at' => now(),
        ]);

        $guest = GuestBook::create([
            'queue_id' => $queue->id,
            'full_name' => 'Budi Santoso',
            'phone_number' => '081234567890',
            'email' => 'budi@example.com',
            'institution_name' => 'Universitas Siliwangi',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Konsultasi data inflasi Tasikmalaya',
        ]);

        $this->assertEquals('A-001', $guest->queue->ticket_code);
        $this->assertEquals('Budi Santoso', $queue->guestBook->full_name);
        $this->assertTrue($counter->services->contains($service));
    }
}
```

- [ ] **Step 2: Jalankan tes untuk memverifikasi kegagalan (Red phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=DatabaseSchemaTest
```
Expected: FAIL (Tabel dan model belum dibuat).

- [ ] **Step 3: Tulis migrasi basis data**

1. `database/migrations/2026_11_18_000001_create_services_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_prefix', 4)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order_priority')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('services');
    }
};
```

2. `database/migrations/2026_11_18_000002_create_counters_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('counter_number')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('counters');
    }
};
```

3. `database/migrations/2026_11_18_000003_create_counter_services_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('counter_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counter_id')->constrained('counters')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->unique(['counter_id', 'service_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('counter_services');
    }
};
```

4. `database/migrations/2026_11_18_000004_create_queues_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->date('queue_date')->index();
            $table->string('ticket_code', 10);
            $table->integer('ticket_number');
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('counter_id')->nullable()->constrained('counters')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('waiting')->index();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('wait_duration_seconds')->nullable();
            $table->integer('serve_duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['queue_date', 'service_id', 'ticket_number']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('queues');
    }
};
```

5. `database/migrations/2026_11_18_000005_create_guest_books_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('guest_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->unique()->constrained('queues')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone_number', 25);
            $table->string('email')->nullable();
            $table->string('institution_name');
            $table->string('institution_type', 50)->default('umum');
            $table->text('data_needs_description');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('guest_books');
    }
};
```

6. `database/migrations/2026_11_18_000006_add_role_and_active_to_users_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('role', 20)->default('petugas')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'is_active']);
        });
    }
};
```

- [ ] **Step 4: Tulis Model Eloquent dan Seeder PST BPS**

1. `app/Models/Service.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = ['name', 'code_prefix', 'description', 'is_active', 'order_priority'];
    protected $casts = ['is_active' => 'boolean'];

    public function queues(): HasMany {
        return $this->hasMany(Queue::class);
    }

    public function counters(): BelongsToMany {
        return $this->belongsToMany(Counter::class, 'counter_services');
    }
}
```

2. `app/Models/Counter.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counter extends Model
{
    protected $fillable = ['name', 'counter_number', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function services(): BelongsToMany {
        return $this->belongsToMany(Service::class, 'counter_services');
    }

    public function queues(): HasMany {
        return $this->hasMany(Queue::class);
    }
}
```

3. `app/Models/Queue.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Queue extends Model
{
    protected $fillable = [
        'queue_date', 'ticket_code', 'ticket_number', 'service_id', 'counter_id',
        'user_id', 'status', 'called_at', 'served_at', 'completed_at',
        'wait_duration_seconds', 'serve_duration_seconds'
    ];

    protected $casts = [
        'queue_date' => 'date',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function service(): BelongsTo {
        return $this->belongsTo(Service::class);
    }

    public function counter(): BelongsTo {
        return $this->belongsTo(Counter::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function guestBook(): HasOne {
        return $this->hasOne(GuestBook::class);
    }
}
```

4. `app/Models/GuestBook.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestBook extends Model
{
    protected $fillable = [
        'queue_id', 'full_name', 'phone_number', 'email',
        'institution_name', 'institution_type', 'data_needs_description'
    ];

    public function queue(): BelongsTo {
        return $this->belongsTo(Queue::class);
    }
}
```

5. `database/seeders/PstBpsSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PstBpsSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Konsultasi Statistik', 'code_prefix' => 'A', 'description' => 'Layanan rujukan & konsultasi data teknis statistik', 'order_priority' => 1],
            ['name' => 'Rekomendasi Kegiatan Statistik', 'code_prefix' => 'B', 'description' => 'Layanan rekomendasi survei/kegiatan statistik instansi pemerintah', 'order_priority' => 2],
            ['name' => 'Permintaan Data Mikro & Publikasi', 'code_prefix' => 'C', 'description' => 'Layanan permohonan raw data, dataset mikro, dan publikasi resmi', 'order_priority' => 3],
            ['name' => 'Perpustakaan Statistik', 'code_prefix' => 'D', 'description' => 'Layanan baca dan referensi koleksi pustaka statistik BPS', 'order_priority' => 4],
        ];

        $serviceModels = [];
        foreach ($services as $srv) {
            $serviceModels[$srv['code_prefix']] = Service::updateOrCreate(['code_prefix' => $srv['code_prefix']], $srv);
        }

        $counter1 = Counter::updateOrCreate(['counter_number' => 1], ['name' => 'Loket 1', 'is_active' => true]);
        $counter2 = Counter::updateOrCreate(['counter_number' => 2], ['name' => 'Loket 2', 'is_active' => true]);

        // Hubungkan counter ke semua layanan secara default
        $allServiceIds = collect($serviceModels)->pluck('id')->all();
        $counter1->services()->sync($allServiceIds);
        $counter2->services()->sync($allServiceIds);

        // Akun default
        User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Administrator PST', 'email' => 'admin@bps.go.id', 'password' => Hash::make('admin123'), 'role' => 'admin', 'is_active' => true]
        );

        User::updateOrCreate(
            ['username' => 'petugas1'],
            ['name' => 'Petugas Loket 1', 'email' => 'petugas1@bps.go.id', 'password' => Hash::make('petugas123'), 'role' => 'petugas', 'is_active' => true]
        );
    }
}
```

- [ ] **Step 5: Jalankan migrasi dan tes untuk memverifikasi kelulusan (Green phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan migrate:fresh --seed --seeder=PstBpsSeeder
distrobox enter my-distrobox -- php artisan test --filter=DatabaseSchemaTest
```
Expected: PASS (Semua model dan relasi terverifikasi).

- [ ] **Step 6: Commit modul skema basis data**

```bash
git add database/ app/Models/ tests/Feature/DatabaseSchemaTest.php
git commit -m "feat: implement database migrations, models, and seeders for pst bps queue system"
```

---

### Task 3: Modul Anjungan Kiosk & Pembuatan Tiket Antrean

**Files:**
- Create:
  - `app/Services/QueueNumberService.php`
  - `app/Http/Controllers/KioskController.php`
  - `resources/views/kiosk/index.blade.php`
  - `tests/Feature/KioskTicketTest.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `Service`, `Queue`
- Produces:
  - `QueueNumberService::generateTicket(int $serviceId): Queue`
  - `GET /kiosk` -> Menampilkan kartu 4 layanan
  - `POST /kiosk/ticket` -> Payload `{service_id: 1}`, merespon JSON tiket + sisa antrean

- [ ] **Step 1: Tulis tes fitur pembuatan nomor tiket antrean Kiosk**

Buat berkas `tests/Feature/KioskTicketTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Service;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
    }

    public function test_kiosk_page_displays_active_services(): void
    {
        $response = $this->get('/kiosk');
        $response->assertStatus(200);
        $response->assertSee('Konsultasi Statistik');
        $response->assertSee('Perpustakaan Statistik');
    }

    public function test_can_generate_ticket_with_consecutive_numbering_per_service(): void
    {
        $serviceA = Service::where('code_prefix', 'A')->first();
        $serviceB = Service::where('code_prefix', 'B')->first();

        $resA1 = $this->postJson('/kiosk/ticket', ['service_id' => $serviceA->id]);
        $resA1->assertStatus(200)
              ->assertJsonPath('data.ticket_code', 'A-001')
              ->assertJsonPath('data.waiting_ahead', 0);

        $resA2 = $this->postJson('/kiosk/ticket', ['service_id' => $serviceA->id]);
        $resA2->assertStatus(200)
              ->assertJsonPath('data.ticket_code', 'A-002')
              ->assertJsonPath('data.waiting_ahead', 1);

        $resB1 = $this->postJson('/kiosk/ticket', ['service_id' => $serviceB->id]);
        $resB1->assertStatus(200)
              ->assertJsonPath('data.ticket_code', 'B-001')
              ->assertJsonPath('data.waiting_ahead', 0);
    }
}
```

- [ ] **Step 2: Jalankan tes untuk memverifikasi kegagalan (Red phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=KioskTicketTest
```
Expected: FAIL (Rute `/kiosk` belum ada).

- [ ] **Step 3: Implementasikan Service pembuatan nomor tiket & Controller Kiosk**

1. `app/Services/QueueNumberService.php`:
```php
<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class QueueNumberService
{
    public function generateTicket(int $serviceId): array
    {
        return DB::transaction(function () use ($serviceId) {
            $service = Service::where('is_active', true)->findOrFail($serviceId);
            $today = now()->toDateString();

            // Kunci baris transaksi tanggal & service untuk atomisitas
            $lastTicketNumber = Queue::where('queue_date', $today)
                ->where('service_id', $service->id)
                ->lockForUpdate()
                ->max('ticket_number') ?? 0;

            $nextNumber = $lastTicketNumber + 1;
            $ticketCode = sprintf('%s-%03d', $service->code_prefix, $nextNumber);

            $queue = Queue::create([
                'queue_date' => $today,
                'ticket_code' => $ticketCode,
                'ticket_number' => $nextNumber,
                'service_id' => $service->id,
                'status' => 'waiting',
            ]);

            // Hitung antrean di depannya yang masih berstatus waiting
            $waitingAhead = Queue::where('queue_date', $today)
                ->where('service_id', $service->id)
                ->where('status', 'waiting')
                ->where('id', '<', $queue->id)
                ->count();

            return [
                'queue' => $queue,
                'ticket_code' => $ticketCode,
                'service_name' => $service->name,
                'created_at' => $queue->created_at->format('d/m/Y H:i') . ' WIB',
                'waiting_ahead' => $waitingAhead,
                'mock_print' => (bool) config('app.kiosk_mock_print', true),
            ];
        });
    }
}
```

2. `app/Http/Controllers/KioskController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\QueueNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KioskController extends Controller
{
    public function index(): View
    {
        $services = Service::where('is_active', true)->orderBy('order_priority')->get();
        return view('kiosk.index', compact('services'));
    }

    public function storeTicket(Request $request, QueueNumberService $service): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
        ]);

        $ticketData = $service->generateTicket((int) $validated['service_id']);

        return response()->json([
            'status' => 'success',
            'message' => 'Tiket berhasil dicetak',
            'data' => $ticketData,
        ]);
    }
}
```

3. Daftarkan rute di `routes/web.php`:
```php
use App\Http\Controllers\KioskController;

Route::get('/', function () {
    return redirect()->route('kiosk.index');
});

Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::post('/kiosk/ticket', [KioskController::class, 'storeTicket'])->name('kiosk.ticket');
```

4. `resources/views/kiosk/index.blade.php`:
Sediakan tampilan anjungan sentuh responsif dengan kartu tombol berukuran besar, modal pratinjau tiket termal (*Virtual Mock Preview*), dan area `@media print`.

- [ ] **Step 4: Jalankan tes untuk memverifikasi kelulusan (Green phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=KioskTicketTest
```
Expected: PASS.

- [ ] **Step 5: Commit modul Kiosk**

```bash
git add app/Services/QueueNumberService.php app/Http/Controllers/KioskController.php resources/views/kiosk/ routes/web.php tests/Feature/KioskTicketTest.php
git commit -m "feat: implement kiosk touch interface, atomic ticket numbering, and mock print logic"
```

---

### Task 4: Layar Display TV & Aliran Server-Sent Events (SSE)

**Files:**
- Create:
  - `app/Http/Controllers/DisplayController.php`
  - `resources/views/display/index.blade.php`
  - `public/audio/announcer/` (Berkas audio chunks atau synthesizer script)
  - `tests/Feature/DisplayStreamTest.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `Queue`, `Counter`
- Produces:
  - `GET /display` -> Monitor ruang tunggu antrean aktif
  - `GET /display/stream` -> Header `text/event-stream`, event `queue-called` & `heartbeat`

- [ ] **Step 1: Tulis tes fitur respons Display dan SSE Stream**

Buat berkas `tests/Feature/DisplayStreamTest.php`:
```php
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
}
```

- [ ] **Step 2: Jalankan tes untuk memverifikasi kegagalan (Red phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=DisplayStreamTest
```
Expected: FAIL.

- [ ] **Step 3: Implementasikan Controller Display TV & Pengaliran SSE**

`app/Http/Controllers/DisplayController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisplayController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $counters = Counter::where('is_active', true)->orderBy('counter_number')->get();
        $recentQueues = Queue::with('counter', 'service')
            ->where('queue_date', $today)
            ->whereIn('status', ['calling', 'serving', 'completed'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        return view('display.index', compact('counters', 'recentQueues'));
    }

    public function streamUpdates(Request $request): StreamedResponse
    {
        return response()->stream(function () {
            // Non-aktifkan buffer output PHP
            if (ob_get_level()) {
                ob_end_clean();
            }

            $today = now()->toDateString();
            $lastChecked = now()->subSeconds(2)->toDateTimeString();

            // Kirim status awal (snapshot)
            $activeQueues = Queue::with('counter', 'service')
                ->where('queue_date', $today)
                ->whereIn('status', ['calling', 'serving'])
                ->get();

            echo "event: initial-state\n";
            echo 'data: ' . json_encode(['active_queues' => $activeQueues]) . "\n\n";
            flush();

            // Pengecekan event panggilan baru selama koneksi berlangsung (max execution 30s)
            $startTime = time();
            while (time() - $startTime < 25) {
                $newCalls = Queue::with('counter', 'service')
                    ->where('queue_date', $today)
                    ->where('status', 'calling')
                    ->where('updated_at', '>=', $lastChecked)
                    ->get();

                foreach ($newCalls as $call) {
                    echo "event: queue-called\n";
                    echo 'data: ' . json_encode([
                        'ticket_code' => $call->ticket_code,
                        'code_prefix' => $call->service->code_prefix,
                        'ticket_number' => $call->ticket_number,
                        'counter_number' => $call->counter?->counter_number ?? 1,
                        'counter_name' => $call->counter?->name ?? 'Loket',
                        'service_name' => $call->service->name,
                    ]) . "\n\n";
                    flush();
                }

                $lastChecked = now()->toDateTimeString();
                echo ": heartbeat\n\n";
                flush();
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, private',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
```

Daftarkan rute di `routes/web.php`:
```php
use App\Http\Controllers\DisplayController;

Route::get('/display', [DisplayController::class, 'index'])->name('display.index');
Route::get('/display/stream', [DisplayController::class, 'streamUpdates'])->name('display.stream');
```

- [ ] **Step 4: Buat Tampilan Layar Display TV & Pemutar Audio Otomatis**

`resources/views/display/index.blade.php`:
- Tampilan kartu status loket aktif (animasi sorot saat ada panggilan).
- Tabel 5 nomor terakhir yang dipanggil.
- Jam digital dan informasi header BPS.
- Script JavaScript SSE Listener (`new EventSource('/display/stream')`).
- Script *Web Audio Chime + Voice Synthesizer / Audio Queue Player* yang merangkai bunyi lonceng diikuti penyebutan kode huruf dan angka.

- [ ] **Step 5: Jalankan tes untuk memverifikasi kelulusan (Green phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=DisplayStreamTest
```
Expected: PASS.

- [ ] **Step 6: Commit modul Display TV & SSE**

```bash
git add app/Http/Controllers/DisplayController.php resources/views/display/ routes/web.php tests/Feature/DisplayStreamTest.php
git commit -m "feat: implement tv display monitor and sse stream with audio announcer queue"
```

---

### Task 5: Operasional Loket & Formulir Buku Tamu Digital Terpadu

**Files:**
- Create:
  - `app/Http/Controllers/AuthController.php`
  - `app/Http/Controllers/CounterController.php`
  - `resources/views/auth/login.blade.php`
  - `resources/views/counter/index.blade.php`
  - `tests/Feature/CounterOperationsTest.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `Queue`, `GuestBook`, `Counter`, `User`
- Produces:
  - `POST /loket/call-next` -> Panggil nomor berikutnya
  - `POST /loket/start-serve` -> Ubah status jadi `serving`
  - `POST /loket/complete` -> Validasi data tamu, simpan `GuestBook`, ubah ke `completed`
  - `POST /loket/skip` -> Tandai antrean terlewat (`skipped`)

- [ ] **Step 1: Tulis tes fitur siklus hidup operasional loket dan validasi buku tamu**

Buat berkas `tests/Feature/CounterOperationsTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounterOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $petugas;
    protected Counter $counter;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
        $this->petugas = User::where('role', 'petugas')->first();
        $this->counter = Counter::first();
        $this->service = Service::first();
    }

    public function test_guest_cannot_access_loket_page(): void
    {
        $response = $this->get('/loket');
        $response->assertRedirect('/login');
    }

    public function test_petugas_can_call_next_queue_and_complete_with_guest_book(): void
    {
        // 1. Buat antrean menunggu
        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $this->service->id,
            'status' => 'waiting',
        ]);

        // 2. Petugas login & panggil nomor berikutnya
        $this->actingAs($this->petugas);
        $callRes = $this->postJson('/loket/call-next', [
            'counter_id' => $this->counter->id,
        ]);
        $callRes->assertStatus(200)
                ->assertJsonPath('data.ticket_code', 'A-001')
                ->assertJsonPath('data.status', 'calling');

        // 3. Mulai melayani
        $serveRes = $this->postJson('/loket/start-serve', ['queue_id' => $queue->id]);
        $serveRes->assertStatus(200)
                 ->assertJsonPath('data.status', 'serving');

        // 4. Selesaikan dengan buku tamu
        $completeRes = $this->postJson('/loket/complete', [
            'queue_id' => $queue->id,
            'full_name' => 'Dr. Hendra Wijaya',
            'phone_number' => '081298765432',
            'email' => 'hendra@univ.ac.id',
            'institution_name' => 'Fakultas Ekonomi Unsil',
            'institution_type' => 'pendidikan',
            'data_needs_description' => 'Permintaan data statistik kemiskinan dan PDRB Kota Tasikmalaya',
        ]);

        $completeRes->assertStatus(200)
                    ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('guest_books', [
            'queue_id' => $queue->id,
            'full_name' => 'Dr. Hendra Wijaya',
        ]);

        $queue->refresh();
        $this->assertEquals('completed', $queue->status);
        $this->assertNotNull($queue->wait_duration_seconds);
        $this->assertNotNull($queue->serve_duration_seconds);
    }
}
```

- [ ] **Step 2: Jalankan tes untuk memverifikasi kegagalan (Red phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=CounterOperationsTest
```
Expected: FAIL.

- [ ] **Step 3: Implementasikan Controller Autentikasi dan Operasional Loket**

1. `app/Http/Controllers/AuthController.php`:
Logika login, autentikasi sesi, dan logout.

2. `app/Http/Controllers/CounterController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\GuestBook;
use App\Models\Queue;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CounterController extends Controller
{
    public function index(Request $request): View
    {
        $counters = Counter::where('is_active', true)->get();
        $selectedCounterId = $request->get('counter_id', $counters->first()?->id);
        $selectedCounter = Counter::with('services')->find($selectedCounterId);

        $today = now()->toDateString();
        $activeQueue = Queue::with('service', 'guestBook')
            ->where('queue_date', $today)
            ->where('counter_id', $selectedCounterId)
            ->whereIn('status', ['calling', 'serving'])
            ->first();

        $waitingCount = Queue::where('queue_date', $today)
            ->where('status', 'waiting')
            ->whereIn('service_id', $selectedCounter?->services->pluck('id') ?? [])
            ->count();

        return view('counter.index', compact('counters', 'selectedCounter', 'activeQueue', 'waitingCount'));
    }

    public function callNext(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'counter_id' => ['required', 'exists:counters,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            $counter = Counter::with('services')->findOrFail($validated['counter_id']);
            $serviceIds = $counter->services->pluck('id')->all();
            $today = now()->toDateString();

            // Tarik antrean waiting terlama yang sesuai layanan loket
            $nextQueue = Queue::where('queue_date', $today)
                ->where('status', 'waiting')
                ->whereIn('service_id', $serviceIds)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (!$nextQueue) {
                return response()->json(['status' => 'empty', 'message' => 'Tidak ada antrean menunggu untuk layanan loket ini'], 404);
            }

            $nextQueue->update([
                'status' => 'calling',
                'counter_id' => $counter->id,
                'user_id' => Auth::id(),
                'called_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $nextQueue->load('service'),
            ]);
        });
    }

    public function recall(Request $request): JsonResponse
    {
        $validated = $request->validate(['queue_id' => ['required', 'exists:queues,id']]);
        $queue = Queue::findOrFail($validated['queue_id']);

        $queue->touch(); // Perbarui updated_at agar tertangkap oleh SSE

        return response()->json(['status' => 'success', 'data' => $queue]);
    }

    public function startServing(Request $request): JsonResponse
    {
        $validated = $request->validate(['queue_id' => ['required', 'exists:queues,id']]);
        $queue = Queue::findOrFail($validated['queue_id']);

        $servedAt = now();
        $waitDuration = $queue->called_at ? $servedAt->diffInSeconds($queue->created_at) : 0;

        $queue->update([
            'status' => 'serving',
            'served_at' => $servedAt,
            'wait_duration_seconds' => $waitDuration,
        ]);

        return response()->json(['status' => 'success', 'data' => $queue]);
    }

    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_id' => ['required', 'exists:queues,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email'],
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_type' => ['required', 'string'],
            'data_needs_description' => ['required', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {
            $queue = Queue::findOrFail($validated['queue_id']);

            GuestBook::updateOrCreate(
                ['queue_id' => $queue->id],
                [
                    'full_name' => $validated['full_name'],
                    'phone_number' => $validated['phone_number'],
                    'email' => $validated['email'] ?? null,
                    'institution_name' => $validated['institution_name'],
                    'institution_type' => $validated['institution_type'],
                    'data_needs_description' => $validated['data_needs_description'],
                ]
            );

            $completedAt = now();
            $serveDuration = $queue->served_at ? $completedAt->diffInSeconds($queue->served_at) : 0;

            $queue->update([
                'status' => 'completed',
                'completed_at' => $completedAt,
                'serve_duration_seconds' => $serveDuration,
            ]);

            return response()->json(['status' => 'success', 'data' => $queue]);
        });
    }

    public function skip(Request $request): JsonResponse
    {
        $validated = $request->validate(['queue_id' => ['required', 'exists:queues,id']]);
        $queue = Queue::findOrFail($validated['queue_id']);

        $queue->update(['status' => 'skipped']);

        return response()->json(['status' => 'success', 'data' => $queue]);
    }
}
```

3. Daftarkan rute autentikasi dan loket di `routes/web.php`:
```php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CounterController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/loket', [CounterController::class, 'index'])->name('counter.index');
    Route::post('/loket/call-next', [CounterController::class, 'callNext'])->name('counter.callNext');
    Route::post('/loket/recall', [CounterController::class, 'recall'])->name('counter.recall');
    Route::post('/loket/start-serve', [CounterController::class, 'startServing'])->name('counter.startServing');
    Route::post('/loket/complete', [CounterController::class, 'complete'])->name('counter.complete');
    Route::post('/loket/skip', [CounterController::class, 'skip'])->name('counter.skip');
});
```

4. Buat tampilan Blade loket dengan form Buku Tamu Digital di `resources/views/counter/index.blade.php`.

- [ ] **Step 4: Jalankan tes untuk memverifikasi kelulusan (Green phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=CounterOperationsTest
```
Expected: PASS.

- [ ] **Step 5: Commit modul operasional loket dan buku tamu**

```bash
git add app/Http/Controllers/AuthController.php app/Http/Controllers/CounterController.php resources/views/counter/ resources/views/auth/ routes/web.php tests/Feature/CounterOperationsTest.php
git commit -m "feat: implement counter operator desk with integrated digital guestbook"
```

---

### Task 6: Mini Dashboard Rekapitulasi & Ekspor Laporan

**Files:**
- Create:
  - `app/Http/Controllers/DashboardController.php`
  - `app/Http/Controllers/ReportController.php`
  - `resources/views/admin/dashboard.blade.php`
  - `resources/views/admin/reports/index.blade.php`
  - `tests/Feature/DashboardReportTest.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `Queue`, `GuestBook`, `Service`
- Produces:
  - `GET /admin/dashboard` -> Ringkasan metrik statistik harian & bulanan
  - `GET /admin/reports` -> Tabel filter pencarian pengunjung
  - `GET /admin/reports/export-csv` -> Ekspor berkas CSV/Spreadsheet

- [ ] **Step 1: Tulis tes fitur agregasi dashboard dan ekspor rekapitulasi**

Buat berkas `tests/Feature/DashboardReportTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\GuestBook;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PstBpsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PstBpsSeeder::class);
        $this->admin = User::where('role', 'admin')->first();
    }

    public function test_admin_can_access_dashboard_and_see_aggregations(): void
    {
        $service = Service::first();
        $queue = Queue::create([
            'queue_date' => now()->toDateString(),
            'ticket_code' => 'A-001',
            'ticket_number' => 1,
            'service_id' => $service->id,
            'status' => 'completed',
            'wait_duration_seconds' => 120,
            'serve_duration_seconds' => 300,
        ]);

        GuestBook::create([
            'queue_id' => $queue->id,
            'full_name' => 'Siti Nurhaliza',
            'phone_number' => '0812341234',
            'institution_name' => 'Bappeda Jawa Barat',
            'institution_type' => 'pemerintah',
            'data_needs_description' => 'Data statistik tenaga kerja',
        ]);

        $this->actingAs($this->admin);

        $res = $this->get('/admin/dashboard');
        $res->assertStatus(200);
        $res->assertSee('Total Kunjungan');
        $res->assertSee('Siti Nurhaliza');

        $exportRes = $this->get('/admin/reports/export-csv');
        $exportRes->assertStatus(200);
        $exportRes->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
```

- [ ] **Step 2: Jalankan tes untuk memverifikasi kegagalan (Red phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=DashboardReportTest
```
Expected: FAIL.

- [ ] **Step 3: Implementasikan Controller Dashboard & Laporan**

1. `app/Http/Controllers/DashboardController.php`:
Menghitung total kunjungan harian & bulanan, rata-rata waktu tunggu, durasi layanan, serta distribusi kategori pengunjung.

2. `app/Http/Controllers/ReportController.php`:
Filter data buku tamu berdasarkan rentang tanggal, jenis layanan, dan kategori instansi, serta fungsi ekspor CSV streaming.

3. Rute admin di `routes/web.php`:
```php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.exportCsv');
});
```

- [ ] **Step 4: Jalankan tes untuk memverifikasi kelulusan (Green phase)**

Jalankan:
```bash
distrobox enter my-distrobox -- php artisan test --filter=DashboardReportTest
```
Expected: PASS.

- [ ] **Step 5: Commit modul Dashboard & Laporan**

```bash
git add app/Http/Controllers/DashboardController.php app/Http/Controllers/ReportController.php resources/views/admin/ routes/web.php tests/Feature/DashboardReportTest.php
git commit -m "feat: implement mini dashboard kpi and visitor report csv export"
```

---

### Task 7: Verifikasi End-to-End & Dokumentasi Panduan Pengujian

**Files:**
- Create: `README.md`
- Test: Seluruh unit test (`php artisan test`)

**Interfaces:**
- Consumes: Seluruh modul sistem
- Produces: Rangkaian tes 100% lulus dan buku petunjuk pengoperasian lokal

- [x] **Step 1: Jalankan seluruh pengujian otomatis**

Jalankan:
```bash
php artisan test
```
Expected: PASS (Semua tes hijau tanpa kesalahan - 42 passed, 220 assertions).

- [x] **Step 2: Buat dokumentasi README.md lengkap**

Tulis dokumentasi di `README.md` mencakup:
- Prasyarat runtime (PHP 8.2+, Composer, SQLite/MySQL).
- Perintah instalasi dan seeder akun default (`admin` & `petugas1`).
- Panduan menjalankan server lokal (`PHP_CLI_SERVER_WORKERS=4 php artisan serve`).
- Panduan simulasi multi-tab peramban (Kiosk, Display TV, Loket, Dashboard).
- Konfigurasi untuk deployment LAN BPS nantinya dan catatan serah terima hardware (`docs/handover-hardware-guide.md`).
- Seeder data demo ratusan kunjungan fiktif lintas bulan (`DemoVisitorSeeder`).

- [x] **Step 3: Commit dokumentasi dan finalisasi**

Seluruh fase pengembangan (Fase 1 s.d. Fase 5) telah tuntas diselesaikan dan diverifikasi.
