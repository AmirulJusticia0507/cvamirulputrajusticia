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
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-gray-100 p-4">
<div class="max-w-6xl mx-auto px-4">
  <div class="flex justify-center">
    <div class="w-full max-w-3xl">

      <div class="bg-white rounded-xl shadow p-4">
      <div class="p-4">

      <h4 class="mb-3">📝 New Cover Letter</h4>
      <p class="text-gray-500 mb-4">Create a tailored cover letter for a specific company and position.</p>

      <form method="post" id="formAdd">

        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
          <input type="text" name="company_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g. PT Asuransi ABC" required>
        </div>

        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
          <input type="text" name="position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Backend Developer (Node.js)" required>
        </div>

        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">Cover Letter Content</label>
          <textarea name="content" rows="9" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required></textarea>
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
