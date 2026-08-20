<?php
session_start();
include 'config.php';

// =======================
// AUTH: hanya admin
// =======================
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
if(($_SESSION['role'] ?? 'viewer') !== 'admin'){
    die('Akses ditolak - hanya admin.');
}

function e($str){ return htmlspecialchars($str ?? ''); }

// Hapus user (non-admin saja, bukan diri sendiri)
if(isset($_GET['delete_user'])){
    $del_id = (int) $_GET['delete_user'];
    if($del_id !== (int) $_SESSION['user_id']){
        // cek role target bukan admin
        $chk = pg_query_params($conn, "SELECT role_id FROM users WHERE id=$1", [$del_id]);
        $u = pg_fetch_assoc($chk);
        if($u && (int)$u['role_id'] !== 1){
            pg_query_params($conn, "DELETE FROM users WHERE id=$1", [$del_id]);
            pg_query_params($conn, "DELETE FROM profile WHERE user_id=$1", [$del_id]);
            pg_query_params($conn, "DELETE FROM skills WHERE user_id=$1", [$del_id]);
            pg_query_params($conn, "DELETE FROM languages WHERE user_id=$1", [$del_id]);
            pg_query_params($conn, "DELETE FROM work_experience WHERE user_id=$1", [$del_id]);
            pg_query_params($conn, "DELETE FROM portfolio WHERE user_id=$1", [$del_id]);
            pg_query_params($conn, "DELETE FROM cv_history WHERE user_id=$1", [$del_id]);
        }
    }
    header("Location: admin_users.php");
    exit;
}

// Ambil semua user + ringkasan CV
$sql = "
SELECT u.id, u.username, u.email,
       r.role_name,
       p.full_name, p.headline, p.photo,
       (SELECT count(*) FROM work_experience w WHERE w.user_id=u.id) AS total_exp,
       (SELECT count(*) FROM skills s WHERE s.user_id=u.id) AS total_skills,
       (SELECT count(*) FROM portfolio pf WHERE pf.user_id=u.id) AS total_portfolio,
       (SELECT count(*) FROM languages l WHERE l.user_id=u.id) AS total_langs,
       (SELECT count(*) FROM cv_history h WHERE h.user_id=u.id) AS total_history
FROM users u
LEFT JOIN roles r ON u.role_id=r.id
LEFT JOIN profile p ON p.user_id=u.id
ORDER BY u.id ASC
";
$users = pg_fetch_all(pg_query($conn, $sql)) ?: [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data User - Admin</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { font-family: 'Inter', sans-serif; background: #f9f9f9; color: #333; padding: 20px; }
body.dark { background:#16181d !important; color:#d6dae1 !important; }
body.dark .bg-white { background:#1f232b !important; }
body.dark h1, body.dark h2, body.dark h3 { color:#e2e6ec; }
body.dark .text-gray-700 { color:#c7cdd6 !important; }
</style>
</head>
<body>
<div class="max-w-6xl mx-auto px-4">

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold"><i class="fas fa-users text-purple-600"></i> Data User</h1>
        <p class="text-gray-500 text-sm">Kelola semua akun pengguna sistem CV. Klik "Lihat CV" untuk melihat detail data CV user.</p>
    </div>
    <div class="flex gap-2">
        <a href="index.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white">← Index</a>
        <a href="settings.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white">⚙️ Settings</a>
    </div>
</div>

<?php if(count($users) === 0): ?>
<div class="bg-gray-100 border border-gray-200 p-6 rounded-lg text-center text-gray-500">
    Belum ada user terdaftar.
</div>
<?php else: ?>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 text-gray-700">
            <tr class="text-left">
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Role</th>
                <th class="px-4 py-3 text-center">Pengalaman</th>
                <th class="px-4 py-3 text-center">Skills</th>
                <th class="px-4 py-3 text-center">Bahasa</th>
                <th class="px-4 py-3 text-center">Portfolio</th>
                <th class="px-4 py-3 text-center">Riwayat CV</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($users as $u): ?>
            <?php $is_self = (int)$u['id'] === (int)$_SESSION['user_id']; ?>
            <?php $is_admin = $u['role_name'] === 'admin'; ?>
            <tr class="border-t border-gray-200 hover:bg-gray-50">
                <td class="px-4 py-3">#<?= (int)$u['id'] ?></td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img src="<?= e($u['photo'] ?? 'uploads/profile/default.jpg') ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200" alt="">
                        <div>
                            <div class="font-semibold"><?= e($u['full_name'] ?: $u['username']) ?>
                                <?php if($is_self): ?><span class="ml-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Anda</span><?php endif; ?>
                                <?php if($is_admin): ?><span class="ml-1 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full"><i class="fas fa-crown"></i> Admin</span><?php endif; ?>
                            </div>
                            <div class="text-gray-500 text-xs">@<?= e($u['username']) ?> • <?= e($u['email']) ?></div>
                            <div class="text-gray-400 text-xs"><?= e($u['headline']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3"><?= e($u['role_name']) ?></td>
                <td class="px-4 py-3 text-center"><?= (int)$u['total_exp'] ?></td>
                <td class="px-4 py-3 text-center"><?= (int)$u['total_skills'] ?></td>
                <td class="px-4 py-3 text-center"><?= (int)$u['total_langs'] ?></td>
                <td class="px-4 py-3 text-center"><?= (int)$u['total_portfolio'] ?></td>
                <td class="px-4 py-3 text-center"><?= (int)$u['total_history'] ?></td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="index.php?user_id=<?= (int)$u['id'] ?>" class="inline-block px-3 py-1 rounded-lg text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 transition"><i class="fas fa-eye"></i> Lihat CV</a>
                    <a href="preview_cv.php?user_id=<?= (int)$u['id'] ?>" class="inline-block px-3 py-1 rounded-lg text-xs font-semibold bg-cyan-600 text-white hover:bg-cyan-700 transition"><i class="fas fa-file"></i> Preview</a>
                    <?php if(!$is_admin && !$is_self): ?>
                    <a href="?delete_user=<?= (int)$u['id'] ?>" onclick="return confirm('Hapus user <?= e($u['username']) ?> beserta seluruh data CV-nya?')" class="inline-block px-3 py-1 rounded-lg text-xs font-semibold bg-red-600 text-white hover:bg-red-700 transition"><i class="fas fa-trash"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 text-sm text-gray-500">
    <i class="fas fa-info-circle text-blue-500"></i> Total user: <strong><?= count($users) ?></strong>. Akun admin tidak bisa dihapus. Anda tidak bisa menghapus akun sendiri.
</div>

<?php endif; ?>

</div>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>