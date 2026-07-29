<?php
include 'config.php';

// Ambil data
$work_exp = pg_query($conn, "SELECT * FROM work_experience ORDER BY start_date DESC");
$skills   = pg_query($conn, "SELECT * FROM skills ORDER BY id ASC");

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
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
.cv-action-fixed .btn {
    width: 210px;       /* diperbesar */
    padding: 0.6rem 1rem;  /* lebih nyaman untuk klik */
    font-size: 14px;       /* lebih readable */
    white-space: nowrap;
}
</style>
</head>

<body>
<!-- Tombol statis kanan -->
<div class="cv-action-fixed no-print">
    <div class="d-flex flex-column gap-2">
        <button id="btn-pdf" class="btn btn-success btn-lg">
            ⬇ Download PDF
        </button>
        <a href="preview_cv.php" class="btn btn-secondary btn-lg">
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
        const { jsPDF } = window.jspdf;
        const cv = document.getElementById('cv');

        const canvas = await html2canvas(cv, {
            scale: window.devicePixelRatio * 2,
            backgroundColor: '#ffffff',
            useCORS: true
        });

        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const pdf = new jsPDF({
            orientation: 'p',
            unit: 'pt',
            format: 'a4',
            compress: true
        });

        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        const imgHeight = (canvas.height * pdfWidth) / canvas.width;

        let heightLeft = imgHeight;
        let position = 0;

        while (heightLeft > 0) {
            pdf.addImage(imgData, 'JPEG', 0, position, pdfWidth, imgHeight);
            heightLeft -= pdfHeight;
            position -= pdfHeight;
            if (heightLeft > 0) pdf.addPage();
        }

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

</body>
</html>
