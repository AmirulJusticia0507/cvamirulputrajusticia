<?php
/**
 * ajax_cover_letter.php
 * Endpoint AJAX: menghasilkan (generate) isi surat lamaran yang *dinamis*
 * berdasarkan company, position, dan keyword yang ketik user.
 *
 * Konten disusun dengan menarik data riil dari database (profil, pengalaman, skill)
 * lalu menyocokkan (keyword matching) pengalaman yang paling relevan.
 */

include 'config.php';

header('Content-Type: application/json');

$company  = trim($_POST['company'] ?? '');
$position = trim($_POST['position'] ?? '');
$keywords = trim($_POST['keywords'] ?? '');
$mode     = trim($_POST['mode'] ?? 'en');   // 'en' | 'de'

if ($company === '' || $position === '') {
    echo json_encode(['ok' => false, 'message' => 'Company dan position wajib diisi.']);
    exit;
}

/* ------------------------------------------------------------------
   Ambil data master dari DB
-------------------------------------------------------------------*/
$profileRes = pg_query($conn, "SELECT * FROM profile ORDER BY id DESC LIMIT 1");
$profile    = pg_fetch_assoc($profileRes);

$skills   = pg_query($conn, "SELECT skill_name, level, years FROM skills ORDER BY id ASC");
$skillsA  = [];
while ($s = pg_fetch_assoc($skills)) $skillsA[] = $s;

$experiences = pg_query($conn, "SELECT company, position, start_date, end_date, present, stack, location, status_kerja FROM work_experience ORDER BY start_date DESC");
$expA = [];
while ($e = pg_fetch_assoc($experiences)) $expA[] = $e;

/* ------------------------------------------------------------------
   Helper: pencocokan keyword
-------------------------------------------------------------------*/
$kw = strtolower($keywords);
$kwList = $kw !== '' ? preg_split('/[\s,]+/', $kw) : [];
$posLower = strtolower($position . ' ' . $company);

function scoreItem($text, $kwList) {
    $score = 0;
    $t = strtolower($text);
    foreach ($kwList as $k) {
        if ($k === '') continue;
        $score += substr_count($t, $k) * 2;
        // bonus mirip kata
        $score += similar_text($t, $k);
    }
    return $score;
}

/* Ranking pengalaman: urutkan berdasarkan keyword relevance + skill */
foreach ($expA as &$e) {
    $stackStr = $e['stack'] ?? '';
    $posStr   = $e['position'] ?? '';
    $score    = scoreItem($stackStr, $kwList) + scoreItem($posStr, $kwList) + scoreItem($posLower, []);
    // semua posisi dapat skor dasar
    $score += 1;
    $e['_score'] = $score;
}
unset($e);
usort($expA, function($a, $b){ return $b['_score'] <=> $a['_score']; });

/* Skill yang relevan */
$relevantSkills = [];
foreach ($skillsA as $sk) {
    $sc = scoreItem($sk['skill_name'], $kwList);
    $sc += scoreItem($sk['skill_name'], explode(' ', $posLower));
    if ($sc > 0) $relevantSkills[$sk['skill_name']] = $sc;
}
arsort($relevantSkills);
$relSkills = array_keys($relevantSkills);
if (empty($relSkills)) $relSkills = array_column($skillsA, 'skill_name');
$relSkills = array_slice($relSkills, 0, 6);

/* ------------------------------------------------------------------
   Bangun subject + content secara dinamis
-------------------------------------------------------------------*/
$fullName = $profile['full_name'] ?? 'Amirul Putra Justicia';
$headline = $profile['headline'] ?? '';
$summary  = $profile['summary'] ?? '';

$subject = "Application for {$position} – " . ($company ?: 'the Company');

/* Format tanggal pengalaman */
function fmtPeriod($start, $end, $present) {
    $s = $start ? date('M Y', strtotime($start)) : '';
    if ($present === 't' || $present == 1 || $present === '1') {
        return $s . ' – Present';
    }
    $e = $end ? date('M Y', strtotime($end)) : 'Present';
    return $s . ' – ' . $e;
}

/* 3 pengalaman paling relevan */
$top = array_slice($expA, 0, 3);

$lines = [];
$lines[] = "Dear Hiring Manager,";
$lines[] = "";
$lines[] = "I am writing to express my strong interest in the **{$position}** role at **{$company}**. With " . ($profile['full_name'] ?? 'my') . " bringing over 6 years of experience in building interoperable web platforms and secure data workflows in GovTech, I have a proven track record of delivering robust, mission-critical systems.";

// highlight skill yang relevan
if (!empty($relSkills)) {
    $skillText = implode(', ', array_map(function($s){ return '<em>'.$s.'</em>'; }, $relSkills));
    $lines[] = "My technical expertise — including {$skillText} — aligns directly with the requirements of this position.";
}

// sebariskan 2-3 poin pencapaian yang relevan
if (!empty($top)) {
    $lines[] = "";
    $lines[] = "A relevant achievement from my recent work includes:";
    foreach ($top as $e) {
        $period = fmtPeriod($e['start_date'], $e['end_date'], $e['present']);
        $stack  = $e['stack'] ? '<em>'.implode(', ', array_map('trim', explode(',', $e['stack']))).'</em>' : '';
        $comp   = $e['company'];
        $pos    = $e['position'];
        $loc    = $e['location'];
        $desc   = $e['description'] ?: 'Spearheaded end-to-end development and integration workflows for mission-critical government systems.';
        $lines[] = "— “{$desc}” at {$comp} as {$pos} ({$period}, {$loc})" . ($stack ? ", using {$stack}" : "") . ".";
    }
}

// summary singkat
if ($summary) {
    $lines[] = "";
    $lines[] = "Beyond the technical work, my focus centers on " . $summary;
}

$lines[] = "";
$lines[] = "I would welcome the opportunity to discuss how my experience in GovTech and interoperable web systems can contribute to {$company}. Thank you for your consideration.";
$lines[] = "";
$lines[] = "Sincerely,";
$lines[] = $fullName;
if ($headline) $lines[] = $headline;

$content = implode("\n", $lines);

echo json_encode([
    'ok'         => true,
    'subject'    => $subject,
    'content'    => $content,
    'skills'     => $relSkills,
    'experience_count' => count($expA),
]);
