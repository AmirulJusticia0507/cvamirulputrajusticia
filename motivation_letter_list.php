<?php
include 'config.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
$fc = $_GET['country_code'] ?? '';
$fl = $_GET['language_code'] ?? '';

$sql = "SELECT * FROM motivation_letters WHERE 1=1";
$params = [];
$i = 1;

if ($fc) {
    $sql .= " AND country_code = $" . $i;
    $params[] = $fc;
    $i++;
}
if ($fl) {
    $sql .= " AND language_code = $" . $i;
    $params[] = $fl;
    $i++;
}

$sql .= " ORDER BY created_at DESC";
$res = pg_query_params($conn, $sql, $params);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Motivation Letters</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* Posisi tombol di kanan, tetap visible saat scroll */
.cv-action-fixed {
    position: fixed;
    top: 50%;           /* tengah vertikal */
    right: 20px;        /* jarak dari kanan layar */
    transform: translateY(-50%);
    z-index: 1000;      /* pastikan di atas elemen lain */
}

/* Tombol rapih vertikal */
.cv-action-fixed .btn {
    width: 210px;       /* diperbesar */
    padding: 0.6rem 1rem;  /* lebih nyaman untuk klik */
    font-size: 14px;       /* lebih readable */
    white-space: nowrap;
}
</style>
</head>
<body class="bg-light">
<div class="cv-action-fixed no-print">
    <div class="d-flex flex-column gap-2">
        <a href="index.php" class="btn btn-secondary btn-lg">⬅ Back</a>
    </div>
</div>
<div class="container py-4">
<h3 class="mb-3">📄 Motivation Letters</h3>

<form method="get" class="row g-2 mb-3">
<div class="col-md-4">
<select name="country_code" class="form-select">
<option value="">All Countries</option>
<option value="JP" <?= $fc==='JP'?'selected':'' ?>>Japan</option>
<option value="EU" <?= $fc==='EU'?'selected':'' ?>>Europe</option>
<option value="US" <?= $fc==='US'?'selected':'' ?>>United States</option>
</select>
</div>

<div class="col-md-4">
<select name="language_code" class="form-select">
<option value="">All Languages</option>
<option value="ja" <?= $fl==='ja'?'selected':'' ?>>Japanese</option>
<option value="en" <?= $fl==='en'?'selected':'' ?>>English</option>
<option value="fr" <?= $fl==='fr'?'selected':'' ?>>French</option>
</select>
</div>

<div class="col-md-4">
<button class="btn btn-primary w-100">🔍 Filter</button>
</div>
</form>

<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>Title</th>
<th>Country</th>
<th>Lang</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php while ($r = pg_fetch_assoc($res)): ?>
<tr>
<td><?= e($r['title']) ?></td>
<td><?= e($r['country_code']) ?></td>
<td><?= e(strtoupper($r['language_code'])) ?></td>
<td>
<a class="btn btn-sm btn-info" href="motivation_letter_preview.php?id=<?= $r['id'] ?>">👁</a>
<a class="btn btn-sm btn-warning" href="motivation_letter_form.php?id=<?= $r['id'] ?>">✏</a>
<a class="btn btn-sm btn-danger"
   onclick="return confirm('Delete this letter?')"
   href="motivation_letter_delete.php?id=<?= $r['id'] ?>">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<a href="motivation_letter_form.php" class="btn btn-success">➕ New Letter</a>
</div>
</body>
</html>
