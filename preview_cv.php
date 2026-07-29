<?php
include 'config.php';
$profileRes = pg_query($conn, "SELECT * FROM profile ORDER BY id DESC LIMIT 1");
$profile = pg_fetch_assoc($profileRes);

if (!$profile) {
    die('Profile not found');
}


// Ambil data
$work_exp = pg_query($conn, "SELECT * FROM work_experience ORDER BY start_date DESC");
$skills   = pg_query($conn, "SELECT * FROM skills ORDER BY id ASC");

// Fungsi escape
function e($str){ return htmlspecialchars($str ?? ''); }
function formatDate($date){ return $date ? date('M Y', strtotime($date)) : ''; }

// Fungsi generate bullet PRAQ
function generatePRAQ($descText, $tech=[], $numbers=[]){
    $bullets = [];

    // Problem: ambil kalimat pertama
    $problem = strtok($descText, ".") ?: "Identified system challenges";
    $bullets[] = "Identified challenge: {$problem}.";

    // Role
    $bullets[] = "Role: Led implementation of backend, frontend, and integration workflows.";

    // Action
    if($tech){
        $bullets[] = "Action: Developed and maintained using ".implode(", ", $tech)."; applied REST/SOAP APIs, data validation, and integration pipelines.";
    } else {
        $bullets[] = "Action: Executed system development, optimized workflows, and ensured data consistency.";
    }

    // Result
    $resultParts = [];
    foreach($numbers as $label => $value){
        if(isset($value) && $value !== '') $resultParts[] = "{$label} {$value}";
    }
    if($resultParts){
        $bullets[] = "Result: ".implode("; ", $resultParts).".";
    } else {
        $bullets[] = "Result: Achieved measurable improvements in system reliability and data integrity.";
    }

    return $bullets;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CV Preview – Amirul Putra Justicia</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    font-family: Inter, Arial, sans-serif;
    background: #eef1f4;
    padding: 24px;
}

#cv {
    max-width: 820px;
    margin: auto;
    background: #fff;
    padding: 36px 36px 32px; /* padding bawah diperkecil */
    border-radius: 10px;
}

/* ================= HEADER ================= */
h1 {
    font-size: 26px;
    margin-bottom: 2px;
}

.header-meta {
    font-size: 13px;
    color: #555;
}

.summary {
    font-size: 14px;
    margin-top: 8px;
    line-height: 1.45;
}

/* ================= SECTION TITLE ================= */
.section-title {
    margin-top: 18px;              /* DIPERKECIL dari 28 */
    font-size: 18px;
    font-weight: 700;
    border-bottom: 2px solid #0d6efd;
    padding-bottom: 5px;
}

/* ================= SKILLS ================= */
.skill-block {
    font-size: 13px;
    line-height: 1.45;
}

.skill-block strong {
    color: #0d6efd;
}

/* ================= EXPERIENCE ================= */
.exp-card {
    margin-top: 12px;
    padding: 10px 12px;            /* lebih padat */
    background: #f8f9fa;
    border-radius: 8px;
    page-break-inside: avoid;
}

.exp-header {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 4px;
}

.exp-meta {
    font-size: 12px;
    color: #666;
    margin-bottom: 6px;
}

.exp-card ul {
    padding-left: 18px;
    margin: 0;
}

.exp-card li {
    margin-bottom: 5px;            /* dipadatkan */
    font-size: 13.5px;
    line-height: 1.4;
}

/* 👉 EXPERIENCE TERAKHIR DIPRESS */
.exp-card:last-of-type {
    margin-bottom: 8px;
}

/* ================= EDUCATION ================= */
.education {
    margin-top: 10px;              /* NAIK */
    page-break-inside: avoid;
}

.education p {
    margin: 2px 0;
    font-size: 13.5px;
    line-height: 1.35;
}

/* ================= FOOTER ================= */
.footer {
    font-size: 12px;
    color: #555;
    margin-top: 16px;              /* DIPERKECIL */
    padding-bottom: 8px;           /* jangan makan halaman */
    page-break-inside: avoid;
}

/* ================= ACTION CARD (NON PRINT) ================= */
.cv-action-wrapper {
    max-width: 820px;
    margin: 24px auto 0;
}

