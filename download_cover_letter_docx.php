<?php
require __DIR__ . '/vendor/autoload.php';
include 'config.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;

if (!isset($_GET['id'])) {
    die('ID tidak ditemukan');
}

$id = (int) $_GET['id'];

$query = "
SELECT 
    cl.subject,
    cl.content,
    ad.company_name,
    ad.position
FROM cover_letters cl
JOIN apply_destination ad ON cl.destination_id = ad.id
WHERE cl.id = $id
";

$result = pg_query($conn, $query);
$data = pg_fetch_assoc($result);

if (!$data) {
    die('Data cover letter tidak ditemukan');
}

$phpWord = new PhpWord();

$section = $phpWord->addSection([
    'marginTop' => Converter::cmToTwip(2),
    'marginLeft' => Converter::cmToTwip(2),
    'marginRight' => Converter::cmToTwip(2),
    'marginBottom' => Converter::cmToTwip(2),
]);

$section->addText(date('F d, Y'));
$section->addTextBreak();

$section->addText("Hiring Manager");
$section->addText($data['company_name']);
$section->addText($data['position']);
$section->addTextBreak();

$section->addText("Dear Hiring Manager,");
$section->addTextBreak();

foreach (explode("\n", $data['content']) as $paragraph) {
    $section->addText(trim($paragraph));
    $section->addTextBreak();
}

$section->addTextBreak();
$section->addText("Sincerely,");
$section->addText("Amirul Putra Justicia", ['bold' => true]);

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="Cover_Letter_AmirulPutraJusticia.docx"');
header('Cache-Control: max-age=0');

IOFactory::createWriter($phpWord, 'Word2007')->save('php://output');
exit;
