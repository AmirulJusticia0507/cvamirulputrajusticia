<?php
include 'config.php';

$work_exp = pg_query($conn, "SELECT * FROM work_experience ORDER BY start_date DESC");
$skills   = pg_query($conn, "SELECT * FROM skills ORDER BY id ASC");

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
function formatDate($date) {
    return $date ? date('M Y', strtotime($date)) : '';
}
function generatePRAQ($descText, $tech = []) {
    $bullets = [];
    $problem = strtok($descText, ".") ?: "Problème identifié";

    $bullets[] = "Problème : {$problem}.";
    $bullets[] = "Rôle : Développement backend, frontend et intégration.";

    $bullets[] = $tech
        ? "Action : Développement avec " . implode(", ", $tech) . " ; APIs REST, validation et pipelines."
        : "Action : Optimisation des workflows et fiabilité des données.";

    $bullets[] = "Résultat : Amélioration mesurable de la stabilité, performance et intégrité.";

    return $bullets;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>CV – Amirul Putra Justicia (FR Modern)</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">

<script src="https://cdn.tailwindcss.com"></script>

<style>
body {
    font-family: Inter, Arial, sans-serif;
    background: #f0f2f5;
    padding: 20px;
}
#cv {
    max-width: 820px;
    margin: auto;
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 6px 25px rgba(0,0,0,.08);
}
.cv-header {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}
.cv-header h1 {
    font-size: 30px;
    margin: 0;
}
.header-meta {
    font-size: 13.5px;
    color: #555;
    text-align: right;
}
.summary {
    margin-top: 14px;
    font-size: 15px;
    line-height: 1.6;
}
.section-title {
    margin-top: 30px;
    font-size: 19px;
    font-weight: 700;
    border-bottom: 2px solid #0d6efd;
    padding-bottom: 6px;
}
.skill-block span {
    display: inline-block;
    background: #0d6efd;
    color: #fff;
    padding: 3px 9px;
    border-radius: 14px;
    font-size: 12.5px;
    margin: 4px 5px 4px 0;
}
.exp-card {
    margin-top: 16px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 10px;
    page-break-inside: avoid;
}
.exp-header {
    font-weight: 600;
    font-size: 15.5px;
}
.exp-meta {
    font-size: 12.5px;
    color: #666;
    margin-bottom: 8px;
}
.exp-card li {
    font-size: 14px;
    margin-bottom: 6px;
}
.footer {
    font-size: 13px;
    margin-top: 18px;
    color: #555;
}
.btn-pdf {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 999;
}
@media print {
    body * { visibility: hidden; }
    #cv, #cv * { visibility: visible; }
    #cv { position: absolute; left: 0; top: 0; box-shadow: none; }
}
</style>
</head>

<body>

<button id="btn-pdf" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-red-600 text-white hover:bg-red-700 btn-pdf">
    ⬇ Télécharger PDF
</button>
<a href="preview_cv.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-gray-500 text-white hover:bg-gray-600">Back</a>

<div id="cv">

<div class="cv-header">
    <h1>Amirul Putra Justicia</h1>
    <div class="header-meta">
        Ingénieur Systèmes Web (GovTech)<br>
        📧 amirulputra0507@gmail.com · 📱 +62-821-3440-2383<br>
        🔗 linkedin.com/in/amirul-putra-justicia-70ba31191
    </div>
</div>

<div class="summary">
Ingénieur Fullstack avec plus de 6 ans d'expérience en GovTech.
Intégration de systèmes gouvernementaux, disponibilité ~99,9%,
réduction d'erreurs inter-systèmes ~30%.
</div>

<div class="section-title">Compétences</div>
<div class="skill-block">
<?php
$groups = [
    'Backend' => ['PHP','Laravel','Node.js','CodeIgniter','Python (Django)'],
    'Frontend' => ['React.js','Vue.js','JavaScript','HTML','CSS'],
    'Base de données' => ['PostgreSQL','MySQL','MongoDB']
];

$allSkills = [];
while ($s = pg_fetch_assoc($skills)) {
    $allSkills[] = e($s['skill_name']);
}

foreach ($groups as $label => $list) {
    echo "<strong>{$label} :</strong> ";
    foreach ($list as $skill) {
        if (in_array($skill, $allSkills)) {
            echo "<span>{$skill}</span>";
        }
    }
    echo "<br>";
}
?>
</div>

    <!-- Languages -->
    <?php
    $langs = pg_query($conn, "SELECT * FROM languages ORDER BY id ASC");
    if(pg_num_rows($langs) > 0):
    ?>
    <div class="section-title">Languages</div>
    <div class="skill-block">
        <?php while($l = pg_fetch_assoc($langs)): ?>
            <?= e($l['language_name']); ?> (<?= e($l['proficiency']); ?>) · 
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

<div class="section-title">Expérience Professionnelle</div>

<?php while ($w = pg_fetch_assoc($work_exp)):
    $praq = generatePRAQ(
        e($w['description']),
        $w['stack'] ? explode(',', $w['stack']) : []
    );
?>
<div class="exp-card">
    <div class="exp-header"><?= e($w['company']) ?> – Ingénieur Web</div>
    <div class="exp-meta">
        <?= formatDate($w['start_date']) ?> – <?= $w['present'] ? 'Présent' : formatDate($w['end_date']) ?>
    </div>
    <ul>
        <?php foreach ($praq as $b): ?>
        <li><?= e($b) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endwhile; ?>

<div class="section-title">Formation</div>
<div class="footer">
Université Ahmad Dahlan – Informatique (2014 – 2018)
</div>

</div>

<!-- PDF ENGINE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.getElementById('btn-pdf').addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    const orig = btn.innerHTML;
    btn.innerHTML = '⏳ Génération PDF...';

    try {
        const cv = document.getElementById('cv');
        const omw = cv.style.maxWidth; const op = cv.style.padding;
        cv.style.maxWidth = '595px'; cv.style.padding = '24px';

        const canvas = await html2canvas(cv, {
            scale: 2,
            backgroundColor: '#ffffff'
        });
        cv.style.maxWidth = omw || ''; cv.style.padding = op || '';

        const { jsPDF } = window.jspdf;
        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const pdf = new jsPDF('p', 'pt', 'a4');

        const pw = pdf.internal.pageSize.getWidth(), ph = pdf.internal.pageSize.getHeight();
        const mg = 28, iw = pw - mg * 2, ih = (canvas.height * iw) / canvas.width, uh = ph - mg * 2;
        let offset = 0, page = 0;
        do {
            if (page > 0) pdf.addPage();
            pdf.addImage(imgData, 'JPEG', mg, mg - offset, iw, ih);
            offset += uh; page++;
        } while (offset < ih);

        pdf.save('CV_AmirulPutraJusticia_FR_Modern.pdf');
    } catch (err) {
        alert('Failed to generate PDF');
        console.error(err);
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
});
</script>

</body>
</html>
