<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\GuestBook;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoVisitorSeeder extends Seeder
{
    /**
     * Seeder untuk menghasilkan data kunjungan fiktif realistis lintas bulan (September, Oktober, November 2026).
     * Data ini ditujukan khusus untuk mendemonstrasikan visualisasi grafik dashboard, analisis jam sibuk,
     * distribusi layanan, dan ekspor dokumen laporan PDF / CSV.
     */
    public function run(): void
    {
        $services = Service::all()->keyBy('code_prefix');
        $counters = Counter::all();
        $petugas = User::where('role', 'petugas')->first() ?? User::where('role', 'admin')->first();

        if ($services->isEmpty() || $counters->isEmpty()) {
            $this->command?->warn('Layanan atau Loket belum tersedia. Jalankan PstBpsSeeder terlebih dahulu.');
            return;
        }

        // Daftar Nama Pengunjung Fiktif Realistis
        $fictionalNames = [
            'Ahmad Fauzi (Fiktif)', 'Siti Rahmawati (Fiktif)', 'Budi Setiawan (Fiktif)', 'Dewi Sartika (Fiktif)',
            'Rizky Pratama (Fiktif)', 'Nurul Hidayah (Fiktif)', 'Hendra Gunawan (Fiktif)', 'Ani Suryani (Fiktif)',
            'Deden Supriatna (Fiktif)', 'Yuliana Permata (Fiktif)', 'Asep Sunandar (Fiktif)', 'Rina Marlina (Fiktif)',
            'Cecep Mulyana (Fiktif)', 'Eka Fitriani (Fiktif)', 'Agus Purwanto (Fiktif)', 'Irma Novitasari (Fiktif)',
            'Tatang Sulaeman (Fiktif)', 'Lilis Suryani (Fiktif)', 'Wawan Hermawan (Fiktif)', 'Eni Rohaeni (Fiktif)',
            'Diki Zulkarnaen (Fiktif)', 'Tia Lestari (Fiktif)', 'Fajar Ramadhan (Fiktif)', 'Nia Kurniawati (Fiktif)',
            'Ginanjar Gumilar (Fiktif)', 'Resti Anggraeni (Fiktif)', 'Ade Kurniawan (Fiktif)', 'Mega Utami (Fiktif)',
            'Rudi Hartono (Fiktif)', 'Santi Indriani (Fiktif)', 'Wildan Nugraha (Fiktif)', 'Maya Sofiana (Fiktif)',
        ];

        // Daftar Instansi Berdasarkan Kategori
        $institutions = [
            'pendidikan' => [
                'Universitas Siliwangi (Fakultas Pertanian)',
                'Universitas Siliwangi (Fakultas Ekonomi & Bisnis)',
                'Universitas Pendidikan Indonesia (Kampus Tasikmalaya)',
                'Politeknik Triguna Tasikmalaya',
                'IAIC Cipasung Tasikmalaya',
                'STIKes Bakti Tunas Husada Tasikmalaya',
                'Universitas BTH Tasikmalaya',
                'STIE Latifah Mubarokiyah',
            ],
            'pemerintah' => [
                'Bappeda Kota Tasikmalaya (Bidang Litbang)',
                'Dinas Kesehatan Kota Tasikmalaya',
                'Dinas Pendidikan Kota Tasikmalaya',
                'Diskominfo Kota Tasikmalaya (Bidang Statistik Sektoral)',
                'Dinas Koperasi & UMKM Kota Tasikmalaya',
                'Dinas Pertanian dan Perikanan Kota Tasikmalaya',
                'Badan Pendapatan Daerah Kota Tasikmalaya',
                'Inspektorat Daerah Kota Tasikmalaya',
            ],
            'swasta' => [
                'PT Sumber Alfaria Trijaya (Cab. Tasikmalaya)',
                'Bank BJB Cabang Tasikmalaya',
                'PT Samudra Luas Logistik',
                'CV Bordir Jaya Sukaraja',
                'PT Citra Niaga Priangan',
                'Hotel Santika Tasikmalaya (Div. Marketing)',
                'Koperasi Simpan Pinjam Sejahtera Tasik',
            ],
            'lsm' => [
                'Lembaga Kajian Kebijakan Publik Priangan',
                'Harian Pagi Radar Tasikmalaya',
                'Kabar Priangan (Pikiran Rakyat Media Network)',
                'Yayasan Lingkungan Hidup Citanduy',
                'Forum Komunikasi Warga Peduli Pendidikan',
            ],
            'umum' => [
                'Masyarakat Kelurahan Sukamulya (Bungursari)',
                'Masyarakat Kelurahan Tuguraja (Cihideung)',
                'Masyarakat Kelurahan Kahuripan (Tawang)',
                'Masyarakat Kelurahan Nagarasari (Cipedes)',
                'Pelaku Usaha Mikro Rajapolah Tasikmalaya',
            ],
        ];

        // Deskripsi Kebutuhan Data per Layanan
        $needsDescriptions = [
            'A' => [
                'Konsultasi konsep definisi dan metodologi penghitungan PDRB riil Kota Tasikmalaya.',
                'Konsultasi data disagregasi tingkat pengangguran terbuka (TPT) dan ketenagakerjaan sektor informal.',
                'Permohonan penjelasan teknis mengenai indikator Indeks Pembangunan Manusia (IPM) 2024-2025.',
                'Konsultasi data angka kemiskinan makro dan garis kemiskinan per kapita di Priangan Timur.',
                'Konsultasi pemanfaatan data sensus pertanian untuk skripsi ketahanan pangan.',
            ],
            'B' => [
                'Pengajuan rekomendasi kegiatan statistik sektoral (Romantik) untuk Survei Kepuasan Masyarakat Diskominfo.',
                'Konsultasi metadata statistik sektoral untuk sensus pohon pelindung Dinas Lingkungan Hidup.',
                'Permohonan validasi rancangan kuesioner pendataan UMKM binaan Dinas Koperasi.',
                'Pengajuan rekomendasi survei morbiditas dan stunting balita Dinas Kesehatan.',
            ],
            'C' => [
                'Permohonan data mikro Susenas 2024 blok konsumsi dan pengeluaran rumah tangga.',
                'Permohonan dataset hasil Sakernas untuk riset ketimpangan upah minimum daerah.',
                'Pembelian publikasi resmi "Kota Tasikmalaya Dalam Angka 2025" cetak dan softcopy.',
                'Permohonan data disagregasi hasil Sensus Pertanian 2023 tingkat kecamatan.',
            ],
            'D' => [
                'Membaca dan menelaah publikasi Produk Domestik Regional Bruto (PDRB) Kota Tasikmalaya kurun 2020-2025.',
                'Kunjungan referensi perpustakaan untuk buku profil kependudukan hasil sensus.',
                'Peminjaman bahan pustaka indikator ekonomi dan keuangan daerah.',
                'Studi literatur indikator inflasi Kota Tasikmalaya 5 tahun terakhir.',
            ],
        ];

        // Periode: 3 Bulan Terakhir (September, Oktober, November 2026)
        $months = [
            ['year' => 2026, 'month' => 9, 'count' => 60],
            ['year' => 2026, 'month' => 10, 'count' => 75],
            ['year' => 2026, 'month' => 11, 'count' => 85],
        ];

        $globalTicketCounter = 1;

        foreach ($months as $period) {
            $year = $period['year'];
            $month = $period['month'];
            $targetCount = $period['count'];

            $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

            // Batasi sampai tanggal hari ini jika bulan berjalan
            $maxDay = ($year == now()->year && $month == now()->month) ? min($daysInMonth, now()->day) : $daysInMonth;

            // Nomor antrean per layanan di-reset setiap hari kerja
            $dailyServiceCounters = [];

            for ($i = 0; $i < $targetCount; $i++) {
                // Pilih hari secara acak (Senin - Jumat diutamakan)
                $day = rand(1, max(1, $maxDay));
                $date = Carbon::createFromDate($year, $month, $day);

                // Lewati hari Sabtu & Minggu (Pelayanan PST buka Senin-Jumat)
                if ($date->isWeekend()) {
                    $date->subDays($date->dayOfWeek === Carbon::SATURDAY ? 1 : 2);
                    $day = $date->day;
                }

                $dateString = $date->toDateString();
                if (!isset($dailyServiceCounters[$dateString])) {
                    $dailyServiceCounters[$dateString] = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
                }

                // Pilih layanan (Bobot: A = 40%, C = 30%, B = 15%, D = 15%)
                $randService = rand(1, 100);
                if ($randService <= 40) {
                    $codePrefix = 'A';
                } elseif ($randService <= 70) {
                    $codePrefix = 'C';
                } elseif ($randService <= 85) {
                    $codePrefix = 'B';
                } else {
                    $codePrefix = 'D';
                }

                $service = $services[$codePrefix] ?? $services->first();
                $dailyServiceCounters[$dateString][$codePrefix]++;
                $ticketNum = $dailyServiceCounters[$dateString][$codePrefix];
                $ticketCode = sprintf('%s-%03d', $codePrefix, $ticketNum);

                // Jam sibuk pelayanan (8.00 - 15.00 WIB)
                $hourWeights = [
                    8 => 10, 9 => 25, 10 => 30, 11 => 15,
                    12 => 5, 13 => 15, 14 => 15, 15 => 5
                ];
                $hour = 9;
                $pick = rand(1, 120);
                $accum = 0;
                foreach ($hourWeights as $h => $w) {
                    $accum += $w;
                    if ($pick <= $accum) {
                        $hour = $h;
                        break;
                    }
                }
                $minute = rand(0, 59);

                $createdAt = $date->copy()->setTime($hour, $minute, rand(0, 59));
                $waitSec = rand(60, 900); // 1 - 15 menit
                $calledAt = $createdAt->copy()->addSeconds($waitSec);
                $servedAt = $calledAt->copy()->addSeconds(rand(5, 30));
                $serveSec = rand(180, 1800); // 3 - 30 menit
                $completedAt = $servedAt->copy()->addSeconds($serveSec);

                $counter = $counters->random();

                // Status antrean mayoritas 'completed', sedikit 'skipped'
                $isSkipped = ($i % 25 === 0);
                $status = $isSkipped ? 'skipped' : 'completed';

                $queue = Queue::create([
                    'queue_date' => $dateString,
                    'ticket_code' => $ticketCode,
                    'ticket_number' => $ticketNum,
                    'service_id' => $service->id,
                    'counter_id' => $counter->id,
                    'user_id' => $petugas->id,
                    'status' => $status,
                    'recall_count' => $isSkipped ? 3 : rand(0, 2),
                    'called_at' => $calledAt,
                    'served_at' => $isSkipped ? null : $servedAt,
                    'completed_at' => $isSkipped ? null : $completedAt,
                    'wait_duration_seconds' => $waitSec,
                    'serve_duration_seconds' => $isSkipped ? null : $serveSec,
                    'created_at' => $createdAt,
                    'updated_at' => $isSkipped ? $calledAt : $completedAt,
                ]);

                // Jika completed, buatkan buku tamu digital
                if ($status === 'completed') {
                    // Tentukan kategori instansi
                    $instKeys = ['pendidikan', 'pemerintah', 'swasta', 'lsm', 'umum'];
                    $cat = $instKeys[array_rand($instKeys)];
                    $instList = $institutions[$cat];
                    $instName = $instList[array_rand($instList)];

                    $fullName = $fictionalNames[array_rand($fictionalNames)];
                    $phone = sprintf('08%d%08d', rand(11, 23), rand(10000000, 99999999));
                    $emailUsername = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', $fullName)[0]));
                    $email = $emailUsername . rand(10, 99) . '@mailfiktif.ac.id';

                    $descList = $needsDescriptions[$codePrefix] ?? ['Konsultasi kebutuhan data publikasi statistik.'];
                    $desc = $descList[array_rand($descList)];

                    GuestBook::create([
                        'queue_id' => $queue->id,
                        'full_name' => $fullName,
                        'phone_number' => $phone,
                        'email' => $email,
                        'institution_name' => $instName,
                        'institution_type' => $cat,
                        'data_needs_description' => $desc,
                        'created_at' => $completedAt,
                        'updated_at' => $completedAt,
                    ]);
                }

                $globalTicketCounter++;
            }
        }
    }
}
