<?php
session_start();
include 'config.php';

$preview_user_id = get_preview_user_id($conn);
if(!$preview_user_id){
    die('Profile not found');
}

$work_exp = pg_query_params($conn, "SELECT * FROM work_experience WHERE user_id=$1 ORDER BY start_date DESC", [$preview_user_id]);
$skills   = pg_query_params($conn, "SELECT * FROM skills WHERE user_id=$1 ORDER BY id ASC", [$preview_user_id]);

function e($str){
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate($date){
    return $date ? date('Y.m', strtotime($date)) : '';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>이력서 – Amirul Putra Justicia</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">

<script src="https://cdn.tailwindcss.com"></script>

<style>
body{
    font-family: "Apple SD Gothic Neo","Malgun Gothic","Noto Sans KR",Arial,sans-serif;
    background:#f2f4f7;
    padding:24px;
}

#cv{
    max-width:820px;
    margin:auto;
    background:#fff;
    padding:40px;
    border-radius:10px;
    box-shadow:0 6px 24px rgba(0,0,0,.08);
}

h1{
    font-size:26px;
    margin-bottom:4px;
}

.meta{
    font-size:13px;
    color:#555;
}

.section-title{
    margin-top:26px;
    font-size:18px;
    font-weight:700;
    border-bottom:2px solid #111;
    padding-bottom:4px;
}

.summary{
    margin-top:10px;
    font-size:14px;
    line-height:1.5;
}

.skill span{
    display:inline-block;
    background:#111;
    color:#fff;
    padding:2px 8px;
    border-radius:12px;
    font-size:12px;
    margin:3px 4px 0 0;
}

.exp{
    margin-top:14px;
    padding:14px;
    background:#f8f9fa;
    border-radius:8px;
    page-break-inside:avoid;
}

.exp h6{
    margin:0;
    font-size:15px;
    font-weight:600;
}

.exp small{
    font-size:12px;
    color:#666;
}

.exp ul{
    margin-top:6px;
    padding-left:18px;
}

.exp li{
    font-size:13.5px;
    line-height:1.4;
}

.actions{
    max-width:820px;
    margin:20px auto 0;
    text-align:center;
}

@media print{
    body *{visibility:hidden;}
    #cv,#cv *{visibility:visible;}
    #cv{position:absolute;left:0;top:0;box-shadow:none;}
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
웹 시스템 엔지니어 (GovTech)<br>
📧 amirulputra0507@gmail.com · 📱 +62-821-3440-2383
</div>

<div class="summary">
6년 이상의 경력을 가진 풀스택 엔지니어로서 정부 시스템(GovTech) 웹 플랫폼 개발,
기관 간 데이터 연계 및 안정적인 시스템 운영을 담당했습니다.
</div>

<div class="section-title">기술 스택</div>
<div class="skill">
<?php
$all=[];
while($s=pg_fetch_assoc($skills)) $all[]=e($s['skill_name']);
foreach($all as $sk) echo "<span>$sk</span>";
?>
</div>

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

<div class="section-title">경력 사항</div>

<?php while($w=pg_fetch_assoc($work_exp)): ?>
<div class="exp">
    <h6><?= e($w['company']) ?> – 웹 엔지니어</h6>
    <small>
        <?= formatDate($w['start_date']) ?> ~
        <?= in_array($w['present'],['t',1,true],true)?'현재':formatDate($w['end_date']) ?>
    </small>
    <ul>
        <li class="justify"><?= e($w['description']) ?></li>
    </ul>
</div>
<?php endwhile; ?>

<?php $pfTitle = '주요 프로젝트'; $pfLinkLabel = 'GitHub 보기'; include __DIR__ . '/includes/portfolio_section.php'; ?>

<div class="section-title">학력</div>
<p class="mt-2">
Universitas Ahmad Dahlan – Informatics Engineering (2014 – 2018)
</p>

</div>

<div class="actions">
    <button onclick="window.print()" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-gray-900 text-white hover:bg-gray-800">
        ⬇ PDF 다운로드
    </button>
    <a href="preview_cv.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-gray-500 text-white hover:bg-gray-600 ml-2">
        ⬅ 돌아가기
    </a>
</div>

<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
