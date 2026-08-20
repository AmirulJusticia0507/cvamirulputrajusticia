<?php
session_start();
include 'config.php';

// =======================
// AUTH CHECK
// =======================
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// =======================
// HANDLE SAVE NEW CV
// =======================
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_new_cv') {
    $user_id    = (int) $_SESSION['user_id'];
    $full_name  = trim($_POST['full_name'] ?? '');
    $headline   = trim($_POST['headline'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $linkedin   = trim($_POST['linkedin'] ?? '');
    $summary    = trim($_POST['summary'] ?? '');

    if ($full_name === '' || $email === '') {
        $error = "Nama dan email wajib diisi.";
    } else {
        // Buat snapshot kosong dari form
        $snapshot = [
            'profile' => [
                'full_name' => $full_name,
                'headline'  => $headline,
                'email'     => $email,
                'phone'     => $phone,
                'linkedin'  => $linkedin,
                'summary'   => $summary,
            ],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $result = pg_query_params(
            $conn,
            "INSERT INTO cv_history (work_snapshot, user_id) VALUES ($1, $2) RETURNING id",
            [json_encode($snapshot), $user_id]
        );
        $row = pg_fetch_assoc($result);
        $id = $row['id'];

        $url = "preview_cv.php?user_id=$user_id";
        pg_query_params($conn, "UPDATE cv_history SET url=$1 WHERE id=$2", [$url, $id]);

        // Simpan juga ke tabel profile agar bisa diedit di index.php
        $chk = pg_query_params($conn, "SELECT id FROM profile WHERE user_id=$1", [$user_id]);
        if (pg_num_rows($chk) > 0) {
            pg_query_params($conn,
                "UPDATE profile SET full_name=$1, headline=$2, email=$3, phone=$4, linkedin=$5, summary=$6, updated_at=NOW() WHERE user_id=$7",
                [$full_name, $headline, $email, $phone, $linkedin, $summary, $user_id]
            );
        } else {
            pg_query_params($conn,
                "INSERT INTO profile (full_name, headline, email, phone, linkedin, summary, user_id, updated_at) VALUES ($1,$2,$3,$4,$5,$6,$7,NOW())",
                [$full_name, $headline, $email, $phone, $linkedin, $summary, $user_id]
            );
        }

        $success = "CV baru berhasil dibuat! <a href='$url' class='underline'>Lihat Preview</a>";
    }
}

$default = [
    'full_name' => '',
    'headline'  => '',
    'email'     => '',
    'phone'     => '',
    'linkedin'  => '',
    'summary'   => '',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create CV Baru</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { font-family: 'Inter', sans-serif; background: #f9f9f9; color: #333; padding: 20px; }
body.dark { background:#16181d !important; color:#d6dae1 !important; }
body.dark .bg-white { background:#1f232b !important; }
body.dark label { color:#c7cdd6; }
body.dark input, body.dark textarea, body.dark select {
    background:#16181d; color:#e2e6ec; border-color:#3a414c;
}
</style>
</head>
<body>
<div class="max-w-3xl mx-auto px-4">

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold">📄 Create CV Baru</h1>
        <p class="text-gray-500 text-sm">Mulai dari CV kosong - isi data profil untuk membuat CV baru.</p>
    </div>
    <a href="index.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white">← Kembali</a>
</div>

<?php if($success): ?>
<div class="bg-green-100 text-green-800 border border-green-200 p-4 rounded-lg mb-4"><?= $success ?></div>
<?php endif; ?>
<?php if($error): ?>
<div class="bg-red-100 text-red-800 border border-red-200 p-4 rounded-lg mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Fresh empty CV form -->
<div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-lg font-semibold border-b-2 border-blue-600 pb-2 mb-4">Profil / Data Diri</h2>

    <form method="POST">
        <input type="hidden" name="action" value="save_new_cv">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($default['full_name']) ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Nama Lengkap" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Headline</label>
                <input type="text" name="headline" value="<?= htmlspecialchars($default['headline']) ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Fullstack/Web Systems Engineer">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="<?= htmlspecialchars($default['email']) ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="email@contoh.com" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone (WhatsApp)</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($default['phone']) ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="+62-xxx-xxxx-xxxx">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                <input type="text" name="linkedin" value="<?= htmlspecialchars($default['linkedin']) ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="https://linkedin.com/in/...">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Summary / Ringkasan</label>
                <textarea name="summary" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ringkasan profesional Anda..."></textarea>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-700"><i class="fas fa-info-circle"></i> Setelah menyimpan profil, Anda bisa mengisi Work Experience, Skills, dan Portfolio di halaman utama (index.php).</p>
        </div>

        <div class="flex justify-end gap-2">
            <a href="index.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white">Cancel</a>
            <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-green-600 text-white hover:bg-green-700">
                <i class="fas fa-save"></i> Simpan CV Baru
            </button>
        </div>
    </form>
</div>

</div>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>