<?php
include 'config.php';

$surats = pg_query($conn, "
SELECT
    s.id,
    s.nomor_surat,
    s.tanggal_surat,
    t.unit_kerja,
    t.nama AS nama_pic
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Surat Tugas SPLP</h3>
        <a href="surat_tugas_form.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Buat Surat Baru
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal</th>
                        <th>Unit Kerja</th>
                        <th>PIC</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; while($s = pg_fetch_assoc($surats)): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>

                        <!-- LINK DETAIL -->
                        <td>
                            <a href="surat_tugas_preview.php?id=<?= $s['id'] ?>" target="_blank">
                                <?= htmlspecialchars($s['nomor_surat']) ?>
                            </a>
                        </td>

                        <td><?= date('d-m-Y', strtotime($s['tanggal_surat'])) ?></td>
                        <td><?= htmlspecialchars($s['unit_kerja']) ?></td>
                        <td><?= htmlspecialchars($s['nama_pic']) ?></td>
                        <td class="text-center">
                            <a href="surat_tugas_preview.php?id=<?= $s['id'] ?>" 
                               class="btn btn-sm btn-info" target="_blank">
                               Detail
                            </a>
                            <a href="surat_tugas_edit.php?id=<?= $s['id'] ?>" 
                               class="btn btn-sm btn-warning">
                               Edit
                            </a>
                            <a href="surat_tugas_delete.php?id=<?= $s['id'] ?>" 
                               class="btn btn-sm btn-danger"
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

</body>
</html>
