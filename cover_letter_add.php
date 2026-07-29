<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $company  = trim($_POST['company_name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $content  = trim($_POST['content'] ?? '');

    if ($company === '' || $position === '' || $content === '') {
        $error = "Semua field wajib diisi.";
    } else {

        /* Cari destination */
        $q = "
            SELECT id 
            FROM apply_destination 
            WHERE company_name = $1 AND position = $2
            LIMIT 1
        ";
        $res = pg_query_params($conn, $q, [$company, $position]);
        $dest = pg_fetch_assoc($res);

        if ($dest) {
            $destination_id = $dest['id'];
        } else {
            /* Insert destination baru */
            $insertDest = "
                INSERT INTO apply_destination (company_name, position)
                VALUES ($1, $2)
                RETURNING id
            ";
            $resInsert = pg_query_params($conn, $insertDest, [$company, $position]);
            $newDest = pg_fetch_assoc($resInsert);
            $destination_id = $newDest['id'];
        }

        /* Insert cover letter */
        pg_query_params(
            $conn,
            "INSERT INTO cover_letters (destination_id, content) VALUES ($1, $2)",
            [$destination_id, $content]
        );

        header("Location: cover_letter_list.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>New Cover Letter</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-light p-4">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">

      <div class="card shadow-sm">
      <div class="card-body">

      <h4 class="mb-3">📝 New Cover Letter</h4>
      <p class="text-muted mb-4">Create a tailored cover letter for a specific company and position.</p>

      <form method="post" id="formAdd">

        <div class="mb-3">
          <label class="form-label">Company Name</label>
          <input type="text" name="company_name" class="form-control" placeholder="e.g. PT Asuransi ABC" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Position</label>
          <input type="text" name="position" class="form-control" placeholder="Backend Developer (Node.js)" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Cover Letter Content</label>
          <textarea name="content" rows="9" class="form-control" required></textarea>
        </div>

        <div class="d-flex justify-content-between">
          <a href="cover_letter_list.php" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Save Cover Letter</button>
        </div>

      </form>

      </div>
    </div>

    </div>
  </div>
</div>
<script>
document.getElementById('formAdd').addEventListener('submit', function(e){
  e.preventDefault();

  Swal.fire({
    title: 'Save Cover Letter?',
    text: 'Make sure the content is correct.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Save'
  }).then((result) => {
    if (result.isConfirmed) {
      e.target.submit();
    }
  });
});
</script>

</body>
</html>
