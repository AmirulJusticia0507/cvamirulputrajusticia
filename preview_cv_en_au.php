<?php
include 'config.php';

$work_exp = pg_query($conn,"SELECT * FROM work_experience ORDER BY start_date DESC");
$skills   = pg_query($conn,"SELECT * FROM skills ORDER BY id ASC");

function e($s){ return htmlspecialchars($s ?? '',ENT_QUOTES,'UTF-8'); }
function d($dt){ return $dt ? date('M Y',strtotime($dt)) : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CV – Amirul Putra Justicia (Australia Tech)</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">

<style>
body{
    font-family: "Segoe UI", Arial, sans-serif;
    background:#f4f6f8;
    padding:30px;
}

#cv{
    max-width:880px;
    margin:auto;
    background:#fff;
    padding:40px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
}

h1{
    font-size:26px;
    margin-bottom:4px;
}

.meta{
    font-size:13px;
    color:#444;
    margin-bottom:14px;
}

.section{
    margin-top:22px;
}

.section h2{
    font-size:17px;
    border-bottom:2px solid #0d6efd;
    padding-bottom:4px;
    margin-bottom:10px;
}

.summary{
    font-size:14px;
    line-height:1.55;
}

.skills span{
    display:inline-block;
    background:#e9f1ff;
    color:#0d6efd;
    padding:4px 10px;
    border-radius:14px;
    font-size:12px;
    margin:4px 6px 4px 0;
}

.job{
    margin-bottom:16px;
}

.job-title{
    font-weight:600;
    font-size:14.5px;
}

.job-meta{
    font-size:12.5px;
    color:#666;
    margin-bottom:4px;
}

ul{
    padding-left:18px;
}

li{
    font-size:13.5px;
    margin-bottom:5px;
    line-height:1.45;
}

@page {
    size: A4;
    margin: 18mm 20mm;
}

@media print {
    body {
        background: white;
        padding: 0;
        margin: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    #cv {
        max-width: 100%;
        box-shadow: none;
        border-radius: 0;
        padding: 0;
        position: static;
    }
    .no-print {
        display: none !important;
    }
    .exp-card {
        page-break-inside: avoid;
    }
    .section-title {
        page-break-after: avoid;
    }
}
.justify{
    text-align: justify;
    text-justify: inter-word;
}

</style>
</head>

<body>

<div id="cv">

<h1>Amirul Putra Justicia</h1>
<div class="meta">
Senior Fullstack Engineer (GovTech)<br>
Email: amirulputra0507@gmail.com | Phone: +62-821-3440-2383<br>
Open to relocation & remote opportunities
</div>

<div class="section">
<h2>Professional Summary</h2>
<div class="summary justify">
Senior Fullstack Engineer with 6+ years of experience delivering scalable government and enterprise-grade
web applications. Strong background in system integration, API interoperability, and secure data pipelines.
Experienced working with cross-functional teams and long-term platform ownership.
</div>
</div>

<div class="section">
<h2>Technical Skills</h2>
<div class="skills">
<?php
while($s=pg_fetch_assoc($skills)){
    echo "<span>".e($s['skill_name'])."</span>";
}
?>
</div>
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

<div class="section">
<h2>Professional Experience</h2>

<?php while($w=pg_fetch_assoc($work_exp)): ?>
<div class="job">
    <div class="job-title">
        <?= e($w['company']) ?> – Web Systems Engineer
    </div>
    <div class="job-meta">
        <?= d($w['start_date']) ?> – <?= in_array($w['present'],['t',1,true],true)?'Present':d($w['end_date']) ?>
    </div>
    <ul>
        <li class="justify"><?= e($w['description']) ?></li>
        <?php if($w['stack']): ?>
        <li><strong>Tech Stack:</strong> <?= e($w['stack']) ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endwhile; ?>

</div>

<div class="section">
<h2>Featured Projects</h2>
<?php $pfTitle = 'Featured Projects'; $pfLinkLabel = 'View on GitHub'; include __DIR__ . '/includes/portfolio_section.php'; ?>
</div>

<div class="section">
<h2>Education</h2>
<p style="font-size:13.5px;">
Bachelor of Informatics Engineering – Universitas Ahmad Dahlan (2014–2018)
</p>
</div>

<div style="margin-top:24px;">
<button class="no-print" onclick="window.print()">Download PDF</button>
</div>

</div>

<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
