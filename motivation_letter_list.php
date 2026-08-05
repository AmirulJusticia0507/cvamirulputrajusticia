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
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
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
.cv-action-fixed a {
    width: 210px;
    padding: 0.6rem 1rem;
    font-size: 14px;
    white-space: nowrap;
    display: block;
    text-align: center;
}
</style>
</head>
<body class="bg-gray-100">
<div class="cv-action-fixed no-print">
    <div class="flex flex-col gap-2">
        <a href="index.php" class="inline-block px-6 py-3 text-lg rounded-lg font-semibold text-center transition whitespace-nowrap bg-gray-500 text-white hover:bg-gray-600">⬅ Back</a>
    </div>
</div>
<div class="max-w-6xl mx-auto px-4 py-4">
<h3 class="mb-3">📄 Motivation Letters</h3>

<form method="get" class="grid grid-cols-1 md:grid-cols-12 gap-2 mb-3">
<div class="md:col-span-4">
<select name="country_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
<option value="">All Countries</option>
<option value="JP" <?= $fc==='JP'?'selected':'' ?>>Japan</option>
<option value="EU" <?= $fc==='EU'?'selected':'' ?>>Europe</option>
<option value="US" <?= $fc==='US'?'selected':'' ?>>United States</option>
</select>
</div>

<div class="md:col-span-4">
<select name="language_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
<option value="">All Languages</option>
<option value="ja" <?= $fl==='ja'?'selected':'' ?>>Japanese</option>
<option value="en" <?= $fl==='en'?'selected':'' ?>>English</option>
<option value="fr" <?= $fl==='fr'?'selected':'' ?>>French</option>
</select>
</div>

<div class="md:col-span-4">
<button class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-blue-600 text-white hover:bg-blue-700 w-full">🔍 Filter</button>
</div>
</form>

<table class="w-full border-collapse border border-gray-200">
<thead class="bg-gray-800 text-white">
<tr>
<th class="border border-gray-200 p-2">Title</th>
<th class="border border-gray-200 p-2">Country</th>
<th class="border border-gray-200 p-2">Lang</th>
<th class="border border-gray-200 p-2">Action</th>
</tr>
</thead>
<tbody>
<?php while ($r = pg_fetch_assoc($res)): ?>
<tr class="even:bg-gray-50">
<td class="border border-gray-200 p-2"><?= e($r['title']) ?></td>
<td class="border border-gray-200 p-2"><?= e($r['country_code']) ?></td>
<td class="border border-gray-200 p-2"><?= e(strtoupper($r['language_code'])) ?></td>
<td class="border border-gray-200 p-2">
<a class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-cyan-500 text-white hover:bg-cyan-600" href="motivation_letter_preview.php?id=<?= $r['id'] ?>">👁</a>
<a class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-yellow-500 text-white hover:bg-yellow-600" href="motivation_letter_form.php?id=<?= $r['id'] ?>">✏</a>
<a class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-red-600 text-white hover:bg-red-700"
   onclick="return confirm('Delete this letter?')"
   href="motivation_letter_delete.php?id=<?= $r['id'] ?>">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<a href="motivation_letter_form.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-green-600 text-white hover:bg-green-700">➕ New Letter</a>
</div>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
