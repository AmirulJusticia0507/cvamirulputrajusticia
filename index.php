<?php
session_start();
include 'config.php';

$profileRes = pg_query($conn, "SELECT * FROM profile LIMIT 1");
$profile = pg_fetch_assoc($profileRes);


function e($str) {
    return htmlspecialchars($str ?? '');
}

require_once __DIR__ . '/handlers/experience_handler.php';

// =======================
// HANDLE SKILLS CRUD
// =======================
if(isset($_POST['action']) && $_POST['action'] === 'add_skill'){
    $skill_name = pg_escape_string($conn, $_POST['skill_name']);
    $level      = pg_escape_string($conn, $_POST['level']);
    $years      = floatval($_POST['years']);
    pg_query($conn, "INSERT INTO skills (skill_name, level, years) VALUES ('$skill_name','$level','$years')");
    header("Location: index.php");
    exit();
}

if(isset($_POST['action']) && $_POST['action'] === 'edit_skill'){
    $id         = (int)$_POST['id'];
    $skill_name = pg_escape_string($conn, $_POST['skill_name']);
    $level      = pg_escape_string($conn, $_POST['level']);
    $years      = floatval($_POST['years']);
    pg_query($conn, "UPDATE skills SET skill_name='$skill_name', level='$level', years='$years' WHERE id=$id");
    header("Location: index.php");
    exit();
}

if(isset($_GET['delete_skill'])){
    $id = (int)$_GET['delete_skill'];
    pg_query($conn, "DELETE FROM skills WHERE id=$id");
    header("Location: index.php");
    exit();
}

// =======================
// HANDLE UPDATE PROFILE
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {

    $sql = "
        UPDATE profile SET
            full_name = $1,
            headline  = $2,
            email     = $3,
            phone     = $4,
            linkedin  = $5,
            summary   = $6,
            updated_at = NOW()
        WHERE id = 1
    ";

    $params = [
        trim($_POST['full_name']),
        trim($_POST['headline']),
        trim($_POST['email']),
        trim($_POST['phone']),
        trim($_POST['linkedin']),
        trim($_POST['summary'])
    ];

    pg_query_params($conn, $sql, $params);
    header("Location: index.php");
    exit();
}


