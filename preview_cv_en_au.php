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

@media print{
    body{background:#fff;padding:0;}
    #cv{box-shadow:none;}
    .no-print { display: none !important; }  /* tombol tidak muncul di PDF */
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

<div class="section">
<h2>Professional Experience</h2>

<?php while($w=pg_fetch_assoc($work_exp)): ?>
<div class="job">
    <div class="job-title">
        <?= e($w['company']) ?> – Web Systems Engineer
    </div>
    <div class="job-meta">
        <?= d($w['start_date']) ?> – <?= $w['present']?'Present':d($w['end_date']) ?>
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
<h2>Education</h2>
<p style="font-size:13.5px;">
Bachelor of Informatics Engineering – Universitas Ahmad Dahlan (2014–2018)
</p>
</div>

<div style="margin-top:24px;">
<button class="no-print" onclick="window.print()">Download PDF</button>
</div>

</div>

</body>
</html>
