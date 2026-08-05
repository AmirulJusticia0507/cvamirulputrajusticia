<?php
session_start();
include 'config.php';

$profileRes = pg_query($conn, "SELECT * FROM profile LIMIT 1");
$profile = pg_fetch_assoc($profileRes);


function e($str) {
    return htmlspecialchars($str ?? '');
}

require_once __DIR__ . '/handlers/experience_handler.php';
require_once __DIR__ . '/handlers/portfolio_handler.php';

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
// HANDLE LANGUAGES CRUD
// =======================
if(isset($_POST['action']) && $_POST['action'] === 'add_language'){
    $lang_name = pg_escape_string($conn, $_POST['language_name']);
    $proficiency = pg_escape_string($conn, $_POST['proficiency']);
    pg_query($conn, "INSERT INTO languages (language_name, proficiency) VALUES ('$lang_name','$proficiency')");
    header("Location: index.php");
    exit();
}

if(isset($_POST['action']) && $_POST['action'] === 'edit_language'){
    $id = (int)$_POST['id'];
    $lang_name = pg_escape_string($conn, $_POST['language_name']);
    $proficiency = pg_escape_string($conn, $_POST['proficiency']);
    pg_query($conn, "UPDATE languages SET language_name='$lang_name', proficiency='$proficiency' WHERE id=$id");
    header("Location: index.php");
    exit();
}

