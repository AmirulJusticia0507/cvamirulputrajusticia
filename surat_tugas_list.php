<?php
include 'config.php';
require_once __DIR__ . '/handlers/validasi_handler.php';

$surats = pg_query($conn, "
SELECT
    s.id,
    s.nomor_surat,
    s.tanggal_surat,
    s.status,
    t.unit_kerja,
    t.nama AS nama_pic,
    (SELECT COUNT(*) FROM tim_pic_splp WHERE surat_id=s.id) AS pic_count,
    (SELECT COUNT(*) FROM uraian_tugas_splp WHERE surat_id=s.id) AS tugas_count,
    (SELECT COUNT(*) FROM whitelist_ip WHERE surat_id=s.id) AS ip_count
FROM surat_tugas_splp s
LEFT JOIN tim_pic_splp t
    ON t.surat_id = s.id
   AND t.is_pic_utama = true
ORDER BY s.tanggal_surat DESC
");

if (!$surats) {
    die(pg_last_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Surat Tugas SPLP</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-6xl mx-auto px-4 mt-4">
    <div class="flex justify-between items-center mb-3">
        <h3>Daftar Surat Tugas SPLP</h3>
        <a href="surat_tugas_form.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-green-600 text-white hover:bg-green-700">
            <i class="fas fa-plus-circle"></i> Buat Surat Baru
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-4">
        <div class="p-4">
            <table class="w-full border-collapse border border-gray-200">
                <thead class="bg-gray-800 text-white text-center">
                    <tr>
                        <th class="border border-gray-200 p-2" width="5%">No</th>
                        <th class="border border-gray-200 p-2">Nomor Surat</th>
                        <th class="border border-gray-200 p-2">Tanggal</th>
                        <th class="border border-gray-200 p-2">Unit Kerja</th>
                        <th class="border border-gray-200 p-2">PIC</th>
                        <th class="border border-gray-200 p-2" width="10%">Validasi</th>
                        <th class="border border-gray-200 p-2" width="25%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; while($s = pg_fetch_assoc($surats)): ?>
                    <tr class="hover:bg-gray-100">
                        <td class="border border-gray-200 p-2 text-center"><?= $no++ ?></td>

                        <!-- LINK DETAIL -->
                        <td class="border border-gray-200 p-2">
                            <a href="surat_tugas_preview.php?id=<?= $s['id'] ?>" target="_blank">
                                <?= htmlspecialchars($s['nomor_surat']) ?>
                            </a>
                        </td>

                        <td class="border border-gray-200 p-2"><?= date('d-m-Y', strtotime($s['tanggal_surat'])) ?></td>
                        <td class="border border-gray-200 p-2"><?= htmlspecialchars($s['unit_kerja']) ?></td>
                        <td class="border border-gray-200 p-2"><?= htmlspecialchars($s['nama_pic']) ?></td>

                        <!-- STATUS VALIDASI DINAMIS -->
                        <td class="border border-gray-200 p-2 text-center">
                            <?php
                            $valid = ($s['pic_count'] > 0 && $s['tugas_count'] > 0 && $s['ip_count'] > 0 && !empty($s['nomor_surat']) && !empty($s['tanggal_surat']) && !empty($s['unit_kerja']));
                            $final = ($s['status'] === 'LOCKED');
                            ?>
                            <span class="inline-block px-2 py-1 text-xs rounded-full font-semibold
                                <?= $valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $valid ? ($final ? 'VALID' : 'VALID*') : 'TIDAK VALID' ?>
                            </span>
                            <?php if($valid && !$final): ?>
                                <i class="fas fa-exclamation-triangle text-yellow-500" title="Kunci surat untuk status final"></i>
                            <?php endif; ?>
                        </td>

                        <td class="border border-gray-200 p-2 text-center">
                            <a href="surat_tugas_preview.php?id=<?= $s['id'] ?>" 
                               class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-cyan-500 text-white hover:bg-cyan-600" target="_blank">
                               Detail
                            </a>
                            <a href="validasi_preview.php?id=<?= $s['id'] ?>" 
                               class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-emerald-600 text-white hover:bg-emerald-700" target="_blank">
                               <i class="fas fa-clipboard-check"></i> Validasi
                            </a>
                            <a href="surat_tugas_edit.php?id=<?= $s['id'] ?>" 
                               class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-yellow-500 text-white hover:bg-yellow-600">
                               Edit
                            </a>
                            <a href="surat_tugas_delete.php?id=<?= $s['id'] ?>" 
                               class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-red-600 text-white hover:bg-red-700"
                               onclick="return confirm('Yakin hapus surat ini?')">
                               Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
