<?php
include 'config.php';
$photoPath = 'pasfotoamirul.jpg';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>履歴書 (Rirekisho)</title>
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
    .no-print, .btn, .btn-danger, .btn-secondary {
        display: none !important;
    }
}
</style>
</head>
<body>

<div class="container">

<h2>履歴書 (Rirekisho)</h2>

<table>
<tr>
    <th>氏名</th>
    <td>Amirul Putra Justicia</td>
    <td rowspan="4" style="width:130px;">
        <div class="photo">
            <?php if(file_exists($photoPath)): ?>
                <img src="<?= $photoPath ?>">
            <?php else: ?>
                写真<br>4cm × 3cm
            <?php endif; ?>
        </div>
    </td>
</tr>
<tr>
    <th>国籍</th>
    <td>インドネシア</td>
</tr>
<tr>
    <th>メール</th>
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
6年以上のWebアプリケーション開発経験を持つフルスタックエンジニア。<br>
PHP（Laravel）、Node.js、PostgreSQL、Vue/Reactを用いた業務システム開発に従事。<br>
GovTech分野における業務システム・データ連携の経験あり。
</td>
</tr>
</table>

<div class="section-title">■ 職歴</div>
<table>
<?php
$work = pg_query($conn,"SELECT * FROM work_experience ORDER BY start_date DESC");
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

<div class="section-title">■ スキル</div>
<table>
<tr>
<td>
<?php
$skills = pg_query($conn,"SELECT * FROM skills ORDER BY skill_name");
$list=[];
while($s = pg_fetch_assoc($skills)){
    $list[] = htmlspecialchars($s['skill_name']);
}
echo implode(' ／ ', $list);
?>
</td>
</tr>
</table>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-danger">PDFとして保存</button>
    <a href="preview_cv.php" class="btn btn-secondary">戻る</a>
</div><br>

</div>
</body>
</html>
