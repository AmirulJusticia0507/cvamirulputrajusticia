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
<title>CV – Amirul Putra Justicia (ATS SG)</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">

<style>
body{
    font-family: Arial, Helvetica, sans-serif;
    background:#fff;
    color:#000;
    padding:30px;
}

#cv{
    max-width:700px;
    margin:auto;
}

h1{
    font-size:22px;
    margin-bottom:4px;
}

.meta{
    font-size:12.5px;
    margin-bottom:10px;
}

.section{
    margin-top:18px;
}

.section h2{
    font-size:15px;
    border-bottom:1px solid #000;
    padding-bottom:3px;
    margin-bottom:6px;
}

ul{
    padding-left:18px;
}

li{
    font-size:13px;
    margin-bottom:4px;
    line-height:1.35;
}

.job{
    margin-bottom:12px;
}

.job-title{
    font-weight:bold;
    font-size:13.5px;
}

.job-meta{
    font-size:12px;
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
Web Systems Engineer (GovTech)<br>
Email: amirulputra0507@gmail.com | Phone: +62-821-3440-2383 | LinkedIn available
</div>

<div class="section">
<h2>Professional Summary</h2>
<p class="justify" style="font-size:13px;">
Fullstack Engineer with 6+ years of experience delivering government-grade web systems,
interoperable APIs, and secure data workflows. Proven experience integrating multi-agency platforms
with ~99.9% uptime.
</p>
</div>

<div class="section">
<h2>Core Skills</h2>
<p style="font-size:13px;">
<?php
$all=[];
while($s=pg_fetch_assoc($skills)) $all[]=e($s['skill_name']);
echo implode(', ',$all);
?>
</p>
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
        Web Systems Engineer – <?= e($w['company']) ?>
    </div>
    <div class="job-meta">
        <?= d($w['start_date']) ?> – <?= $w['present']?'Present':d($w['end_date']) ?>
    </div>
    <ul>
        <li class="justify"><?= e($w['description']) ?></li>
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
<p style="font-size:13px;">
Universitas Ahmad Dahlan – B.Sc. in Informatics Engineering (2014–2018)
</p>
</div>

<div style="margin-top:20px;">
<button class="no-print" onclick="window.print()">Download PDF</button>
</div>

</div>

<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
