<?php
include 'config.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die('Invalid ID');

$q = "SELECT cl.id, cl.subject, cl.content, cl.destination_id, ad.company_name, ad.position FROM cover_letters cl JOIN apply_destination ad ON ad.id = cl.destination_id WHERE cl.id = $1";
$res = pg_query_params($conn, $q, [$id]);
$data = pg_fetch_assoc($res);
if (!$data) die('Cover letter not found');

$dest = pg_query($conn, "SELECT id, company_name, position FROM apply_destination ORDER BY company_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination_id = intval($_POST['destination_id']);
    $subject        = trim($_POST['subject'] ?? '');
    $content        = trim($_POST['content'] ?? '');

    if ($destination_id <= 0 || $content === '') {
        $error = "All fields are required.";
    } else {
        $subj = $subject !== '' ? $subject : "Application for {$data['position']} – {$data['company_name']}";
        pg_query_params(
            $conn,
            "UPDATE cover_letters SET destination_id=$1, subject=$2, content=$3, updated_at=NOW() WHERE id=$4",
            [$destination_id, $subj, $content, $id]
        );
        header("Location: cover_letter_list.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Cover Letter</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>textarea{font-size:14px;line-height:1.6}</style>
</head>
<body class="bg-gray-100 p-4">
<div class="max-w-6xl mx-auto px-4">
  <div class="bg-white rounded-xl shadow p-4">
    <div class="p-4">

    <h4 class="mb-3">✏️ Edit Cover Letter</h4>
    <p class="text-gray-500">Update destination, subject, or regenerate content.</p>

    <?php if(!empty($error)): ?>
    <div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200"><?=$error?></div>
    <?php endif; ?>

    <form method="post" id="formEdit">
      <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Apply To</label>
        <select name="destination_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
          <?php while($d = pg_fetch_assoc($dest)): ?>
          <option value="<?=$d['id']?>" <?=($d['id']==$data['destination_id'])?'selected':'' ?>>
            <?=htmlspecialchars($d['company_name'])?> – <?=htmlspecialchars($d['position'])?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
        <input type="text" name="subject" id="subject_edit"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
          value="<?=htmlspecialchars($data['subject'])?>">
      </div>

      <div class="mb-3 flex items-end gap-2">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
          <textarea name="content" id="content_edit" rows="12"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required><?=htmlspecialchars($data['content'])?></textarea>
        </div>
        <button type="button" id="btnRegen"
          class="inline-block px-4 py-2 rounded-lg font-semibold text-white bg-purple-600 hover:bg-purple-700 transition whitespace-nowrap">
          <i class="fas fa-bolt"></i> Regenerate
        </button>
      </div>

      <div class="flex justify-between">
        <a href="cover_letter_list.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white">Cancel</a>
        <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700">Update</button>
      </div>
    </form>
    </div>
  </div>

<script>
const btn = document.getElementById('btnRegen');
btn.addEventListener('click', function(){
  const sel = document.querySelector('select[name="destination_id"]');
  const selected = sel.options[sel.selectedIndex];
  const labelText = selected.text;            // "company – position"
  const parts = labelText.split(' – ');
  const company = parts[0] || '';
  const position = parts.slice(1).join(' – ') || '';

  const fd = new FormData();
  fd.append('company', company);
  fd.append('position', position);

  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  fetch('ajax_cover_letter.php', {method:'POST', body: fd})
    .then(r=>r.json())
    .then(res=>{
      if(res.ok){ Swal.fire({icon:'success',title:'Regenerated'}); }
      else { Swal.fire({icon:'error',title:'Gagal',text:res.message||'Generate gagal'}); }
    })
    .catch(()=>{ Swal.fire({icon:'error',title:'Error'}); })
    .finally(()=>{ btn.disabled=false; btn.innerHTML = '<i class="fas fa-bolt"></i> Regenerate'; });
});
document.getElementById('formEdit').addEventListener('submit', function(e){
  e.preventDefault();
  Swal.fire({title:'Update Cover Letter?',icon:'question',showCancelButton:true,confirmButtonText:'Yes, Update'})
    .then((r)=>{ if(r.isConfirmed){ e.target.submit(); } });
});
</script>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
