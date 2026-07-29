<?php
include 'config.php';

/* =====================
   VALIDASI ID
===================== */
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Invalid ID');
}

/* =====================
   AMBIL DATA
===================== */
$q = "
SELECT 
  cl.id,
  cl.content,
  ad.id AS destination_id
FROM cover_letters cl
JOIN apply_destination ad ON ad.id = cl.destination_id
WHERE cl.id = $1
";
$res = pg_query_params($conn, $q, [$id]);
$data = pg_fetch_assoc($res);

if (!$data) {
    die('Cover letter not found');
}

/* =====================
   LIST DESTINATION
===================== */
$dest = pg_query($conn, "SELECT * FROM apply_destination ORDER BY company_name");

/* =====================
   UPDATE PROCESS
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination_id = intval($_POST['destination_id']);
    $content        = trim($_POST['content']);

    if ($destination_id <= 0 || $content === '') {
        $error = "All fields are required.";
    } else {
        pg_query_params(
            $conn,
            "UPDATE cover_letters 
             SET destination_id=$1, content=$2, updated_at=NOW()
             WHERE id=$3",
            [$destination_id, $content, $id]
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
<title>Edit Cover Letter</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 p-4">

<div class="max-w-6xl mx-auto px-4">
<div class="bg-white rounded-xl shadow p-4">
<div class="p-4">

<h4 class="mb-3">✏️ Edit Cover Letter</h4>
<p class="text-gray-500">Update destination or refine your content.</p>


<?php if(!empty($error)): ?>
<div class="p-4 rounded-lg bg-red-100 text-red-800 border border-red-200"><?=$error?></div>
<?php endif; ?>

<form method="post" id="formEdit">

  <div class="mb-3">
    <label class="block text-sm font-medium text-gray-700 mb-1">Apply To</label>
    <select name="destination_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
      <?php while($d = pg_fetch_assoc($dest)): ?>
      <option value="<?=$d['id']?>" 
        <?=$d['id']==$data['destination_id']?'selected':''?>>
        <?=$d['company_name']?> – <?=$d['position']?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
    <textarea name="content" rows="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required><?=htmlspecialchars($data['content'])?></textarea>
  </div>

  <button class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-blue-600 text-white hover:bg-blue-700">Update</button>
  <a href="cover_letter_list.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-gray-500 text-white hover:bg-gray-600">Cancel</a>
</form>

</div>
</div>
</div>
<script>
document.getElementById('formEdit').addEventListener('submit', function(e){
  e.preventDefault();

  Swal.fire({
    title: 'Update Cover Letter?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, Update'
  }).then((result) => {
    if (result.isConfirmed) {
      e.target.submit();
    }
  });
});
</script>

</body>
</html>
