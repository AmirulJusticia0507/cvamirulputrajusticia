<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

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
 * Font by country
 */
$font = match ($d['country_code']) {
    'JP' => "'Noto Serif JP', serif",
    'CN' => "'Noto Serif SC', serif",
    'KR' => "'Noto Serif KR', serif",
    'EU' => "Georgia, serif",
    'US' => "Arial, Helvetica, sans-serif",
    default => "Arial, sans-serif"
};

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP&family=Noto+Serif+SC&family=Noto+Serif+KR&display=swap" rel="stylesheet">
<style>
body {
    font-family: '.$font.';
    font-size: 12pt;
    line-height: 1.7;
}
h2 {
    border-bottom: 2px solid #000;
    padding-bottom: 6px;
    margin-bottom: 18px;
}
</style>
</head>
<body>
<h2>'.e($d['title']).'</h2>
<p>'.nl2br(e($d['content'])).'</p>
</body>
</html>
';

$pdf = new Dompdf([
    'defaultFont' => 'Arial'
]);

$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();

$filename = 'Motivation_Letter_'.$d['country_code'].'_'.strtoupper($d['language_code']).'.pdf';

$pdf->stream($filename, ['Attachment' => true]);
