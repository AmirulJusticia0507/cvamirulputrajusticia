<?php
session_start();
include 'config.php';

$preview_user_id = get_preview_user_id($conn);
if(!$preview_user_id){
    die('Profile not found');
}

// Ambil data
$work_exp = pg_query_params($conn, "SELECT * FROM work_experience WHERE user_id=$1 ORDER BY start_date DESC", [$preview_user_id]);
$skills   = pg_query_params($conn, "SELECT * FROM skills WHERE user_id=$1 ORDER BY id ASC", [$preview_user_id]);

// Helper
function e($str){ return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
function formatDate($date){ return $date ? date('m/Y', strtotime($date)) : ''; }

// Bullet gaya German (factual)
function generateGermanBullets($desc, $tech=[], $numbers=[]){
    $bullets = [];

    // Action
    if($tech){
        $bullets[] = "Entwicklung und Wartung von Systemen mit ".implode(", ", $tech).".";
    } else {
        $bullets[] = "Entwicklung und Wartung von Web- und Backend-Systemen.";
    }

    // Responsibility
    $bullets[] = "Verantwortlich für Systemintegration, Datenvalidierung und API-Schnittstellen.";

    // Result
    $results = [];
    foreach($numbers as $label => $val){
        if($val) $results[] = "$label: $val";
    }
    if($results){
        $bullets[] = "Ergebnisse: ".implode("; ", $results).".";
    }

    return $bullets;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Lebenslauf – Amirul Putra Justicia</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>

<style>
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #eef1f4;
    padding: 24px;
}

#cv {
    max-width: 820px;
    margin: auto;
    background: #fff;
    padding: 36px;
}

h1 {
    font-size: 26px;
    margin-bottom: 4px;
}

.meta {
    font-size: 13px;
    color: #444;
}

.section-title {
    margin-top: 18px;
    font-size: 17px;
    font-weight: 700;
    border-bottom: 2px solid #000;
    padding-bottom: 4px;
}

/* Experience */
.exp {
    margin-top: 12px;
    page-break-inside: avoid;
}

.exp-title {
    font-weight: 600;
    font-size: 15px;
}

.exp-meta {
    font-size: 12px;
    color: #555;
    margin-bottom: 4px;
}

.exp ul {
    padding-left: 18px;
    margin: 0;
}

.exp li {
    font-size: 13.5px;
    margin-bottom: 4px;
}

/* Print */
@media print {
    body {
        background: #fff;
        padding: 0;
    }
}
/* Posisi tombol di kanan, tetap visible saat scroll */
.cv-action-fixed {
    position: fixed;
    top: 50%;           /* tengah vertikal */
    right: 20px;        /* jarak dari kanan layar */
    transform: translateY(-50%);
    z-index: 1000;      /* pastikan di atas elemen lain */
}

/* Tombol rapih vertikal */
.cv-action-fixed a, .cv-action-fixed button {
    width: 210px;
    padding: 0.6rem 1rem;
    font-size: 14px;
    white-space: nowrap;
}
</style>
</head>

<body>
<!-- Tombol statis kanan -->
<div class="cv-action-fixed no-print">
    <div class="flex flex-col gap-2">
        <button id="btn-pdf" class="inline-block px-6 py-3 text-lg rounded-lg font-semibold text-center transition bg-green-600 text-white hover:bg-green-700">
            ⬇ Download PDF
        </button>
        <a href="preview_cv.php" class="inline-block px-6 py-3 text-lg rounded-lg font-semibold text-center transition bg-gray-500 text-white hover:bg-gray-600">
            ⬅ Back
        </a>
    </div>
</div>

