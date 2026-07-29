<?php
include 'config.php';

// Ambil data
$work_exp = pg_query($conn,"SELECT * FROM work_experience ORDER BY start_date DESC");
$skills   = pg_query($conn,"SELECT * FROM skills ORDER BY id ASC");

// Escape
function e($s){ return htmlspecialchars($s ?? '',ENT_QUOTES,'UTF-8'); }
function d($dt){ return $dt ? date('M Y',strtotime($dt)) : ''; }

// FAANG-style bullets generator
function generateFAANGBullets($desc,$tech=[],$numbers=[]){
    $bullets=[];
    $problem = strtok($desc,".") ?: "Challenge identified";
    $bullets[] = "Challenge: $problem.";
    $bullets[] = "Role: Led fullstack development and integration pipelines.";
    if($tech) $bullets[] = "Tech: ".implode(", ",$tech)."; REST/SOAP APIs, CI/CD, unit & integration testing.";
    
    $resultParts=[];
    foreach($numbers as $label=>$value){
        if($value!=="") $resultParts[]="$label: $value";
    }
    if($resultParts) $bullets[]="Result: ".implode("; ",$resultParts).".";
    else $bullets[]="Result: Delivered measurable improvements in reliability and performance.";
    return $bullets;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CV – Amirul Putra Justicia (US Tech / FAANG)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    font-family: "Segoe UI", Arial, sans-serif;
    background:#f4f6f8;
    padding:24px;
}
#cv{
    max-width:880px;
    margin:auto;
    background:#fff;
    padding:36px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
}
h1{ font-size:28px; margin-bottom:4px; }
.meta{ font-size:13px; color:#444; margin-bottom:12px; }
.section{ margin-top:24px; }
.section h2{
    font-size:18px;
    border-bottom:2px solid #0d6efd;
    padding-bottom:4px;
    margin-bottom:10px;
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
.job{ margin-bottom:18px; }
.job-title{ font-weight:700; font-size:14.5px; }
.job-meta{ font-size:12.5px; color:#666; margin-bottom:14px; }
ul{ padding-left:18px; }
li{ font-size:13.5px; line-height:1.45; margin-bottom:4px; }

/* ================= PRINT ================= */
@media print{
    body{background:#fff;padding:0;}
    #cv{box-shadow:none;}
    .no-print{ display:none !important; visibility:hidden !important; }
}
</style>
</head>
<body>

<!-- CV Content -->
<div id="cv">
<h1>Amirul Putra Justicia</h1>
<div class="meta">
Senior Fullstack Engineer (GovTech)<br>
Email: amirulputra0507@gmail.com | Phone: +62-821-3440-2383 | Open to remote/relocation
</div>

<div class="section">
<h2>Professional Summary</h2>
<p>
Senior Fullstack Engineer with 6+ years of experience delivering scalable government-grade web platforms.
Expert in system integration, high-availability architectures, CI/CD pipelines, and cross-functional team leadership.
</p>
</div>

<div class="section">
<h2>Technical Skills</h2>
<div class="skills">
<?php while($s=pg_fetch_assoc($skills)){
    echo "<span>".e($s['skill_name'])."</span>";
} ?>
</div>
</div>

<div class="section">
<h2>Professional Experience</h2>
<?php
while($w=pg_fetch_assoc($work_exp)):
    $present = in_array($w['present'],['t',1,true],true);
    $techStack = $w['stack'] ? explode(",",$w['stack']) : [];
    $numbers=[
        "Integrated agencies"=>$w['integrated']??'',
        "Uptime"=>$w['uptime']??'',
        "Error reduction"=>$w['error_reduction']??'',
        "Users onboarded"=>$w['users_onboarded']??''
    ];
    $bullets = generateFAANGBullets(e($w['description']),$techStack,$numbers);
?>
<div class="job">
    <div class="job-title"><?= e($w['company']) ?> – Web Systems Engineer</div>
    <div class="job-meta"><?= d($w['start_date']) ?> – <?= $present?'Present':d($w['end_date']) ?></div>
    <ul>
        <?php foreach($bullets as $b): ?>
        <li><?= e($b) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endwhile; ?>
</div>

<div class="section">
<h2>Education</h2>
<p>Bachelor of Informatics Engineering – Universitas Ahmad Dahlan (2014–2018)</p>
</div>

</div> <!-- end #cv -->

<!-- Download PDF Button, outside #cv -->
<div class="no-print" style="margin-top:24px; text-align:center;">
    <button id="btn-pdf" class="btn btn-primary">⬇ Download PDF</button>
</div>

<!-- PDF Script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById('btn-pdf').addEventListener('click', async function(){
    const btn=this; btn.disabled=true; const orig=btn.innerHTML; btn.innerHTML='⏳ Generating PDF...';
    try{
        const { jsPDF } = window.jspdf;
        const cv=document.getElementById('cv');
        const canvas=await html2canvas(cv,{scale:2,backgroundColor:'#ffffff',useCORS:true});
        const imgData=canvas.toDataURL('image/jpeg',0.95);
        const pdf=new jsPDF({orientation:'p',unit:'pt',format:'a4',compress:true});
        const pdfWidth=pdf.internal.pageSize.getWidth();
        const pdfHeight=pdf.internal.pageSize.getHeight();
        const imgHeight=(canvas.height*pdfWidth)/canvas.width;
        let heightLeft=imgHeight,position=0;
        while(heightLeft>0){
            pdf.addImage(imgData,'JPEG',0,position,pdfWidth,imgHeight);
            heightLeft-=pdfHeight; position-=pdfHeight;
            if(heightLeft>0) pdf.addPage();
        }
        pdf.save('CV_AmirulPutraJusticia_US.pdf');
    }catch(err){ alert('Failed to generate PDF.'); console.error(err); }
    finally{ btn.disabled=false; btn.innerHTML=orig; }
});
</script>

</body>
</html>
