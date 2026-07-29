<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die('Cover letter ID tidak ditemukan');
}

$id = (int) $_GET['id'];

$query = "
SELECT 
    cl.subject,
    cl.content,
    ad.company_name,
    ad.position
FROM cover_letters cl
JOIN apply_destination ad ON cl.destination_id = ad.id
WHERE cl.id = $id
";

$result = pg_query($conn, $query);
$data = pg_fetch_assoc($result);

if (!$data) {
    die('Data cover letter tidak ditemukan');
}

// ambil waiting list
$emails = pg_query_params($conn, "
    SELECT * FROM cover_letter_waiting_list
    WHERE cover_letter_id = $1
    ORDER BY created_at DESC
", [$id]);

// handle submit email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        pg_query_params(
            $conn,
            "INSERT INTO cover_letter_waiting_list (cover_letter_id, email)
             VALUES ($1, $2)",
            [$id, $email]
        );
        header("Location: cover_letter.php?id=".$id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cover Letter Preview</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body { background:#f4f6f8; padding:30px; }
.letter {
    max-width: 800px;
    margin:auto;
    background:#fff;
    padding:50px;
    border-radius:8px;
    font-family: "Times New Roman", serif;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

</style>
</head>
<body>

<div class="letter">
    <div class="mb-4 text-right" style="font-size:0.9rem;">
        Yogyakarta, <?= date('d F Y', strtotime($data['created_at'] ?? 'now')) ?>
    </div>
    <p>
        Hiring Manager<br>
        <strong><?= htmlspecialchars($data['company_name']) ?></strong><br>
        <?= htmlspecialchars($data['position']) ?>
    </p>

    <p>Dear Hiring Manager,</p>

    <p><?= nl2br(htmlspecialchars($data['content'])) ?></p>

    <p>
        Sincerely,<br>
        <strong>Amirul Putra Justicia</strong>
    </p>
</div>

<div class="text-center mt-4">
    <a href="download_cover_letter_pdf.php?id=<?= $id ?>" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-red-600 text-white hover:bg-red-700">
        Download PDF
    </a>
    <button class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-gray-500 text-white hover:bg-gray-600" onclick="goBack()">Back</button>
</div>


<script>
function goBack(){
  Swal.fire({
    title: 'Back to list?',
    text: 'Unsaved changes will be lost.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes'
  }).then((r)=>{
    if(r.isConfirmed){
      window.location = 'cover_letter_list.php';
    }
  });
}
</script>
</body>
</html>
