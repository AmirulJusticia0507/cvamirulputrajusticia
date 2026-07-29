<?php
include 'config.php';
$work = pg_query($conn,"SELECT * FROM work_experience ORDER BY start_date DESC");
function e($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<title>个人简历 – Amirul</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#eee;
    padding:20px;
    font-family: "Noto Sans SC","PingFang SC","Microsoft YaHei",Arial,sans-serif;
}
#cv{
    max-width:820px;
    background:#fff;
    margin:auto;
    padding:36px;
}
h1{font-size:28px;margin:0}
.photo{
    width:120px;
    border-radius:6px;
}
.meta{
    font-size:13px;
    color:#444;
}
.section{
    margin-top:22px;
}
.section h3{
    font-size:17px;
    border-bottom:1px solid #333;
    padding-bottom:4px;
}
.exp{
    margin-top:10px;
}
.exp b{
    font-size:14px;
}
li{
    font-size:13.5px;
    line-height:1.4;
}

/* ACTION CARD */
.cv-action-wrapper{
    max-width:820px;
    margin:20px auto 0;
}
.cv-action-card{
    background:#fff;
    padding:16px 20px;
    border-radius:10px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
}
</style>
</head>

<body>

<!-- ================= CV CONTENT ================= -->
<div id="cv">

<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Amirul Putra Justicia</h1>
        <div class="meta">
            全栈工程师（政府系统）<br>
            邮箱: amirulputra0507@gmail.com<br>
            电话: +62-821-3440-2383
        </div>
    </div>
    <img src="pasfotoamirul.jpg" class="photo">
</div>

<div class="section">
<h3>个人简介</h3>
6年以上政府信息系统开发经验，专注于系统集成、稳定性与数据一致性。
</div>

<div class="section">
<h3>工作经历</h3>
<?php while($w=pg_fetch_assoc($work)): ?>
<div class="exp">
<b><?=e($w['company'])?> ｜ 全栈工程师</b>
<ul>
    <li><?=e(strtok($w['description'],'.'))?></li>
    <li>技术栈：<?=e($w['stack'])?></li>
    <li>系统性能与稳定性显著提升</li>
</ul>
</div>
<?php endwhile; ?>
</div>

<div class="section">
<h3>教育背景</h3>
Universitas Ahmad Dahlan — 计算机科学（2014–2018）
</div>

</div>
<!-- =============== END CV ================= -->

<!-- ================= ACTIONS ================= -->
<div class="cv-action-wrapper">
    <div class="cv-action-card">
        <div class="d-flex gap-2 flex-wrap">
            <button id="btn-pdf" class="btn btn-danger">
                ⬇ 下载 PDF
            </button>
            <a href="index.php" class="btn btn-secondary">
                ⬅ 返回
            </a>
        </div>
    </div>
</div>

<!-- ================= PDF LIBS ================= -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.getElementById('btn-pdf').addEventListener('click', async function () {
    const btn = this;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ 生成 PDF...';

    try{
        const { jsPDF } = window.jspdf;
        const cv = document.getElementById('cv');

        const canvas = await html2canvas(cv,{
            scale: window.devicePixelRatio * 2,
            backgroundColor:'#ffffff',
            useCORS:true,
            scrollY:-window.scrollY
        });

        const imgData = canvas.toDataURL('image/jpeg',0.95);
        const pdf = new jsPDF('p','pt','a4',true);

        const pdfW = pdf.internal.pageSize.getWidth();
        const pdfH = pdf.internal.pageSize.getHeight();
        const imgH = canvas.height * pdfW / canvas.width;

        let heightLeft = imgH;
        let pos = 0;

        while(heightLeft > 0){
            pdf.addImage(imgData,'JPEG',0,pos,pdfW,imgH);
            heightLeft -= pdfH;
            pos -= pdfH;
            if(heightLeft > 0) pdf.addPage();
        }

        pdf.save('CV_AmirulPutraJusticia_CN.pdf');

    }catch(e){
        alert('PDF 生成失败');
        console.error(e);
    }finally{
        btn.disabled=false;
        btn.innerHTML=original;
    }
});
</script>

</body>
</html>
