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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light p-4">

<div class="container">
<div class="card shadow-sm">
<div class="card-body">

<h4 class="mb-3">✏️ Edit Cover Letter</h4>
<p class="text-muted">Update destination or refine your content.</p>


<?php if(!empty($error)): ?>
<div class="alert alert-danger"><?=$error?></div>
<?php endif; ?>

<form method="post" id="formEdit">

  <div class="mb-3">
    <label class="form-label">Apply To</label>
    <select name="destination_id" class="form-select" required>
      <?php while($d = pg_fetch_assoc($dest)): ?>
      <option value="<?=$d['id']?>" 
        <?=$d['id']==$data['destination_id']?'selected':''?>>
        <?=$d['company_name']?> – <?=$d['position']?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" rows="10" class="form-control" required><?=htmlspecialchars($data['content'])?></textarea>
  </div>

  <button class="btn btn-primary">Update</button>
  <a href="cover_letter_list.php" class="btn btn-secondary">Cancel</a>
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
