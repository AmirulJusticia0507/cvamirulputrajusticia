<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company  = trim($_POST['company_name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $subject  = trim($_POST['subject'] ?? '');
    $content  = trim($_POST['content'] ?? '');

    if ($company === '' || $position === '' || $content === '') {
        $error = "Company, position, and content are required.";
    } else {

        $q = "SELECT id FROM apply_destination WHERE company_name = $1 AND position = $2 LIMIT 1";
        $res = pg_query_params($conn, $q, [$company, $position]);
        $dest = pg_fetch_assoc($res);

        if ($dest) {
            $destination_id = $dest['id'];
        } else {
            $insertDest = "INSERT INTO apply_destination (company_name, position) VALUES ($1, $2) RETURNING id";
            $resInsert = pg_query_params($conn, $insertDest, [$company, $position]);
            $newDest = pg_fetch_assoc($resInsert);
            $destination_id = $newDest['id'];
        }

        $insertSql = "INSERT INTO cover_letters (destination_id, subject, content) VALUES ($1, $2, $3)";
        $subj = $subject !== '' ? $subject : "Application for {$position} – {$company}";
        pg_query_params($conn, $insertSql, [$destination_id, $subj, $content]);

        header("Location: cover_letter_list.php");
        exit;
    }
}

// daftar destination yang sudah ada, untuk autocomplete
$destRes = pg_query($conn, "SELECT id, company_name, position FROM apply_destination ORDER BY company_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>New Cover Letter</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
textarea{font-size:14px;line-height:1.6}
.suggest-item{cursor:pointer}
</style>
</head>
<body class="bg-gray-100 p-4">
<div class="max-w-6xl mx-auto px-4">
  <div class="flex justify-center">
    <div class="w-full max-w-3xl">
      <div class="bg-white rounded-xl shadow p-4">
      <div class="p-4">

      <h4 class="mb-3">📝 New Cover Letter <span class="text-xs text-gray-400 font-normal">(form dinamis)</span></h4>
      <p class="text-gray-500 mb-4">Generate dan sesuaikan surat lamaran secara otomatis dengan menekan <strong>Generate</strong> berdasarkan posisi &amp; skill yang relevan.</p>

      <?php if(!empty($error)): ?>
      <div class="p-3 rounded-lg bg-red-100 text-red-800 border border-red-200"><?=$error?></div>
      <?php endif; ?>

      <form method="post" id="formAdd">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
            <input type="text" name="company_name" id="company_name" list="company_suggest"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
              placeholder="e.g. PT Asuransi ABC" required>
            <datalist id="company_suggest">
              <?php while($d = pg_fetch_assoc($destRes)): ?>
                <option value="<?=htmlspecialchars($d['company_name'])?>">
                  <?=htmlspecialchars($d['company_name']) . ' – ' . htmlspecialchars($d['position'])?>
                </option>
              <?php endwhile; ?>
            </datalist>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
            <input type="text" name="position" id="position"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
              placeholder="Backend Developer (Node.js)" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">Tech/Keywords <small class="text-gray-400 font-normal">(opsional, mengarahkan generasi isi)</small></label>
          <input type="text" name="keywords" id="keywords"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="contoh: Laravel, PostgreSQL, REST APIs">
        </div>

        <div class="flex items-end mb-3 gap-2">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <input type="text" name="subject" id="subject"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
              placeholder="Application for Backend Developer – PT ABC">
          </div>
          <button type="button" id="btnGenerate"
            class="inline-block px-4 py-2 rounded-lg font-semibold text-white bg-purple-600 hover:bg-purple-700 transition flex items-center gap-2">
            <i class="fas fa-bolt"></i> Generate
          </button>
        </div>

        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">Cover Letter Content</label>
          <textarea name="content" id="content" rows="12"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required></textarea>
        </div>

        <div class="flex justify-between">
          <a href="cover_letter_list.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white">Cancel</a>
          <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-blue-600 text-white hover:bg-blue-700">Save Cover Letter</button>
        </div>
      </form>
      </div>
    </div>
    </div>
  </div>
</div>

<script>
const form = document.getElementById('formAdd');
const btn  = document.getElementById('btnGenerate');

btn.addEventListener('click', function(){
  const company  = document.getElementById('company_name').value.trim();
  const position = document.getElementById('position').value.trim();
  const keywords = document.getElementById('keywords').value.trim();

  if(!company || !position){
    Swal.fire({icon:'warning',title:'Isi dulu',text:'Company dan Position wajib diisi sebelum generate.'});
    return;
  }

  const fd = new FormData();
  fd.append('company', company);
  fd.append('position', position);
  fd.append('keywords', keywords);

  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating…';

  fetch('ajax_cover_letter.php', {method:'POST', body: fd})
    .then(r=>r.json())
    .then(res=>{
      if(res.ok){
        if(!document.getElementById('subject').value) document.getElementById('subject').value = res.subject;
        document.getElementById('content').value = res.content;
        Swal.fire({icon:'success',title:'Berhasil',text:'Cover letter berhasil digenerate.',timer:1200,showClass:{popup:'animate__animated animate__fadeIn'},hideClass:{popup:'animate__animated animate__fadeOut'}});
      } else {
        Swal.fire({icon:'error',title:'Gagal',text:res.message||'Generate gagal'});
      }
    })
    .catch(()=>{ Swal.fire({icon:'error',title:'Error',text:'Terhubung ke server gagal'}); })
    .finally(()=>{ btn.disabled=false; btn.innerHTML = '<i class="fas fa-bolt"></i> Generate'; });
});

form.addEventListener('submit', function(e){
  e.preventDefault();
  Swal.fire({title:'Save Cover Letter?',text:'Pastikan content sudah benar.',icon:'question',showCancelButton:true,confirmButtonText:'Yes, Save'})
    .then((r)=>{ if(r.isConfirmed){ e.target.submit(); } });
});
</script>
</body>
</html>
