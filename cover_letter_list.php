<?php
include 'config.php';

// Ambil semua cover letter
$q = "
SELECT cl.id, ad.company_name, ad.position, cl.created_at
FROM cover_letters cl
JOIN apply_destination ad ON cl.destination_id = ad.id
ORDER BY cl.created_at DESC
";
$letters = pg_query($conn, $q);

// Handle waiting list submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['waiting_email'], $_POST['cover_letter_id'])) {
    $email = trim($_POST['waiting_email']);
    $cover_letter_id = intval($_POST['cover_letter_id']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $cover_letter_id > 0) {
        pg_query_params(
            $conn,
            "INSERT INTO cover_letter_waiting_list (cover_letter_id, email) VALUES ($1,$2)",
            [$cover_letter_id, $email]
        );
        header("Location: cover_letter_list.php");
        exit;
    } else {
        $waiting_error = "Email tidak valid atau cover letter tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cover Letter Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<style>
body { background:#f4f6f8; padding:30px; }
.table-actions button, .table-actions a { margin-right:5px; }
.card { margin-bottom:30px; }
</style>
</head>
<body>
<div class="container">
    <div class="mb-3">
        <a href="preview_cv.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to CV Preview
        </a>
    </div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📄 Cover Letters</h3>
        <a href="cover_letter_add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Cover Letter
        </a>
    </div>

    <!-- Cover Letter Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover" id="coverLetterTable">
                <thead class="table-light">
                    <tr>
                        <th>Company</th>
                        <th>Position</th>
                        <th>Date</th>
                        <th width="250">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = pg_fetch_assoc($letters)): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['company_name']) ?></td>
                        <td><?= htmlspecialchars($r['position']) ?></td>
                        <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                        <td class="table-actions">
                            <a href="cover_letter.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="cover_letter_edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <button class="btn btn-sm btn-danger" onclick="deleteCoverLetter(<?= $r['id'] ?>)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- JS Section -->
<script>
// SweetAlert delete confirmation
function deleteCoverLetter(id) {
    Swal.fire({
        title: 'Delete Cover Letter?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = 'cover_letter_delete.php?id=' + id;
        }
    });
}

// Initialize DataTables
$(document).ready(function() {
    $('#coverLetterTable').DataTable({
        "order": [[2,"desc"]],
        "columnDefs":[{ "orderable": false, "targets": 3 }]
    });

    // Waiting list confirmation (jika nanti diaktifkan)
    $('.waiting-form').on('submit', function(e){
        e.preventDefault();
        let form = this;
        Swal.fire({
            title: 'Add this email to waiting list?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((r)=>{
            if(r.isConfirmed){
                form.submit();
            }
        });
    });
});
</script>
</body>
</html>
