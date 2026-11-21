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
