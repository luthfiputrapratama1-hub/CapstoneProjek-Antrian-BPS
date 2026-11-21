<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Pengunjung PST BPS</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.3;
        }
        /* Kop Surat BPS */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .kop-logo {
            width: 70px;
            text-align: center;
            font-weight: 900;
            font-size: 20pt;
            color: #0f172a;
            border: 2px solid #0f172a;
            padding: 8px;
            letter-spacing: 2px;
        }
        .kop-text {
            text-align: center;
            padding-left: 10px;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .kop-text h3 {
            margin: 2px 0;
            font-size: 11pt;
            font-weight: bold;
            color: #1e3a8a;
        }
        .kop-text p {
            margin: 1px 0;
            font-size: 8pt;
            color: #475569;
        }
        .double-line {
            border-top: 2px solid #0f172a;
            border-bottom: 1px solid #0f172a;
            height: 3px;
            margin-top: 6px;
            margin-bottom: 12px;
        }

        /* Judul Laporan */
        .report-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-title h1 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
        }
        .report-title .period {
            font-size: 8.5pt;
            color: #475569;
            margin-top: 3px;
        }

        /* Ringkasan KPI */
        .summary-box {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-box td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            background-color: #f8fafc;
            font-size: 8pt;
        }
        .summary-box strong {
            color: #0f172a;
        }

        /* Tabel Data Pengunjung */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #1e3a8a;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            font-size: 7.5pt;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #94a3b8;
            border-radius: 2px;
            background-color: #f1f5f9;
        }

        /* Tanda Tangan */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            width: 50%;
            vertical-align: top;
            font-size: 8.5pt;
            padding: 0 20px;
        }
        .sig-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- Kop Dinas BPS -->
    <table class="header-table">
        <tr>
            <td style="width: 80px;">
                <div class="kop-logo">BPS</div>
            </td>
            <td class="kop-text">
                <h2>Badan Pusat Statistik Kota Tasikmalaya</h2>
                <h3>Pelayanan Statistik Terpadu (PST)</h3>
                <p>Jl. Ir. H. Juanda No. 84, Sukamulya, Kec. Bungursari, Kota Tasikmalaya, Jawa Barat 46151</p>
                <p>Telp: (0265) 345678 | Email: bps3278@bps.go.id | Website: tasikmalayakota.bps.go.id</p>
            </td>
        </tr>
    </table>
    <div class="double-line"></div>

    <!-- Judul Dokumen -->
    <div class="report-title">
        <h1>Laporan Rekapitulasi Kunjungan Pengunjung PST</h1>
        <div class="period">
            Periode Data: <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s.d. 
            <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
        </div>
    </div>

    <!-- Ringkasan Eksekutif -->
    <table class="summary-box">
        <tr>
            <td style="width: 33%;">
                Total Tamu Dilayani: <strong>{{ number_format($totalRecords, 0, ',', '.') }} Orang</strong>
            </td>
            <td style="width: 33%;">
                Rata-rata Waktu Tunggu: <strong>{{ $avgWaitMinutes }} Menit</strong>
            </td>
            <td style="width: 34%;">
                Rata-rata Waktu Layanan: <strong>{{ $avgServeMinutes }} Menit</strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Rekap Data -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 8%;">Tiket</th>
                <th style="width: 14%;">Layanan PST</th>
                <th style="width: 13%;">Nama Pengunjung</th>
                <th style="width: 10%;">Kontak</th>
                <th style="width: 13%;">Instansi Asal</th>
                <th style="width: 7%;">Kategori</th>
                <th style="width: 7%;">Tunggu</th>
                <th style="width: 7%;">Layanan</th>
                <th style="width: 9%;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $r)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $r->queue->queue_date->format('d/m/Y') }}</td>
                    <td class="text-center font-mono">{{ $r->queue->ticket_code }}</td>
                    <td>{{ $r->queue->service?->name ?? '-' }}</td>
                    <td><strong>{{ $r->full_name }}</strong></td>
                    <td>{{ $r->phone_number }}</td>
                    <td>{{ $r->institution_name }}</td>
                    <td class="text-center">
                        <span class="badge">{{ $r->institution_type }}</span>
                    </td>
                    <td class="text-center">{{ round(($r->queue->wait_duration_seconds ?? 0) / 60, 1) }}m</td>
                    <td class="text-center">{{ round(($r->queue->serve_duration_seconds ?? 0) / 60, 1) }}m</td>
                    <td>{{ $r->queue->user?->name ?? ($r->queue->counter?->name ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 16px; color: #64748b;">
                        Tidak ada data kunjungan yang tercatat untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bagian Pengesahan / Tanda Tangan Pimpinan -->
    <table class="signature-table">
        <tr>
            <td class="text-center">
                <p>Mengetahui,</p>
                <p><strong>Kepala BPS Kota Tasikmalaya</strong></p>
                <div class="sig-space"></div>
                <p><strong>(......................................................)</strong></p>
                <p style="font-size: 7.5pt; color: #64748b;">NIP. ...................................................</p>
            </td>
            <td class="text-center">
                <p>Tasikmalaya, {{ $printedAt }}</p>
                <p><strong>Penanggung Jawab PST</strong></p>
                <div class="sig-space"></div>
                <p><strong>(......................................................)</strong></p>
                <p style="font-size: 7.5pt; color: #64748b;">NIP. ...................................................</p>
            </td>
        </tr>
    </table>

</body>
</html>
