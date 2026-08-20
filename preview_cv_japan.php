<?php
session_start();
include 'config.php';

$preview_user_id = get_preview_user_id($conn);
if(!$preview_user_id){
    die('Profile not found');
}

$photoPath = 'pasfotoamirul.jpg';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>履歴書 (Rirekisho)</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<style>
body{
    font-family:"MS Gothic","Yu Gothic","Meiryo",Arial;
    font-size:13px; /* dinaikkan dari 12px */
    line-height:1.4;
    background:#fff;
    color:#000;
}
.container{
    width:800px;
    margin:0 auto;
}
h2{
    text-align:center;
    margin-bottom:20px;
}
table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}
th,td{
    border:1px solid #000;
    padding:8px; /* lebih lega */
    vertical-align:top;
    word-wrap:break-word;
}
th{
    background:#f5f5f5;
    width:120px;
    text-align:left;
}
.photo{
    width:120px;
    height:160px;
    border:1px solid #000;
    text-align:center;
    overflow:hidden;
}
.photo img{
    width:100%;
    height:100%;
    object-fit:cover;
}
.section-title{
    margin-top:18px;
    font-weight:bold;
}
.no-print{
    text-align:center;
    margin-top:25px;
}
@media print {
    .no-print {
        display: none !important;
    }
}
body.dark .container {
    background: #1f232b !important;
    color: #d6dae1;
}
</style>
</head>

<body>

<div class="container">

<h2>履歴書 (Rirekisho)</h2>

<table>
<tr>
    <th>氏名</th>
    <td>アムリル・プトラ・ユスティシア</td>
    <td rowspan="6" style="width:130px;">
        <div class="photo">
            <?php if(file_exists($photoPath)): ?>
                <img src="<?= $photoPath ?>" alt="Amirul Putra Justicia">
            <?php else: ?>
                写真<br>4cm × 3cm
            <?php endif; ?>
        </div>
    </td>
</tr>
<tr>
    <th>生年月日</th>
    <td>1990年1月15日</td>
</tr>
<tr>
    <th>住所</th>
    <td>インドネシア・ヨゴヤカルタ</td>
</tr>
<tr>
    <th>国籍</th>
    <td>インドネシア</td>
</tr>
<tr>
    <th>メールアドレス</th>
    <td>amirulputra0507@gmail.com</td>
</tr>
<tr>
    <th>電話番号</th>
    <td>+62-821-3440-2383</td>
</tr>
</table>

<div class="section-title">■ 職務要約</div>
<table>
<tr>
<td>
6年以上のWebアプリケーション開発経験を持つフルスタックエンジニア。PHP（Laravel）、Node.js、PostgreSQL、Vue/Reactを用いた業務システム開発に従事。GovTech分野における業務システム・データ連携の経験あり。日本のテクノロジーと文化に強い関心を持ち、日本でのエンジニアキャリアを追求しています。
</td>
</tr>
</table>

<div class="section-title">■ 日本語能力</div>
<table>
<tr>
<td>
ビジネスレベル（日常会話可能、業務連絡が可能）。日本の職場でミーティングやドキュメントの読み書きが可能です。日本語の習得度については、日常的なコミュニケーションが可能で、ビジネスメールや簡単な報告書の作成ができます。
</td>
</tr>
</table>

<div class="section-title">■ 職歴</div>
<table>
<?php
$work = pg_query_params($conn,"SELECT * FROM work_experience WHERE user_id=$1 ORDER BY start_date DESC", [$preview_user_id]);
while($w = pg_fetch_assoc($work)):
?>
<tr>
<td>
<strong><?= htmlspecialchars($w['company']) ?></strong><br>
 <?= htmlspecialchars($w['position']) ?><br>
 <?= htmlspecialchars($w['start_date']) ?> ～ <?= htmlspecialchars($w['end_date'] ?: '現在') ?>
</td>
</tr>
<?php endwhile; ?>
</table>

<div class="section-title">■ 主な業務と実績</div>
<table>
<?php
$work = pg_query_params($conn,"SELECT * FROM work_experience WHERE user_id=$1 ORDER BY start_date DESC", [$preview_user_id]);
$i = 0;
while($w = pg_fetch_assoc($work)):
    $i++;
    $desc = htmlspecialchars($w['description'] ?? '');
    $stack = htmlspecialchars($w['stack'] ?? '');
    $project = htmlspecialchars($w['project'] ?? '');
    ?>
    <tr>
    <td style="padding-top:12px;">
    <strong><?= $i ?>. <?= $w['position'] ?> at <?= $w['company'] ?></strong><br>
    <?= $desc ?><br>
    スタック: <?= $stack ?><br>
    プロジェクト: <?= $project ?><br>
    <?= $w['location'] ?><br>
    </td>
    </tr>
    <?php endwhile; ?>
</table>

<div class="section-title">■ スキル</div>
<table>
<tr>
<td>
<?php
$skills = pg_query_params($conn,"SELECT * FROM skills WHERE user_id=$1 ORDER BY skill_name", [$preview_user_id]);
$list=[];
while($s = pg_fetch_assoc($skills)){
    $list[] = htmlspecialchars($s['skill_name']) . ' (' . htmlspecialchars($s['level'] ?? '') . ')';
}
echo implode(' ／ ', $list);
?>
</td>
</tr>
</table>

    <!-- Languages -->
    <?php
    $langs = pg_query_params($conn, "SELECT * FROM languages WHERE user_id=$1 ORDER BY id ASC", [$preview_user_id]);
    if(pg_num_rows($langs) > 0):
    ?>
    <div class="section-title">言語</div>
    <div class="skill-block">
        <?php while($l = pg_fetch_assoc($langs)): ?>
            <?= htmlspecialchars($l['language_name']); ?> (<?= htmlspecialchars($l['proficiency']); ?>) · 
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <?php $pfTitle = '■ 主要プロジェクト'; $pfLinkLabel = 'GitHubで見る'; include __DIR__ . '/includes/portfolio_section.php'; ?>

<div class="no-print">
    <button onclick="window.print()" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-red-600 text-white hover:bg-red-700">PDFとして保存</button>
    <a href="preview_cv.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-gray-500 text-white hover:bg-gray-600">戻る</a>
</div><br>

</div>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