// =======================
// FETCH DATA
// =======================
$work_exp = pg_fetch_all(pg_query($conn, "SELECT * FROM work_experience ORDER BY start_date DESC"));
$skills_result = pg_query($conn, "SELECT * FROM skills ORDER BY id ASC");
$skills = $skills_result ? pg_fetch_all($skills_result) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CV - Amirul Putra Justicia</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
body { font-family: 'Inter', sans-serif; background: #f9f9f9; color: #333; padding:20px;}
.header { display:flex; flex-wrap:wrap; justify-content: space-between; align-items: center; margin-bottom:30px;}
.header-left { flex:1 1 60%; }
.header-right { flex:1 1 30%; text-align:right; }
.profile-img { width:120px; height:120px; object-fit:cover; border-radius:50%; border:3px solid #0d6efd;}
h1 { font-weight:700; margin-bottom:0.3rem;}
h2 { border-bottom:2px solid #0d6efd; padding-bottom:5px; margin-top:25px; margin-bottom:15px;}
.card { background:#fff; border:none; border-radius:12px; margin-bottom:15px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.05);}
.section-title { font-weight:600; color:#0d6efd; font-size:16px; margin-bottom:8px;}
.skill-badge { display:inline-block; background:#0d6efd; color:#fff; font-weight:500; padding:5px 10px; margin:2px; border-radius:20px; font-size:12px; transition:0.2s;}
.skill-badge:hover { background:#0b5ed7;}
.contact-info { font-size:13px; color:#555;}
.btn-crud { margin-left:5px;}
ul { padding-left:1.2rem;}
</style>
</head>
<body>
<div class="container">

<!-- Header -->
<div class="header">
    <!-- EDIT PROFILE MODAL -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form method="post">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="modal-header">
                        <h5 class="modal-title">✏ Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Full Name</label><span style="color:red;">*</span>
                                <input type="text" name="full_name" class="form-control"
                                    value="<?= e($profile['full_name']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Headline</label><span style="color:red;">*</span>
                                <input type="text" name="headline" class="form-control"
                                    value="<?= e($profile['headline']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label><span style="color:red;">*</span>
                                <input type="email" name="email" class="form-control"
                                    value="<?= e($profile['email']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone (WhatsApp)</label><span style="color:red;">*</span>
                                <input type="text" name="phone" class="form-control"
                                    value="<?= e($profile['phone']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">LinkedIn</label><span style="color:red;">*</span>
                                <input type="text" name="linkedin" class="form-control"
                                    value="<?= e($profile['linkedin']) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Summary</label><span style="color:red;">*</span>
                                <textarea name="summary" class="form-control" required rows="4"><?= e($profile['summary']) ?></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-success">
                            💾 Save Changes
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="header-left">
        <div class="d-flex align-items-center gap-2">
        <h1 class="mb-0"><?= e($profile['full_name']) ?></h1>
            <button class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#editProfileModal">
                ✏ Edit
            </button>
        </div>
        <p class="mb-1"><?= e($profile['headline']) ?></p>

        <p class="contact-info mb-1">
            Email: <a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a> |
            Phone: <a href="https://wa.me/<?= preg_replace('/\D/', '', $profile['phone']) ?>">
                <?= e($profile['phone']) ?>
            </a> |
            LinkedIn: <a href="<?= e($profile['linkedin']) ?>" target="_blank">Profile</a>
        </p>

        <p><?= e($profile['summary']) ?></p>
    </div>

    <div class="header-right text-center">
        <img src="<?= e($profile['photo'] ?? 'uploads/profile/default.jpg') ?>"
             alt="Profile Photo"
             class="profile-img mb-2">

        <!-- FORM UPDATE FOTO -->
        <form method="post" action="profile_update_photo.php" enctype="multipart/form-data">
            <input type="file"
                   name="photo"
                   accept="image/jpeg,image/png"
                   class="form-control form-control-sm mb-2"
                   required>
            <button class="btn btn-sm btn-outline-primary w-100">
                🔄 Update Photo
            </button>
        </form>
    </div>
</div>

<button id="preview-cv" class="btn btn-info w-100 mt-3">
    <i class="bi bi-eye"></i> Preview CV
</button>

<div class="row">
    <!-- Kolom Kiri -->
    <div class="col-md-4">
        <h2>Languages</h2>
        <div class="card">
            <span class="skill-badge">Indonesian</span>
            <span class="skill-badge">English</span>
            <span class="skill-badge">Javanese</span>
        </div>

        <h2>Technical Skills</h2>
        <div class="card">
            <?php foreach($skills as $skill): ?>
                <span class="skill-badge">
                    <?= e($skill['skill_name']); ?> (<?= e($skill['level']); ?>, <?= e($skill['years']); ?> yrs)
                    <button class="btn btn-sm btn-primary btn-crud" data-bs-toggle="modal" data-bs-target="#editSkillModal<?= $skill['id']; ?>"><i class="bi bi-pencil"></i></button>
                    <a href="?delete_skill=<?= $skill['id']; ?>" class="btn btn-sm btn-danger btn-crud" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                </span>

                <!-- Edit Skill Modal -->
                <div class="modal fade" id="editSkillModal<?= $skill['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Skill</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="edit_skill">
                                    <input type="hidden" name="id" value="<?= $skill['id']; ?>">
                                    <input type="text" name="skill_name" class="form-control mb-2" value="<?= e($skill['skill_name']); ?>" placeholder="Skill Name" required>
                                    <input type="text" name="level" class="form-control mb-2" value="<?= e($skill['level']); ?>" placeholder="Level (Beginner/Intermediate/Expert)">
                                    <input type="number" step="0.1" name="years" class="form-control mb-2" value="<?= e($skill['years']); ?>" placeholder="Years of Experience (0 if none)">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

            <!-- Add Skill Button -->
            <button class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addSkillModal"><i class="bi bi-plus"></i> Add Skill</button>
        </div>

        <!-- Add Skill Modal -->
        <div class="modal fade" id="addSkillModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Skill</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add_skill">
                            <input type="text" name="skill_name" class="form-control mb-2" placeholder="Skill Name" required>
                            <input type="text" name="level" class="form-control mb-2" placeholder="Level (Beginner/Intermediate/Expert)">
                            <input type="number" step="0.1" name="years" class="form-control mb-2" placeholder="Years of Experience (0 if none)">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

<!-- Kolom Kanan -->
<div class="col-md-8">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Work Experience</h2>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus"></i> Add
        </button>
    </div>

    <?php if ($work_exp): ?>
        <?php foreach ($work_exp as $row): ?>
            <?php 
                $present = in_array($row['present'], ['t', true, 1], true); 
                $descriptionLines = array_filter(array_map('trim', explode("\n", $row['description'])));
            ?>
            <!-- Work Experience Card -->
            <div class="card mb-4 shadow-sm p-3">
                <!-- Header: Company & Position -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0"><?= e($row['company']); ?> – <span class="fw-semibold"><?= e($row['position']); ?></span></h5>
                    <span class="badge <?= $present ? 'bg-success' : 'bg-secondary'; ?>">
                        <i class="bi <?= $present ? 'bi-check-circle' : 'bi-archive'; ?>"></i>
                        <?= $present ? 'Active' : 'Completed'; ?>
                    </span>
                </div>

                <!-- Subtitle: Dates, Location, Status -->
                <p class="mb-2 text-muted">
                    <em><?= date('M Y', strtotime($row['start_date'])) ?> – <?= $present ? 'Present' : date('M Y', strtotime($row['end_date'])) ?></em>
                    | <?= e($row['location']); ?>
                    | <strong><?= e($row['status_kerja']); ?></strong>
                </p>

                <!-- PRAQ Bullets -->
                <?php if ($descriptionLines): ?>
                    <ul class="mb-2 ps-3">
                        <?php foreach($descriptionLines as $bullet): ?>
                            <li><?= e($bullet); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <!-- Optional Tech Stack -->
                <?php if(!empty($row['tech_stack'])): ?>
                    <p class="mb-1"><strong>Tech Stack:</strong> <span class="text-secondary"><?= e($row['tech_stack']); ?></span></p>
                <?php endif; ?>

                <!-- Optional Project / Key Achievement -->
                <?php if(!empty($row['project'])): ?>
                    <p class="mb-0"><strong>Project / Key Achievement:</strong> <span class="text-primary"><?= e($row['project']); ?></span></p>
                <?php endif; ?>

                <!-- Edit/Delete Buttons -->
                <div class="d-flex gap-1 mt-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="?delete=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>

            <!-- Modal Edit (loop) -->
            <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Work Experience</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">

                                <div class="mb-2">
                                    <label>Company</label>
                                    <input type="text" name="company" class="form-control" value="<?= e($row['company']); ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label>Position</label>
                                    <input type="text" name="position" class="form-control" value="<?= e($row['position']); ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?= e($row['start_date']); ?>" required>
                                </div>

                                <div class="mb-2">
                                    <label>Location</label>
                                    <input type="text" name="location" class="form-control" value="<?= e($row['location']); ?>" placeholder="City, Country">
                                </div>

                                <div class="mb-2">
                                    <label>Status Kerja</label>
                                    <select name="status_kerja" class="form-select">
                                        <?php $statuses = ['Full-time','Contract','Project-based','Freelance']; ?>
                                        <?php foreach($statuses as $status): ?>
                                            <option value="<?= $status ?>" <?= $row['status_kerja'] === $status ? 'selected' : ''; ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label>Status Pekerjaan</label>
                                    <select name="present" class="form-select" onchange="toggleEnd<?= $row['id']; ?>(this.value)">
                                        <option value="0" <?= !$present ? 'selected' : ''; ?>>Sudah Selesai</option>
                                        <option value="1" <?= $present ? 'selected' : ''; ?>>Masih Bekerja</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label>End Date</label>
                                    <input type="date" id="end<?= $row['id']; ?>" name="end_date" class="form-control"
                                        value="<?= (!$present && $row['end_date']) ? e($row['end_date']) : ''; ?>"
                                        <?= $present ? 'disabled' : ''; ?>>
                                </div>

                                <div class="mb-2">
                                    <label>Description (PRAQ & metrics)</label>
                                    <textarea name="description" class="form-control" rows="3"><?= e($row['description']); ?></textarea>
                                </div>

                                <div class="mb-2">
                                    <label>Project / Key Achievement</label>
                                    <textarea name="project" class="form-control" rows="2"><?= e($row['project']); ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                function toggleEnd<?= $row['id']; ?>(val){
                    const end = document.getElementById('end<?= $row['id']; ?>');
                    end.disabled = (val === '1');
                    if(val==='1') end.value='';
                }
            </script>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No work experience data available.</p>
    <?php endif; ?>

    <!-- Add Work Experience Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Work Experience</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">

                        <input type="text" name="company" class="form-control mb-2" placeholder="Company" required>
                        <input type="text" name="position" class="form-control mb-2" placeholder="Position" value="Fullstack/Web Systems Engineer – GovTech" required>
                        <input type="date" name="start_date" class="form-control mb-2" required>
                        <input type="text" name="location" class="form-control mb-2" placeholder="City, Country">

                        <div class="mb-2">
                            <label>Status Kerja</label>
                            <select name="status_kerja" class="form-select">
                                <?php foreach($statuses as $status): ?>
                                    <option value="<?= $status ?>"><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="present" id="presentCheck" onchange="toggleEndAdd(this)">
                            <label class="form-check-label" for="presentCheck">Currently Working Here</label>
                        </div>

                        <input type="date" name="end_date" id="endAdd" class="form-control mb-2">
                        <textarea name="description" class="form-control mb-2" rows="3" placeholder="Action – Result – Quantify"></textarea>
                        <textarea name="project" class="form-control mb-2" rows="2" placeholder="Project / Key Achievement"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</div>


</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){

    // Generic toggle function
    function toggleEnd(selectElement) {
        const targetId = selectElement.dataset.target;
        const endInput = document.getElementById(targetId);

        if (!endInput) return;

        if (selectElement.value === '1' || selectElement.checked) {
            endInput.disabled = true;
            endInput.value = '';
        } else {
            endInput.disabled = false;
        }
    }

    // Attach to all select present fields
    document.querySelectorAll('[data-toggle="end-date"]').forEach(el => {
        el.addEventListener('change', function(){
            toggleEnd(this);
        });
    });

    // Preview CV confirmation
    const previewBtn = document.getElementById('preview-cv');
    if(previewBtn){
        previewBtn.addEventListener('click', function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah CV sudah benar dan fix?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Preview',
                cancelButtonText: 'Belum, revisi'
            }).then((result) => {
                if(result.isConfirmed){
                    fetch('log_cv.php', {method:'POST'})
                        .finally(() => {
                            window.location.href = 'preview_cv.php';
                        });
                }
            });
        });
    }

});
</script>
</body>
</html>