<div id="cv">

    <!-- HEADER -->
    <h1>Amirul Putra Justicia</h1>
    <div class="meta">
        Web Systems Engineer (GovTech)<br>
        📧 amirulputra0507@gmail.com · 📱 +62-821-3440-2383 · LinkedIn
    </div>

    <!-- PROFILE -->
    <div class="section-title">Profil</div>
    <p style="font-size:14px">
        Fullstack Engineer mit über 6 Jahren Erfahrung im Bereich GovTech.
        Schwerpunkt auf Systemintegration, sichere Datenverarbeitung und
        skalierbare Webplattformen.
    </p>

    <!-- SKILLS -->
    <div class="section-title">Kompetenzen</div>
    <p style="font-size:13.5px">
        Backend: PHP (Laravel), Node.js, Django ·
        Frontend: React, Vue.js ·
        Datenbanken: PostgreSQL, MySQL ·
        Integration: REST / SOAP APIs
    </p>

    <!-- Languages -->
    <?php
    $langs = pg_query_params($conn, "SELECT * FROM languages WHERE user_id=$1 ORDER BY id ASC", [$preview_user_id]);
    if(pg_num_rows($langs) > 0):
    ?>
    <div class="section-title">Languages</div>
    <div class="skill-block">
        <?php while($l = pg_fetch_assoc($langs)): ?>
            <?= e($l['language_name']); ?> (<?= e($l['proficiency']); ?>) · 
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- EXPERIENCE -->
    <div class="section-title">Berufserfahrung</div>

    <?php while($w = pg_fetch_assoc($work_exp)):
        $present = in_array($w['present'], ['t', true, 1], true);
        $numbers = [
            "Integrierte Behörden" => $w['integrated'] ?? null,
            "Systemverfügbarkeit" => $w['uptime'] ?? null,
            "Fehlerreduktion" => $w['error_reduction'] ?? null
        ];
        $techStack = $w['stack'] ? array_map('trim', explode(",", $w['stack'])) : [];
        $bullets = generateGermanBullets($w['description'], $techStack, $numbers);
    ?>
    <div class="exp">
        <div class="exp-title">
            Web Systems Engineer – <?= e($w['company']) ?>
        </div>
        <div class="exp-meta">
            <?= formatDate($w['start_date']); ?> –
            <?= $present ? 'Heute' : formatDate($w['end_date']); ?>
            · <?= e($w['location']); ?>
        </div>
        <ul>
            <?php foreach($bullets as $b): ?>
                <li><?= e($b); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endwhile; ?>

    <!-- FEATURED PROJECTS -->
    <?php $pfTitle = 'Ausgewählte Projekte'; $pfLinkLabel = 'Auf GitHub ansehen'; include __DIR__ . '/includes/portfolio_section.php'; ?>

    <!-- EDUCATION -->
    <div class="section-title">Ausbildung</div>
    <p style="font-size:13.5px">
        Universitas Ahmad Dahlan – Informatik (2014 – 2018)
    </p>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.getElementById('btn-pdf').addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Generating PDF...';

    try {
        const cv = document.getElementById('cv');
        const omw = cv.style.maxWidth; const op = cv.style.padding;
        cv.style.maxWidth = '595px'; cv.style.padding = '24px';

        const canvas = await html2canvas(cv, {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true
        });
        cv.style.maxWidth = omw || ''; cv.style.padding = op || '';

        const { jsPDF } = window.jspdf;
        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const pdf = new jsPDF({
            orientation: 'p',
            unit: 'pt',
            format: 'a4',
            compress: true
        });

        const pw = pdf.internal.pageSize.getWidth(), ph = pdf.internal.pageSize.getHeight();
        const mg = 28, iw = pw - mg * 2, ih = (canvas.height * iw) / canvas.width, uh = ph - mg * 2;
        let offset = 0, page = 0;
        do {
            if (page > 0) pdf.addPage();
            pdf.addImage(imgData, 'JPEG', mg, mg - offset, iw, ih);
            offset += uh; page++;
        } while (offset < ih);

        pdf.save('Lebenslauf_Amirul_Putra_Justicia.pdf');
    } catch (err) {
        alert('Failed to generate PDF');
        console.error(err);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>

<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
