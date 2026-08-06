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

$experiences = pg_query($conn, "SELECT company, position, start_date, end_date, present, stack, location, status_kerja, description, project, integrated, uptime, error_reduction, users_onboarded FROM work_experience ORDER BY start_date DESC");
$expA = [];
while ($e = pg_fetch_assoc($experiences)) $expA[] = $e;

/* ------------------------------------------------------------------
   Helper: buat bullet PRAQ (Problem / Role / Action / Result)
   dari data pengalaman riil di database.
-------------------------------------------------------------------*/
function buildPRAQ($desc, $tech=[]) {
    $bullets = [];
    // Problem: kalimat pertama dari description (atau challenge generik)
    $problem = preg_split('/[.\r\n]+/', $desc)[0] ?: "Addressed technical challenges while building GovTech web platforms";
    $bullets[] = "Problem: " . trim($problem) . ".";

    // Role
    $bullets[] = "Role: Led implementation of backend, frontend, and integration workflows.";

    // Action
    if ($tech) {
        $bullets[] = "Action: Developed and maintained using " . implode(", ", $tech) . "; applied REST/SOAP APIs, data validation, and integration pipelines.";
    } else {
        $bullets[] = "Action: Executed system development, optimized workflows, and ensured data consistency.";
    }

    // Result (diisi angka-angka jika ada)
    $results = [];
    if (!empty($tech)) {
        // tech-based result contoh (bisa dikembangkan)
        $results[] = "Delivered systems with measurable reliability improvements";
    }
    return $bullets;
}

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

// sebariskan 2-3 pencapaian yang relevan, dengan data riil dari DB
if (!empty($top)) {
    $lines[] = "";
    $lines[] = "Below are a few relevant achievements from my recent work:";
    foreach ($top as $e) {
        $period = fmtPeriod($e['start_date'], $e['end_date'], $present ?? 'f');
        $comp   = $e['company'];
        $loc    = $e['location'] ?: 'Indonesia';
        $pos    = $e['position'];

        // stack
        $tech = $e['stack'] ? array_filter(array_map('trim', explode(',', $e['stack']))) : [];
        $techStr = !empty($tech) ? '<em>'.implode(', ', $tech).'</em>' : '';

        // build PRAQ bullets from the real description + metrics
        $praq = buildPRAQ($e['description'] ?: 'Spearheaded end-to-end development and integration workflows for mission-critical government systems.', $tech);

        // append real-world Result metrics if present
        $metrics = [];
        if (!empty($e['integrated']))    $metrics[] = "Integrated {$e['integrated']}";
        if (!empty($e['uptime']))        $metrics[] = "Uptime {$e['uptime']}";
        if (!empty($e['error_reduction'])) $metrics[] = "Error reduction {$e['error_reduction']}";
        if (!empty($e['users_onboarded']))$metrics[] = "Users onboarded {$e['users_onboarded']}";
        if (!empty($metrics)) $praq[] = "Result: " . implode('; ', $metrics) . ".";

        $header = "**{$pos}** — {$comp} ({$period}, {$loc})" . ($techStr ? " · Tech: {$techStr}" : "");
        $lines[] = $header;
        foreach ($praq as $b) {
            $lines[] = "  • " . $b;
        }
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
