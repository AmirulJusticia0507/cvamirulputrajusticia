<?php
include 'config.php';

function e($s){
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die('Invalid ID');
}

$res = pg_query_params(
    $conn,
    "SELECT * FROM motivation_letters WHERE id = $1",
    [$id]
);

$d = pg_fetch_assoc($res);
if (!$d) {
    die('Motivation letter not found');
}

/**
 * Font decision by country
 */
$font = match ($d['country_code']) {
    'JP' => "'Noto Serif JP', serif",
    'CN' => "'Noto Serif SC', serif",
    'KR' => "'Noto Serif KR', serif",
    'EU' => "Georgia, serif",
    'US' => "Arial, Helvetica, sans-serif",
    default => "Arial, sans-serif"
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= e($d['title']) ?></title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">

<!-- Font CDN (only loaded if needed) -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP&family=Noto+Serif+SC&family=Noto+Serif+KR&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
    font-family: <?= $font ?>;
    background: #f4f6f8;
    padding: 40px;
}

.letter {
    max-width: 820px;
    margin: auto;
    background: #fff;
    padding: 48px 52px;
    border-radius: 10px;
    line-height: 1.75;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

h2 {
    font-size: 22px;
    margin-bottom: 24px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
}

p {
    font-size: 15px;
    white-space: pre-line;
}
.no-print {
    margin-bottom: 16px;
}

@media print {
    .no-print {
        display: none !important;
    }
}

/* Hide UI when printing / PDF */
@media print {
    body { background: none; padding: 0; }
    .letter {
        box-shadow: none;
        border-radius: 0;
        padding: 0;
    }
}
</style>
</head>

<body>
<div class="letter">
    <h2><?= e($d['title']) ?></h2>
    <p><?= nl2br(e($d['content'])) ?></p>
</div><br><br>

<div class="no-print" style="max-width:820px;margin:0 auto 20px auto;">
    <div class="flex gap-2">
        <a href="motivation_letter_pdf.php?id=<?= $d['id'] ?>" 
           class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-blue-600 text-white hover:bg-blue-700">
            ⬇ Download PDF
        </a>

        <a href="motivation_letter_list.php" 
           class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-gray-500 text-white hover:bg-gray-600">
            ⬅ Back to List
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
