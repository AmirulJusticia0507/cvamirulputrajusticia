<?php
// ============================================================
// validasi_preview.php
// Preview status VALID / TIDAK VALID sebuah Surat Tugas SPL.
// Fokus integrasi: Abhipraya (PT. ASI PUDJIASTUTI AVIATION)
//                   bagian dari DISKOMINFO DIY.
// ============================================================
include 'config.php';
include __DIR__ . '/handlers/validasi_handler.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID surat tidak valid');
}

$v = validateSurat($conn, $id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Preview Validasi Surat — <?= htmlspecialchars($v['surat']['nomor_surat'] ?? ('ID '.$id)) ?></title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-3xl mx-auto px-4 py-8">
  <div class="bg-white rounded-xl shadow p-6">

    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">Preview Validasi Surat Tugas SPL</h2>
      <span class="text-xs text-gray-400">Sistem: <?= htmlspecialchars($v['target']) ?> • <?= htmlspecialchars($v['issued_by']) ?></span>
    </div>

    <?php if (!$v['surat']): ?>
      <div class="p-4 rounded-lg bg-red-100 text-red-800">Data surat tidak ditemukan.</div>
    <?php else: ?>
    <div class="mb-4 text-sm text-gray-500">
      Nomor: <strong><?= htmlspecialchars($v['surat']['nomor_surat'] ?? '') ?></strong> ·
      Tanggal: <strong><?= htmlspecialchars($v['surat']['tanggal_surat'] ?? '') ?></strong> ·
      Unit: <strong><?= htmlspecialchars($v['surat']['unit_kerja'] ?? '') ?></strong>
    </div>

    <!-- Verdict -->
    <div id="verdict" class="mb-6">
      <?php if ($v['valid']): ?>
        <div class="flex items-center gap-3 p-4 rounded-xl bg-green-50 border-2 border-green-200 text-green-800">
          <i class="fas fa-check-circle text-3xl"></i>
          <div>
            <div class="text-xl font-bold">VALID</div>
            <div class="text-sm">Semua kriteria wajib terpenuhi. Surat siap pakai untuk integrasi <?= htmlspecialchars($v['target']) ?>.</div>
          </div>
        </div>
      <?php else: ?>
        <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border-2 border-red-200 text-red-800">
          <i class="fas fa-times-circle text-3xl"></i>
          <div>
            <div class="text-xl font-bold">TIDAK VALID</div>
            <div class="text-sm">Ada kriteria belum terpenuhi. Perbaiki dan cek kembali.</div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!$v['valid'] || !$v['final']): ?>
    <div class="mb-4 <?=($v['final']?'':'')?>">
      <?php if (!$v['final']): ?>
      <div class="p-3 mb-3 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200">
        <i class="fas fa-lock-open"></i> Status belum <strong>dikunci</strong>. Klik <em>🔒 Kunci Surat Tugas</em> pada halaman utama untuk men-final-kan.
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Checklist kriteria -->
    <h3 class="font-semibold mb-3">Checklist Kriteria</h3>
    <div class="divide-y border rounded-lg overflow-hidden">
      <?php foreach ($v['checks'] as $c): ?>
        <div class="flex items-center justify-between p-3 <?= $c['valid'] ? 'bg-green-50' : 'bg-red-50' ?>">
          <div class="flex items-center gap-2">
            <?= $c['valid']
              ? '<i class="fas fa-check text-green-600"></i>'
              : '<i class="fas fa-times text-red-600"></i>' ?>
            <span class="font-medium"><?= $c['label'] ?></span>
          </div>
          <span class="text-sm text-gray-600"><?= htmlspecialchars($c['note']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="mt-6 flex justify-between">
    <button onclick="location.href='surat_tugas_preview.php?id=<?= $id ?>'" class="inline-block px-4 py-2 rounded-lg font-semibold bg-gray-500 text-white hover:bg-gray-600">
      ← Kembali ke Preview Surat
    </button>
    <button onclick="location.reload()" class="inline-block px-4 py-2 rounded-lg font-semibold bg-blue-600 text-white hover:bg-blue-700">
      <i class="fas fa-redo"></i> Cek Ulang Validasi
    </button>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