.cv-action-card {
    background: #ffffff;
    padding: 18px 20px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

.cv-action-card h6 {
    font-weight: 600;
    margin-bottom: 12px;
    color: #333;
}

/* ================= PRINT ================= */
@media print {
    body * {
        visibility: hidden;
    }

    #cv, #cv * {
        visibility: visible;
    }

    #cv {
        position: absolute;
        left: 0;
        top: 0;
        box-shadow: none;
        border-radius: 0;
    }
}
/* Posisi tombol di kanan, tetap visible saat scroll */
.cv-action-fixed {
    position: fixed;
    top: 50%;           /* tengah vertikal */
    right: 20px;        /* jarak dari kanan layar */
    transform: translateY(-50%);
    z-index: 1000;      /* pastikan di atas elemen lain */
}

/* Tombol rapih vertikal */
.cv-action-fixed .btn {
    width: 210px;       /* diperbesar */
    padding: 0.6rem 1rem;  /* lebih nyaman untuk klik */
    font-size: 14px;       /* lebih readable */
    white-space: nowrap;
}
.btn {
  text-align: center;
  padding-left: 16px;
}

</style>
</head>
<body>
<!-- Tombol statis kanan -->
<div class="cv-action-fixed no-print">
    <div class="d-flex flex-column gap-2">
        <a href="cover_letter_list.php" class="btn btn-success btn-lg" target="_blank">📄 Create Cover Letters</a>
        <a href="motivation_letter_list.php" class="btn btn-warning btn-lg" target="_blank">✍ Motivation Letters</a>
        <a href="index.php" class="btn btn-secondary btn-lg">⬅ Back</a>
    </div>
</div>

<div id="cv">
    <!-- Header -->
    <h1><?= e($profile['full_name']) ?></h1>
    <div class="header-meta">
        <?= e($profile['headline']) ?><br>
        📧 <?= e($profile['email']) ?> ·
        📱 <?= e($profile['phone']) ?> ·
        🔗 <?= e(parse_url($profile['linkedin'], PHP_URL_HOST) . parse_url($profile['linkedin'], PHP_URL_PATH)) ?>
    </div>

    <!-- Summary -->
    <div class="summary">
        <?= nl2br(e($profile['summary'])) ?>
    </div>

    <!-- Core Skills -->
    <div class="section-title">Core Skills</div>
    <div class="skill-block">
        <?php
        $backend  = ['PHP','Laravel','Node.js','CodeIgniter','Python (Django)'];
        $frontend = ['React.js','Vue.js','JavaScript','HTML','CSS'];
        $db       = ['PostgreSQL','MySQL','MongoDB'];
        $grouped = [];
        while($s = pg_fetch_assoc($skills)){
            $skill = e($s['skill_name']);
            if(in_array($skill,$backend))   $grouped['Backend'][] = $skill;
            if(in_array($skill,$frontend))  $grouped['Frontend'][] = $skill;
            if(in_array($skill,$db))        $grouped['Database'][] = $skill;
        }
        foreach($grouped as $cat=>$list){
            echo "<strong>$cat:</strong> ".implode(', ',$list)."<br>";
        }
        ?>
        <strong>Integration:</strong> REST/SOAP APIs, Data Validation, Pipelines
    </div>

    <!-- Work Experience -->
    <div class="section-title">Work Experience</div>
    <?php while($w = pg_fetch_assoc($work_exp)):
        $present = in_array($w['present'], ['t', true, 1], true);
        $descText = e($w['description']);
        $numbers = [
            "Integrated agencies" => $w['integrated'] ?? null,
            "Uptime" => $w['uptime'] ?? null,
            "Error reduction" => $w['error_reduction'] ?? null,
            "Users onboarded" => $w['users_onboarded'] ?? null
        ];
        $techStack = $w['stack'] ? explode(",", $w['stack']) : [];
        $praqBullets = generatePRAQ($descText, $techStack, $numbers);
    ?>
    <div class="exp-card">
        <div class="exp-header">
            <?php 
            // Jika Maybank role IT Support
            $roleLabel = strpos(strtolower($w['company']), 'maybank')!==false ? 'IT Support (GovTech)' : 'Web Systems Engineer (GovTech)';
            echo "{$roleLabel} — ".e($w['company']); 
            ?>
        </div>
        <div class="exp-meta">
            <?= formatDate($w['start_date']); ?> – <?= $present?'Present':formatDate($w['end_date']); ?> · <?= e($w['location']); ?> · <?= e($w['status_kerja']); ?>
        </div>
        <?php if($techStack): ?>
        <div class="exp-meta"><strong>Tech:</strong> <?= implode(", ", $techStack); ?></div>
        <?php endif; ?>

        <ul>
            <?php foreach($praqBullets as $bullet): ?>
                <li><?= e($bullet); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endwhile; ?>

    <!-- Education -->
    <div class="section-title">Education</div>
    <div class="footer">
        Universitas Ahmad Dahlan – Informatics Engineering (2014 – 2018)
    </div>
