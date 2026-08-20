<?php
error_reporting(0);
ini_set('display_errors', 0);

ob_start();

session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

$preview_user_id = get_preview_user_id($conn);
if(!$preview_user_id){
    http_response_code(404);
    exit;
}

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;

/* ================= INIT ================= */
$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Arial');
$phpWord->setDefaultFontSize(11);

$section = $phpWord->addSection([
    'marginTop'    => Converter::cmToTwip(1.5),
    'marginBottom' => Converter::cmToTwip(1.5),
    'marginLeft'   => Converter::cmToTwip(2),
    'marginRight'  => Converter::cmToTwip(2),
]);

/* ================= HEADER ================= */
$section->addText(
    'Amirul Putra Justicia',
    ['bold' => true, 'size' => 16]
);

$section->addText(
    'Full Stack Developer',
    ['italic' => true]
);

$section->addText(
    'Email: amirulputra0507@gmail.com | Phone: +62-821-3440-2383',
    ['size' => 10]
);

$section->addTextBreak(1);

/* ================= SKILLS ================= */
$section->addText('Technical Skills', ['bold' => true]);

$skills = pg_query_params($conn, "SELECT skill_name, level FROM skills WHERE user_id=$1 ORDER BY id ASC", [$preview_user_id]);
while ($s = pg_fetch_assoc($skills)) {
    $section->addText("- {$s['skill_name']} ({$s['level']})", ['size' => 10]);
}

$section->addTextBreak(1);

/* ================= EXPERIENCE ================= */
$section->addText('Work Experience', ['bold' => true]);

$work = pg_query_params($conn, "SELECT * FROM work_experience WHERE user_id=$1 ORDER BY start_date DESC", [$preview_user_id]);
while ($w = pg_fetch_assoc($work)) {

    $section->addText(
        "{$w['company']} – {$w['position']}",
        ['bold' => true]
    );

    $section->addText(
        "{$w['start_date']} – {$w['end_date']}",
        ['italic' => true, 'size' => 10]
    );

    if (!empty($w['description'])) {
        $section->addText($w['description'], ['size' => 10]);
    }

    $section->addTextBreak(1);
}

/* ================= OUTPUT ================= */
ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="CV_AmirulPutraJusticia.docx"');
header('Cache-Control: max-age=0');

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save('php://output');
exit;
