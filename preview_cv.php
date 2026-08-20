<?php
session_start();
include 'config.php';

$preview_user_id = get_preview_user_id($conn);
if(!$preview_user_id){
    die('Profile not found');
}

$profileRes = pg_query_params($conn, "SELECT * FROM profile WHERE user_id=$1 ORDER BY id DESC LIMIT 1", [$preview_user_id]);
$profile = pg_fetch_assoc($profileRes);

if (!$profile) {
    die('Profile not found');
}


// Ambil data
$work_exp = pg_query_params($conn, "SELECT * FROM work_experience WHERE user_id=$1 ORDER BY start_date DESC", [$preview_user_id]);
$skills   = pg_query_params($conn, "SELECT * FROM skills WHERE user_id=$1 ORDER BY id ASC", [$preview_user_id]);

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
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
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

/* ================= PRINT / PDF ================= */
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
    .no-print, .cv-action-wrapper, .cv-action-fixed {
        display: none !important;
    }
    .exp-card {
        page-break-inside: avoid;
        background: #f8f9fa !important;
    }
    .section-title {
        page-break-after: avoid;
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
.cv-action-fixed a {
    width: 210px;
    padding: 0.6rem 1rem;
    font-size: 14px;
    white-space: nowrap;
    text-align: center;
    padding-left: 16px;
}

</style>
</head>
<body>
<!-- Tombol statis kanan -->
<div class="cv-action-fixed no-print">
    <div class="flex flex-col gap-2">
        <a href="cover_letter_list.php" class="inline-block px-6 py-3 text-lg rounded-lg font-semibold text-center transition bg-green-600 text-white hover:bg-green-700" target="_blank">📄 Create Cover Letters</a>
        <a href="motivation_letter_list.php" class="inline-block px-6 py-3 text-lg rounded-lg font-semibold text-center transition bg-yellow-500 text-white hover:bg-yellow-600" target="_blank">✍ Motivation Letters</a>
        <a href="index.php" class="inline-block px-6 py-3 text-lg rounded-lg font-semibold text-center transition bg-gray-500 text-white hover:bg-gray-600">⬅ Back</a>
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

    <!-- Languages -->
    <?php
    $langs = pg_query_params($conn, "SELECT * FROM languages WHERE user_id=$1 ORDER BY id ASC", [$preview_user_id]);
    if(pg_num_rows($langs) > 0):
    ?>
    <div class="section-title">Languages</div>
    <div class="skill-block">
        <?php while($l = pg_fetch_assoc($langs)): ?>
            <?= e($l['language_name']); ?> (<?= e($l['proficiency']); ?>)<?= pg_num_rows($langs)>0 ? ' · ' : ''; ?>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

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

    <!-- Featured Projects -->
    <?php $pfTitle = 'Featured Projects'; $pfLinkLabel = 'View on GitHub'; include __DIR__ . '/includes/portfolio_section.php'; ?>

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
        <div class="flex gap-2 mb-3">
            <button id="btn-pdf" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 flex-1 no-print">⬇ Download PDF</button>
            <button id="btn-pdf-print" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white flex-1 no-print">🖨 Print (ATS Friendly)</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

            <!-- JAPAN -->
            <div>
                <div class="grid gap-2">
                <a href="preview_cv_japan.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-red-600 text-white hover:bg-red-700" target="_blank">
                    🇯🇵 Traditional Japan
                </a>
                <a href="preview_cv_japan_modern.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-gray-900 text-white hover:bg-gray-800" target="_blank">
                    🇯🇵 Modern Japan
                </a>
                <a href="preview_cv_de.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white" target="_blank">
                    🇩🇪 German CV
                </a>
                </div>
            </div>

            <!-- EU / ASIA -->
            <div>
                <div class="grid gap-2">
                <a href="preview_cv_fr.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-yellow-500 text-white hover:bg-yellow-600" target="_blank">
                    🇫🇷 French CV
                </a>
                <a href="preview_cv_cn.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-red-600 text-white hover:bg-red-700" target="_blank">
                    🇨🇳 Chinese CV
                </a>
                <a href="preview_cv_kr.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-gray-900 text-white hover:bg-gray-800" target="_blank">
                    🇰🇷 Korean CV
                </a>
                </div>
            </div>

            <!-- ENGLISH -->
            <div>
                <div class="grid gap-2">
                <a href="preview_cv_en_sg.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-gray-800 text-gray-800 hover:bg-gray-800 hover:text-white" target="_blank">
                    🇸🇬 English ATS (Singapore)
                </a>
                <a href="preview_cv_en_au.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white" target="_blank">
                    🇦🇺 Australia Tech
                </a>
                <a href="preview_cv_us.php?user_id=<?= (int)$preview_user_id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-cyan-500 text-white hover:bg-cyan-600 no-print" target="_blank">
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
// Quick PDF — image-based, instant download
document.getElementById('btn-pdf').addEventListener('click', async function(){
    const btn=this; btn.disabled=true; const orig=btn.innerHTML; btn.innerHTML='⏳ Generating PDF...';
    try{
        const cv=document.getElementById('cv');
        const origMaxW=cv.style.maxWidth; const origP=cv.style.padding;
        cv.style.maxWidth='595px'; cv.style.padding='24px';

        const canvas=await html2canvas(cv,{scale:2,backgroundColor:'#ffffff',useCORS:true});
        cv.style.maxWidth=origMaxW||''; cv.style.padding=origP||'';

        const { jsPDF } = window.jspdf;
        const imgData=canvas.toDataURL('image/jpeg',0.95);
        const pdf=new jsPDF({orientation:'p',unit:'pt',format:'a4',compress:true});
        const pw=pdf.internal.pageSize.getWidth();
        const ph=pdf.internal.pageSize.getHeight();
        const mg=28;
        const iw=pw-mg*2;
        const ih=(canvas.height*iw)/canvas.width;
        const uh=ph-mg*2;
        let offset=0, page=0;
        do{
            if(page>0) pdf.addPage();
            pdf.addImage(imgData,'JPEG',mg,mg-offset,iw,ih);
            offset+=uh; page++;
        }while(offset<ih);
        pdf.save('CV_AmirulPutraJusticia.pdf');
    } catch(err){ alert('Failed to generate PDF.'+err.message); console.error(err); }
    finally{ btn.disabled=false; btn.innerHTML=orig; }
});

// Print PDF — text-based, proper page breaks, ATS-friendly
document.getElementById('btn-pdf-print').addEventListener('click', function(){
    window.print();
});
</script>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
