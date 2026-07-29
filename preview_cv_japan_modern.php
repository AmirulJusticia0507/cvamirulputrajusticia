<?php
include 'config.php';
$photoPath = 'pasfotoamirul.jpg';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>職務経歴書 (Shokumukeirekisho)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    background:#f5f5f5;
    font-family:"Hiragino Kaku Gothic ProN","Yu Gothic","Meiryo",sans-serif;
}
.cv{
    width:820px;
    margin:30px auto;
    background:#fff;
    padding:40px;
    color:#000;
    font-size:14px;
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
}
.photo{
    width:110px;
    height:140px;
    border:1px solid #000;
}
.photo img{
    width:100%;
    height:100%;
    object-fit:cover;
}
.section-title{
    margin-top:26px;
    font-weight:bold;
    border-bottom:2px solid #000;
    padding-bottom:4px;
}
.no-print{
    text-align:center;
    margin-top:30px;
}
@media print {
    .no-print, .btn, .btn-danger, .btn-secondary {
        display: none !important;
    }
}
@media print {
    body {
        background: #fff !important;
    }

    .cv {
        box-shadow: none !important;
        padding: 24px !important;
    }

    img {
        image-rendering: optimizeSpeed;
    }

    p, li {
        font-size: 12px;
        line-height: 1.4;
    }
}

</style>
</head>
<body>

<div class="cv">

<div class="header mb-3">
    <div>
        <h3 class="fw-bold">職 務 経 歴 書</h3>
        <div><?= date('Y') ?>年<?= date('m') ?>月<?= date('d') ?>日現在</div>
        <div class="fw-bold mt-2">氏名：Amirul Putra Justicia</div>
    </div>
    <div class="photo">
        <?php if(file_exists($photoPath)): ?>
            <img src="<?= $photoPath ?>">
        <?php endif; ?>
    </div>
</div>

<div class="section-title">■ 職務要約</div>
<p>
Webシステムエンジニアとして6年以上の実務経験を有し、
GovTech領域を中心に業務系Webシステムの設計・開発・運用を担当。
REST API、データ連携、業務効率化を強みとする。
</p>

<div class="section-title">■ 職務経歴</div>

<?php
$work = pg_query($conn,"SELECT * FROM work_experience ORDER BY start_date DESC");
while($w = pg_fetch_assoc($work)):
?>
<p class="fw-bold mt-3">
<?= htmlspecialchars($w['company']) ?>
（<?= htmlspecialchars($w['start_date']) ?> ～ <?= htmlspecialchars($w['end_date'] ?: '現在') ?>）
</p>
<p><?= htmlspecialchars($w['position']) ?></p>
<ul>
    <li>業務Webアプリケーションの設計・開発・運用</li>
    <li>REST API設計および外部システム連携</li>
    <li>PostgreSQL / MySQL を用いたデータ設計</li>
</ul>
<?php endwhile; ?>

<div class="section-title">■ テクニカルスキル</div>
<ul>
    <li>Backend：PHP（Laravel）, Node.js, Python（Django）</li>
    <li>Frontend：Vue.js, React.js</li>
    <li>DB：PostgreSQL, MySQL</li>
    <li>Tools：Git, Docker（基礎）</li>
</ul>

<div class="section-title">■ 自己PR</div>
<p>
新しい技術や環境への適応力を強みとし、
短期間でのキャッチアップと安定した成果提供を重視しています。
チームおよびプロダクトに中長期的な価値を提供できるエンジニアを目指しています。
</p>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-danger">PDFダウンロード</button>
    <a href="preview_cv.php" class="btn btn-secondary">戻る</a>
</div>

</div>
</body>
</html>