if(isset($_GET['delete_language'])){
    $id = (int)$_GET['delete_language'];
    pg_query($conn, "DELETE FROM languages WHERE id=$id");
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
$languages_result = pg_query($conn, "SELECT * FROM languages ORDER BY id ASC");
$languages = $languages_result ? pg_fetch_all($languages_result) : [];
$portfolio_result = pg_query($conn, "SELECT * FROM portfolio ORDER BY sort_order ASC, id ASC");
$portfolio = $portfolio_result ? pg_fetch_all($portfolio_result) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CV - Amirul Putra Justicia</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config={theme:{extend:{colors:{primary:'#0d6efd'},fontFamily:{sans:['Inter','sans-serif']}}}}
</script>
<style>
body { font-family: 'Inter', sans-serif; background: #f9f9f9; color: #333; padding:20px;}
.header { display:flex; flex-wrap:wrap; justify-content: space-between; align-items: center; margin-bottom:30px;}
.header-left { flex:1 1 60%; }
.header-right { flex:1 1 30%; text-align:right; }
h1 { font-weight:700; margin-bottom:0.3rem;}
h2 { border-bottom:2px solid #0d6efd; padding-bottom:5px; margin-top:25px; margin-bottom:15px;}
.section-title { font-weight:600; color:#0d6efd; font-size:16px; margin-bottom:8px;}
.contact-info { font-size:13px; color:#555;}
.btn-crud { margin-left:5px;}
</style>
</head>
<body>
<div class="max-w-6xl mx-auto px-4">

<!-- Header -->
<div class="header">
    <!-- EDIT PROFILE MODAL -->
    <div id="editProfileModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">

                <form method="post">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold">✏ Edit Profile</h5>
                        <button type="button" onclick="closeModal('editProfileModal')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                    </div>

                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label><span style="color:red;">*</span>
                                <input type="text" name="full_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    value="<?= e($profile['full_name']) ?>" required>
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Headline</label><span style="color:red;">*</span>
                                <input type="text" name="headline" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    value="<?= e($profile['headline']) ?>" required>
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label><span style="color:red;">*</span>
                                <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    value="<?= e($profile['email']) ?>" required>
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone (WhatsApp)</label><span style="color:red;">*</span>
                                <input type="text" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    value="<?= e($profile['phone']) ?>" required>
                            </div>

                            <div class="col-span-12">
                                <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label><span style="color:red;">*</span>
                                <input type="text" name="linkedin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    value="<?= e($profile['linkedin']) ?>" required>
                            </div>

                            <div class="col-span-12">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Summary</label><span style="color:red;">*</span>
                                <textarea name="summary" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required rows="4"><?= e($profile['summary']) ?></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="flex justify-end gap-2 p-4 border-t">
                        <button type="button" onclick="closeModal('editProfileModal')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                            Cancel
                        </button>
                        <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            💾 Save Changes
                        </button>
                    </div>

                </form>

        </div>
    </div>

    <div class="header-left">
        <div class="flex items-center gap-2">
        <h1 class="mb-0"><?= e($profile['full_name']) ?></h1>
            <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white text-sm"
                    onclick="openModal('editProfileModal')">
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
             class="w-32 h-32 object-cover rounded-full border-4 border-blue-600 shadow-md mb-2">

        <!-- FORM UPDATE FOTO -->
        <form method="post" action="profile_update_photo.php" enctype="multipart/form-data">
            <input type="file"
                   name="photo"
                   accept="image/jpeg,image/png"
                   class="w-full px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm mb-2"
                   required>
            <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white w-full text-sm">
                🔄 Update Photo
            </button>
        </form>
    </div>
</div>

<button id="preview-cv" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-cyan-500 text-white hover:bg-cyan-600 w-full mt-3">
    <i class="fas fa-eye"></i> Preview CV
</button>

<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
    <!-- Kolom Kiri -->
    <div class="md:col-span-4">
        <h2>Languages</h2>
        <div class="bg-white rounded-xl shadow p-4">
            <?php foreach($languages as $lang): ?>
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium my-1">
                    <?= e($lang['language_name']); ?> (<?= e($lang['proficiency']); ?>)
                    <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 btn-crud text-sm" onclick="openModal('editLangModal<?= $lang['id']; ?>')"><i class="fas fa-pencil-alt"></i></button>
                    <a href="?delete_language=<?= $lang['id']; ?>" class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-red-600 text-white hover:bg-red-700 btn-crud text-sm" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                </span>

                <!-- Edit Language Modal -->
                <div id="editLangModal<?= $lang['id']; ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                        <form method="POST">
                            <div class="flex justify-between items-center p-4 border-b">
                                <h5 class="text-lg font-semibold">Edit Language</h5>
                                <button type="button" onclick="closeModal('editLangModal<?= $lang['id']; ?>')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                            </div>
                            <div class="p-4">
                                <input type="hidden" name="action" value="edit_language">
                                <input type="hidden" name="id" value="<?= $lang['id']; ?>">
                                <input type="text" name="language_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" value="<?= e($lang['language_name']); ?>" placeholder="Language" required>
                                <select name="proficiency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                                    <?php $profs = ['Native','Fluent','Advanced','Intermediate','Basic']; ?>
                                    <?php foreach($profs as $p): ?>
                                        <option value="<?= $p ?>" <?= $lang['proficiency'] === $p ? 'selected' : ''; ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex justify-end gap-2 p-4 border-t">
                                <button type="button" onclick="closeModal('editLangModal<?= $lang['id']; ?>')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php endforeach; ?>

            <!-- Add Language Button -->
            <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-green-600 text-white hover:bg-green-700 mt-2 text-sm" onclick="openModal('addLangModal')"><i class="fas fa-plus"></i> Add Language</button>
        </div>

        <!-- Add Language Modal -->
        <div id="addLangModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <form method="POST">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold">Add Language</h5>
                        <button type="button" onclick="closeModal('addLangModal')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                    </div>
                    <div class="p-4">
                        <input type="hidden" name="action" value="add_language">
                        <input type="text" name="language_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Language (e.g. English)" required>
                        <select name="proficiency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                            <option value="">Select proficiency</option>
                            <option value="Native">Native</option>
                            <option value="Fluent">Fluent</option>
                            <option value="Advanced">Advanced</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Basic">Basic</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 p-4 border-t">
                        <button type="button" onclick="closeModal('addLangModal')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <h2>Technical Skills</h2>
        <div class="bg-white rounded-xl shadow p-4">
            <?php foreach($skills as $skill): ?>
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium my-1">
                    <?= e($skill['skill_name']); ?> (<?= e($skill['level']); ?>, <?= e($skill['years']); ?> yrs)
                    <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 btn-crud text-sm" onclick="openModal('editSkillModal<?= $skill['id']; ?>')"><i class="fas fa-pencil-alt"></i></button>
                    <a href="?delete_skill=<?= $skill['id']; ?>" class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-red-600 text-white hover:bg-red-700 btn-crud text-sm" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                </span>

                <!-- Edit Skill Modal -->
                <div id="editSkillModal<?= $skill['id']; ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                        <form method="POST">
                            <div class="flex justify-between items-center p-4 border-b">
                                <h5 class="text-lg font-semibold">Edit Skill</h5>
                                <button type="button" onclick="closeModal('editSkillModal<?= $skill['id']; ?>')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                            </div>
                            <div class="p-4">
                                <input type="hidden" name="action" value="edit_skill">
                                <input type="hidden" name="id" value="<?= $skill['id']; ?>">
                                <input type="text" name="skill_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" value="<?= e($skill['skill_name']); ?>" placeholder="Skill Name" required>
                                <input type="text" name="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" value="<?= e($skill['level']); ?>" placeholder="Level (Beginner/Intermediate/Expert)">
                                <input type="number" step="0.1" name="years" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" value="<?= e($skill['years']); ?>" placeholder="Years of Experience (0 if none)">
                            </div>
                            <div class="flex justify-end gap-2 p-4 border-t">
                                <button type="button" onclick="closeModal('editSkillModal<?= $skill['id']; ?>')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php endforeach; ?>

            <!-- Add Skill Button -->
            <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-green-600 text-white hover:bg-green-700 mt-2 text-sm" onclick="openModal('addSkillModal')"><i class="fas fa-plus"></i> Add Skill</button>
        </div>

        <!-- Add Skill Modal -->
        <div id="addSkillModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <form method="POST">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h5 class="text-lg font-semibold">Add Skill</h5>
                        <button type="button" onclick="closeModal('addSkillModal')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                    </div>
                    <div class="p-4">
                        <input type="hidden" name="action" value="add_skill">
                        <input type="text" name="skill_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Skill Name" required>
                        <input type="text" name="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Level (Beginner/Intermediate/Expert)">
                        <input type="number" step="0.1" name="years" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Years of Experience (0 if none)">
                    </div>
                    <div class="flex justify-end gap-2 p-4 border-t">
                        <button type="button" onclick="closeModal('addSkillModal')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

<!-- Kolom Kanan -->
<div class="md:col-span-8">
    <div class="flex justify-between items-center mb-3">
        <h2>Work Experience</h2>
        <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-green-600 text-white hover:bg-green-700 text-sm" onclick="openModal('addModal')">
            <i class="fas fa-plus"></i> Add
        </button>
    </div>

    <?php if ($work_exp): ?>
        <?php foreach ($work_exp as $row): ?>
            <?php 
                $present = in_array($row['present'], ['t', true, 1], true); 
                $descriptionLines = array_filter(array_map('trim', explode("\n", $row['description'])));
            ?>
            <!-- Work Experience Card -->
            <div class="bg-white rounded-xl shadow mb-4 shadow-sm p-3">
                <!-- Header: Company & Position -->
                <div class="flex justify-between items-center mb-2">
                    <h5 class="mb-0"><?= e($row['company']); ?> – <span class="font-semibold"><?= e($row['position']); ?></span></h5>
                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full text-white <?= $present ? 'bg-green-500' : 'bg-gray-500'; ?>">
                        <i class="fas <?= $present ? 'fa-check-circle' : 'fa-archive'; ?>"></i>
                        <?= $present ? 'Active' : 'Completed'; ?>
                    </span>
                </div>

                <!-- Subtitle: Dates, Location, Status -->
                <p class="mb-2 text-gray-500">
                    <em><?= date('M Y', strtotime($row['start_date'])) ?> – <?= $present ? 'Present' : date('M Y', strtotime($row['end_date'])) ?></em>
                    | <?= e($row['location']); ?>
                    | <strong><?= e($row['status_kerja']); ?></strong>
                </p>

                <!-- PRAQ Bullets -->
                <?php if ($descriptionLines): ?>
                    <ul class="mb-2 pl-3">
                        <?php foreach($descriptionLines as $bullet): ?>
                            <li><?= e($bullet); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <!-- Optional Tech Stack -->
                <?php if(!empty($row['tech_stack'])): ?>
                    <p class="mb-1"><strong>Tech Stack:</strong> <span class="text-gray-500"><?= e($row['tech_stack']); ?></span></p>
                <?php endif; ?>

                <!-- Optional Project / Key Achievement -->
                <?php if(!empty($row['project'])): ?>
                    <p class="mb-0"><strong>Project / Key Achievement:</strong> <span class="text-blue-600"><?= e($row['project']); ?></span></p>
                <?php endif; ?>

                <!-- Edit/Delete Buttons -->
                <div class="flex gap-1 mt-2">
                    <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 text-sm" onclick="openModal('editModal<?= $row['id']; ?>')">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <a href="?delete=<?= $row['id']; ?>" class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-red-600 text-white hover:bg-red-700 text-sm" onclick="return confirm('Are you sure?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>

            <!-- Modal Edit (loop) -->
            <div id="editModal<?= $row['id']; ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
                    <form method="POST">
                        <div class="flex justify-between items-center p-4 border-b">
                            <h5 class="text-lg font-semibold">Edit Work Experience</h5>
                            <button type="button" onclick="closeModal('editModal<?= $row['id']; ?>')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                        </div>
                        <div class="p-4">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">

                            <div class="mb-2">
                                <label>Company</label>
                                <input type="text" name="company" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= e($row['company']); ?>" required>
                            </div>

                            <div class="mb-2">
                                <label>Position</label>
                                <input type="text" name="position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= e($row['position']); ?>" required>
                            </div>

                            <div class="mb-2">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= e($row['start_date']); ?>" required>
                            </div>

                            <div class="mb-2">
                                <label>Location</label>
                                <input type="text" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= e($row['location']); ?>" placeholder="City, Country">
                            </div>

                            <div class="mb-2">
                                <label>Status Kerja</label>
                                <select name="status_kerja" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <?php $statuses = ['Full-time','Contract','Project-based','Freelance']; ?>
                                    <?php foreach($statuses as $status): ?>
                                        <option value="<?= $status ?>" <?= $row['status_kerja'] === $status ? 'selected' : ''; ?>><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label>Status Pekerjaan</label>
                                <select name="present" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" onchange="toggleEnd<?= $row['id']; ?>(this.value)">
                                    <option value="0" <?= !$present ? 'selected' : ''; ?>>Sudah Selesai</option>
                                    <option value="1" <?= $present ? 'selected' : ''; ?>>Masih Bekerja</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label>End Date</label>
                                <input type="date" id="end<?= $row['id']; ?>" name="end_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                    value="<?= (!$present && $row['end_date']) ? e($row['end_date']) : ''; ?>"
                                    <?= $present ? 'disabled' : ''; ?>>
                            </div>

                            <div class="mb-2">
                                <label>Description (PRAQ & metrics)</label>
                                <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" rows="3"><?= e($row['description']); ?></textarea>
                            </div>

                            <div class="mb-2">
                                <label>Project / Key Achievement</label>
                                <textarea name="project" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" rows="2"><?= e($row['project']); ?></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 p-4 border-t">
                            <button type="button" onclick="closeModal('editModal<?= $row['id']; ?>')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
                        </div>
                    </form>
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

    <!-- ================= PORTFOLIO (FEATURED PROJECTS) ================= -->
    <div class="flex justify-between items-center mb-3 mt-6">
        <h2>Featured Projects / Portfolio</h2>
        <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-green-600 text-white hover:bg-green-700 text-sm" onclick="openModal('addPortfolioModal')">
            <i class="fas fa-plus"></i> Add
        </button>
    </div>

    <?php if ($portfolio): ?>
        <?php foreach ($portfolio as $pf): ?>
            <div class="bg-white rounded-xl shadow mb-4 shadow-sm p-3">
                <div class="flex justify-between items-center mb-2">
                    <h5 class="mb-0"><?= e($pf['title']); ?></h5>
                    <div class="flex gap-1">
                        <button class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 text-sm" onclick="openModal('editPortfolioModal<?= $pf['id']; ?>')">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <a href="?delete_portfolio=<?= $pf['id']; ?>" class="inline-block px-3 py-1 rounded-lg font-semibold text-center transition bg-red-600 text-white hover:bg-red-700 text-sm" onclick="return confirm('Delete this project?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>

                <?php if(!empty($pf['description'])): ?>
                    <p class="mb-1 text-gray-600"><?= e($pf['description']); ?></p>
                <?php endif; ?>

                <?php if(!empty($pf['tech_stack'])): ?>
                    <p class="mb-1"><strong>Tech Stack:</strong> <span class="text-gray-500"><?= e($pf['tech_stack']); ?></span></p>
                <?php endif; ?>

                <p class="mb-0">
                    <?php if(!empty($pf['repo_url'])): ?>
                        <a href="<?= e($pf['repo_url']); ?>" target="_blank" class="text-blue-600 underline">GitHub</a>
                    <?php endif; ?>
                    <?php if(!empty($pf['repo_url']) && !empty($pf['demo_url'])): ?> · <?php endif; ?>
                    <?php if(!empty($pf['demo_url'])): ?>
                        <a href="<?= e($pf['demo_url']); ?>" target="_blank" class="text-green-600 underline">Live Demo</a>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Edit Portfolio Modal -->
            <div id="editPortfolioModal<?= $pf['id']; ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
                    <form method="POST">
                        <div class="flex justify-between items-center p-4 border-b">
                            <h5 class="text-lg font-semibold">Edit Portfolio Project</h5>
                            <button type="button" onclick="closeModal('editPortfolioModal<?= $pf['id']; ?>')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                        </div>
                        <div class="p-4">
                            <input type="hidden" name="action" value="edit_portfolio">
                            <input type="hidden" name="id" value="<?= $pf['id']; ?>">

                            <div class="mb-2">
                                <label>Title</label>
                                <input type="text" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= e($pf['title']); ?>" required>
                            </div>

                            <div class="mb-2">
                                <label>Description</label>
                                <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" rows="3"><?= e($pf['description']); ?></textarea>
                            </div>

                            <div class="mb-2">
                                <label>Tech Stack</label>
                                <input type="text" name="tech_stack" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= e($pf['tech_stack']); ?>" placeholder="e.g. PHP, Laravel, PostgreSQL">
                            </div>

                            <div class="mb-2">
                                <label>Repo URL (GitHub)</label>
                                <input type="url" name="repo_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= e($pf['repo_url']); ?>" placeholder="https://github.com/...">
                            </div>

                            <div class="mb-2">
                                <label>Demo URL</label>
                                <input type="url" name="demo_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= e($pf['demo_url']); ?>" placeholder="https://...">
                            </div>

                            <div class="mb-2">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= (int)$pf['sort_order']; ?>">
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 p-4 border-t">
                            <button type="button" onclick="closeModal('editPortfolioModal<?= $pf['id']; ?>')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No portfolio projects yet.</p>
    <?php endif; ?>

    <!-- Add Portfolio Modal -->
    <div id="addPortfolioModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
            <form method="POST">
                <div class="flex justify-between items-center p-4 border-b">
                    <h5 class="text-lg font-semibold">Add Portfolio Project</h5>
                    <button type="button" onclick="closeModal('addPortfolioModal')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <div class="p-4">
                    <input type="hidden" name="action" value="add_portfolio">

                    <div class="mb-2">
                        <label>Title</label>
                        <input type="text" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Project name" required>
                    </div>

                    <div class="mb-2">
                        <label>Description</label>
                        <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" rows="3" placeholder="Short description"></textarea>
                    </div>

                    <div class="mb-2">
                        <label>Tech Stack</label>
                        <input type="text" name="tech_stack" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g. PHP, Laravel, PostgreSQL">
                    </div>

                    <div class="mb-2">
                        <label>Repo URL (GitHub)</label>
                        <input type="url" name="repo_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="https://github.com/...">
                    </div>

                    <div class="mb-2">
                        <label>Demo URL</label>
                        <input type="url" name="demo_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="https://...">
                    </div>

                    <div class="mb-2">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="0">
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t">
                    <button type="button" onclick="closeModal('addPortfolioModal')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Work Experience Modal -->
    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 modal-overlay">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
            <form method="POST">
                <div class="flex justify-between items-center p-4 border-b">
                    <h5 class="text-lg font-semibold">Add Work Experience</h5>
                    <button type="button" onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <div class="p-4">
                    <input type="hidden" name="action" value="add">

                    <input type="text" name="company" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Company" required>
                    <input type="text" name="position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Position" value="Fullstack/Web Systems Engineer – GovTech" required>
                    <input type="date" name="start_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" required>
                    <input type="text" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="City, Country">

                    <div class="mb-2">
                        <label>Status Kerja</label>
                        <select name="status_kerja" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <?php foreach($statuses as $status): ?>
                                <option value="<?= $status ?>"><?= $status ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 mb-2">
                        <input class="h-4 w-4 text-blue-600 border-gray-300 rounded" type="checkbox" name="present" id="presentCheck" onchange="toggleEndAdd(this)">
                        <label class="text-sm" for="presentCheck">Currently Working Here</label>
                    </div>

                    <input type="date" name="end_date" id="endAdd" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2">
                    <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" rows="3" placeholder="Action – Result – Quantify"></textarea>
                    <textarea name="project" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" rows="2" placeholder="Project / Key Achievement"></textarea>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t">
                    <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add</button>
                </div>
            </form>
        </div>
    </div>


</div>


</div>
<style>.modal-open{overflow:hidden}</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openModal(id){document.getElementById(id).classList.remove('hidden');document.body.classList.add('modal-open');}
function closeModal(id){document.getElementById(id).classList.add('hidden');document.body.classList.remove('modal-open');}
document.addEventListener('click',function(e){
    if(e.target.classList.contains('modal-overlay')) {
        e.target.closest('[id^="add"], [id^="edit"]').classList.add('hidden');
        document.body.classList.remove('modal-open');
    }
});
</script>
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
