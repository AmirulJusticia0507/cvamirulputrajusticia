<?php
// ============================================================
// handlers/validasi_handler.php
// Logika validasi status sebuah Surat Tugas SPL.
// Digunakan oleh validasi_preview.php. Target integrasi:
//   - Abhipraya (PT. ASI PUDJIASTUTI AVIATION) — tetap di bawah DISKOMINFO DIY
// ============================================================

if (!function_exists('validateSurat')) {
    function validateSurat($conn, $id)
    {
        $id = (int) $id;

        $q = "
            SELECT
                s.id, s.nomor_surat, s.tanggal_surat, s.kota,
                s.pimpinan_nama, s.pimpinan_nip, s.pimpinan_jabatan,
                s.status, s.nomor_urut, s.kode_surat, s.bulan, s.tahun,
                t.unit_kerja
            FROM surat_tugas_splp s
            LEFT JOIN tim_pic_splp t ON t.surat_id = s.id
            WHERE s.id = $1
        ";
        $res = pg_query_params($conn, $q, [$id]);
        $s = pg_fetch_assoc($res);

        $result = [
            'surat'     => $s,
            'checks'    => [],
            'valid'     => false,
            'target'    => 'Abhipraya (PT. ASI PUDJIASTUTI AVIATION)',
            'issued_by' => 'DISKOMINFO DIY',
        ];

        if (!$s) {
            $result['checks'][] = ['label' => 'Surat ditemukan', 'valid' => false, 'note' => 'Data surat tidak ada.'];
            return $result;
        }

        $unitKerja = $s['unit_kerja'] ?? 'DISKOMINFO';
        $nomor = $s['nomor_surat'] ?? sprintf(
            "%03d/%s/%s/%s/%d",
            $s['nomor_urut'] ?? 1,
            $s['kode_surat'] ?? 'SPLP',
            strtoupper(preg_replace('/\s+/', '', $unitKerja)),
            bulanRomawi((int) $s['bulan'] ?: (int) date('n', strtotime($s['tanggal_surat'] ?? 'now'))),
            (int) ($s['tahun'] ?: date('Y', strtotime($s['tanggal_surat'] ?? 'now')))
        );

        // 1. Data wajib diisi
        $mandatory = [
            'nomor_surat'       => $nomor ?: null,
            'tanggal_surat'     => $s['tanggal_surat'],
            'kota'              => $s['kota'],
            'unit_kerja'        => $unitKerja,
            'pimpinan_nama'     => $s['pimpinan_nama'],
            'pimpinan_nip'      => $s['pimpinan_nip'],
            'pimpinan_jabatan'  => $s['pimpinan_jabatan'],
        ];
        foreach ($mandatory as $field => $val) {
            $ok = !empty($val);
            $result['checks'][] = [
                'label' => 'Data wajib ' . ucfirst(str_replace('_', ' ', $field)),
                'valid' => $ok,
                'note'  => $ok ? ($val) : 'Belum diisi.',
            ];
        }

        // 2. Minimal 1 PIC
        $picCount = (int) pg_fetch_result(
            pg_query_params($conn, "SELECT COUNT(*) FROM tim_pic_splp WHERE surat_id=$1", [$id]),
            0, 0
        );
        $result['checks'][] = [
            'label' => 'PIC (Tim Penanggung Jawab)',
            'valid' => $picCount > 0,
            'note'  => $picCount . ' orang',
        ];

        // 3. Minimal 1 uraian tugas
        $tugasCount = (int) pg_fetch_result(
            pg_query_params($conn, "SELECT COUNT(*) FROM uraian_tugas_splp WHERE surat_id=$1", [$id]),
            0, 0
        );
        $result['checks'][] = [
            'label' => 'Uraian Tugas',
            'valid' => $tugasCount > 0,
            'note'  => $tugasCount . ' item',
        ];

        // 4. Whitelist IP publik (diperlukan agar integrasi Abhipraya bisa dijangkau)
        $ipCount = (int) pg_fetch_result(
            pg_query_params($conn, "SELECT COUNT(*) FROM whitelist_ip WHERE surat_id=$1", [$id]),
            0, 0
        );
        $result['checks'][] = [
            'label' => 'Whitelist IP Publik (untuk ' . $result['target'] . ')',
            'valid' => $ipCount > 0,
            'note'  => $ipCount . ' IP terdaftar',
        ];

        // 5. Status harus terkunci (final)
        $locked = ($s['status'] === 'LOCKED');
        $result['checks'][] = [
            'label' => 'Status dikunci (final)',
            'valid' => $locked,
            'note'  => 'Status: ' . ($s['status'] ?? 'DRAFT'),
        ];

        // 6. Nomor surat valid format
        $nomorValid = !empty($s['nomor_surat']);
        $result['checks'][] = [
            'label' => 'Nomor Surat',
            'valid' => $nomorValid,
            'note'  => $nomorValid ? $nomor : 'Nomor surat belum di-generate.',
        ];

        // Verdict akhir: semua check wajib lolong
        $required = array_filter($result['checks'], function ($c) {
            // semua kecuali status kunci dianggap wajib; status kunci menentukan "final"
            return $c['label'] !== 'Status dikunci (final)';
        });
        $allRequiredOk = true;
        foreach ($required as $c) {
            if (!$c['valid']) { $allRequiredOk = false; break; }
        }
        $result['valid'] = $allRequiredOk;

        // tambahan flag: apakah sudah final (locked)
        $result['final'] = $locked;

        return $result;
    }
}

function bulanRomawi($bulan)
{
    $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
    return $map[(int)$bulan] ?? '?';
}
