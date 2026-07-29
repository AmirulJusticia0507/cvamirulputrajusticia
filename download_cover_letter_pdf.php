<?php
require __DIR__ . '/vendor/autoload.php';
include 'config.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Invalid ID');
}

$q = "
SELECT 
    cl.content,
    cl.created_at,
    ad.company_name,
    ad.position
FROM cover_letters cl
JOIN apply_destination ad ON ad.id = cl.destination_id
WHERE cl.id = $1
";
$res = pg_query_params($conn, $q, [$id]);
$data = pg_fetch_assoc($res);

if (!$data) {
    die('Cover letter not found');
}

$mpdf = new \Mpdf\Mpdf([
    'format' => 'A4',
    'margin_top' => 25,
    'margin_bottom' => 25,
    'margin_left' => 25,
    'margin_right' => 25
]);

$html = '
<style>
body {
    font-family: "Times New Roman", serif;
    font-size: 12pt;
}
p {
    text-align: justify;
}
.date {
    text-align: right;
}
</style>

<p class="date">' . date('F d, Y', strtotime($data['created_at'])) . '</p>

<p>
Hiring Manager<br>
<strong>' . htmlspecialchars($data['company_name']) . '</strong><br>
' . htmlspecialchars($data['position']) . '
</p>

<p>Dear Hiring Manager,</p>

<p>' . nl2br(htmlspecialchars($data['content'])) . '</p>

<p>
Sincerely,<br><br>
<strong>Amirul Putra Justicia</strong>
</p>
';

$mpdf->WriteHTML($html);
$mpdf->Output('Surat Lamaran_Amirul Putra Justicia_' . $id . '.pdf', 'D');
