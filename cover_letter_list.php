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
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<style>
body { background:#f4f6f8; padding:30px; }
.table-actions button, .table-actions a { margin-right:5px; }

</style>
</head>
<body>
<div class="max-w-6xl mx-auto px-4">
    <div class="mb-3">
        <a href="preview_cv.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white">
            <i class="fas fa-arrow-left"></i> Back to CV Preview
        </a>
    </div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h3>📄 Cover Letters</h3>
        <a href="cover_letter_add.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition whitespace-nowrap bg-blue-600 text-white hover:bg-blue-700">
            <i class="fas fa-plus-circle"></i> New Cover Letter
        </a>
    </div>

    <!-- Cover Letter Table -->
    <div class="bg-white rounded-xl shadow p-4">
        <div class="p-0">
            <table class="w-full border-collapse" id="coverLetterTable">
                <thead class="bg-gray-100">
                    <tr>
                        <th>Company</th>
                        <th>Position</th>
                        <th>Date</th>
                        <th width="250">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = pg_fetch_assoc($letters)): ?>
                    <tr class="even:bg-gray-50 hover:bg-gray-100">
                        <td><?= htmlspecialchars($r['company_name']) ?></td>
                        <td><?= htmlspecialchars($r['position']) ?></td>
                        <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                        <td class="table-actions">
                            <a href="cover_letter.php?id=<?= $r['id'] ?>" class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-cyan-500 text-white hover:bg-cyan-600">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="cover_letter_edit.php?id=<?= $r['id'] ?>" class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-yellow-500 text-white hover:bg-yellow-600">
                                <i class="fas fa-pencil-alt"></i> Edit
                            </a>
                            <button class="inline-block px-3 py-1 text-sm rounded-lg font-semibold text-center transition whitespace-nowrap bg-red-600 text-white hover:bg-red-700" onclick="deleteCoverLetter(<?= $r['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
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