</div>

<!-- CV Actions -->
<div class="cv-action-wrapper">
    <div class="cv-action-card">
        <h6>CV Actions</h6>

        <!-- Tombol PDF -->
        <div class="mb-3">
            <button id="btn-pdf" class="btn btn-primary w-100 no-print">⬇ Download PDF</button>
        </div>

        <div class="row g-2">

            <!-- JAPAN -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="d-grid gap-2">
                <a href="preview_cv_japan.php" class="btn btn-danger" target="_blank">
                    🇯🇵 Traditional Japan
                </a>
                <a href="preview_cv_japan_modern.php" class="btn btn-dark" target="_blank">
                    🇯🇵 Modern Japan
                </a>
                <a href="preview_cv_de.php" class="btn btn-outline-secondary" target="_blank">
                    🇩🇪 German CV
                </a>
                </div>
            </div>

            <!-- EU / ASIA -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="d-grid gap-2">
                <a href="preview_cv_fr.php" class="btn btn-warning" target="_blank">
                    🇫🇷 French CV
                </a>
                <a href="preview_cv_cn.php" class="btn btn-danger" target="_blank">
                    🇨🇳 Chinese CV
                </a>
                <a href="preview_cv_kr.php" class="btn btn-dark" target="_blank">
                    🇰🇷 Korean CV
                </a>
                </div>
            </div>

            <!-- ENGLISH -->
            <div class="col-md-4 col-sm-12 col-12">
                <div class="d-grid gap-2">
                <a href="preview_cv_en_sg.php" class="btn btn-outline-dark" target="_blank">
                    🇸🇬 English ATS (Singapore)
                </a>
                <a href="preview_cv_en_au.php" class="btn btn-outline-primary" target="_blank">
                    🇦🇺 Australia Tech
                </a>
                <a href="preview_cv_us.php" class="btn btn-info no-print" target="_blank">
                    🇺🇸 US Tech Resume
                </a>
                </div>
            </div>

        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById('btn-pdf').addEventListener('click', async function(){
    const btn=this; btn.disabled=true; const orig=btn.innerHTML; btn.innerHTML='⏳ Generating PDF...';
    try{
        const { jsPDF } = window.jspdf;
        const cv=document.getElementById('cv');
        const canvas=await html2canvas(cv,{scale:window.devicePixelRatio*2,backgroundColor:'#ffffff',useCORS:true,scrollY:-window.scrollY});
        const imgData=canvas.toDataURL('image/jpeg',0.95);
        const pdf=new jsPDF({orientation:'p',unit:'pt',format:'a4',compress:true});
        const pdfWidth=pdf.internal.pageSize.getWidth();
        const pdfHeight=pdf.internal.pageSize.getHeight();
        const imgHeight=(canvas.height*pdfWidth)/canvas.width;
        let heightLeft=imgHeight,position=0;
        while(heightLeft>0){
            pdf.addImage(imgData,'JPEG',0,position,pdfWidth,imgHeight);
            heightLeft-=pdfHeight; position-=pdfHeight; if(heightLeft>0) pdf.addPage();
        }
        pdf.save('CV_AmirulPutraJusticia.pdf');
    } catch(err){ alert('Failed to generate PDF.'); console.error(err); }
    finally{ btn.disabled=false; btn.innerHTML=orig; }
});
</script>
</body>
</html>
