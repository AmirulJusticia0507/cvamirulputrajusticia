<?php
session_start();
include 'config.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    die("Hanya admin yang bisa mengakses halaman ini.");
}

$error = $success = '';

// =======================
// SIMPAN SETTINGS SESSION
// =======================
if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings'){
    $session_minute = intval($_POST['session_minute']);
    $remember_day = intval($_POST['remember_day']);

    pg_query_params($conn, "INSERT INTO settings (key, value) VALUES ('session_minute',$1) ON CONFLICT (key) DO UPDATE SET value=$1", [$session_minute]);
    pg_query_params($conn, "INSERT INTO settings (key, value) VALUES ('remember_day',$1) ON CONFLICT (key) DO UPDATE SET value=$1", [$remember_day]);
    $success = "Pengaturan berhasil disimpan.";
}

// =======================
// BUAT AKUN BARU
// =======================
if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_account'){
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $role      = $_POST['role'] ?? 'viewer';

    if($username === '' || $email === '' || $password === ''){
        $error = "Semua field wajib diisi.";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Format email tidak valid.";
    }
    elseif($password !== $password2){
        $error = "Password tidak cocok.";
    }
    else {
        $check = pg_query_params($conn, "SELECT 1 FROM users WHERE username=$1 OR email=$2", [$username, $email]);
        if(pg_num_rows($check) > 0){
            $error = "Username atau email sudah terdaftar.";
        }
        else {
            $role_id = ($role === 'admin') ? 1 : 2;
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = pg_query_params(
                $conn,
                "INSERT INTO users (username, email, password_hash, role_id) VALUES ($1,$2,$3,$4)",
                [$username, $email, $hash, $role_id]
            );
            if(!$insert){
                die(pg_last_error($conn));
            }
            $success = "Akun '$username' berhasil dibuat dengan role '$role'.";
        }
    }
}

// =======================
// HAPUS AKUN
// =======================
if(isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])){
    $uid = (int)$_GET['delete_user'];
    $res = pg_query_params($conn, "SELECT role_id FROM users WHERE id=$1", [$uid]);
    $u = pg_fetch_assoc($res);
    if($u && (int)$u['role_id'] !== 1 && $uid !== (int)($_SESSION['user_id'] ?? 0)){
        pg_query_params($conn, "DELETE FROM profile WHERE user_id=$1", [$uid]);
        pg_query_params($conn, "DELETE FROM skills WHERE user_id=$1", [$uid]);
        pg_query_params($conn, "DELETE FROM languages WHERE user_id=$1", [$uid]);
        pg_query_params($conn, "DELETE FROM work_experience WHERE user_id=$1", [$uid]);
        pg_query_params($conn, "DELETE FROM portfolio WHERE user_id=$1", [$uid]);
        pg_query_params($conn, "DELETE FROM cv_history WHERE user_id=$1", [$uid]);
        pg_query_params($conn, "DELETE FROM users WHERE id=$1", [$uid]);
        $success = "Akun berhasil dihapus.";
    } else {
        $error = "Akun admin tidak bisa dihapus.";
    }
    header("Location: settings.php");
    exit;
}

// =======================
// TAMBAH ROLE BARU
// =======================
if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_role'){
    $role_name = trim($_POST['role_name'] ?? '');
    if($role_name === ''){
        $error = "Nama role wajib diisi.";
    }
    else {
        $insert = pg_query_params($conn, "INSERT INTO roles (role_name) VALUES ($1)", [$role_name]);
        if($insert){
            $success = "Role '$role_name' berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan role: " . pg_last_error($conn);
        }
    }
}

// =======================
// HAPUS ROLE
// =======================
if(isset($_GET['delete_role']) && is_numeric($_GET['delete_role'])){
    $rid = (int)$_GET['delete_role'];
    pg_query_params($conn, "DELETE FROM roles WHERE id=$1 AND id NOT IN (1,2)", [$rid]);
    $success = "Role berhasil dihapus.";
    header("Location: settings.php");
    exit;
}

// =======================
// AMBIL DATA
// =======================
$settings = pg_fetch_all(pg_query($conn, "SELECT * FROM settings")) ?: [];
$session_minute = 60;
$remember_day = 7;
foreach($settings as $s){
    if($s['key'] === 'session_minute') $session_minute = $s['value'];
    if($s['key'] === 'remember_day') $remember_day = $s['value'];
}

$roles = pg_fetch_all(pg_query($conn, "SELECT * FROM roles ORDER BY id")) ?: [];
$users = pg_fetch_all(pg_query($conn, "SELECT u.id, u.username, u.email, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id")) ?: [];

// Ambil key admin register
$adminKey = '';
foreach($settings as $s){ if($s['key'] === 'admin_register_key') $adminKey = $s['value']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - CV Management</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body { background:#f9f9f9; color:#333; padding:20px; font-family: 'Inter', sans-serif; }
body.dark { background:#16181d !important; color:#d6dae1 !important; }
body.dark .bg-white { background:#1f232b !important; }
body.dark label { color:#c7cdd6; }
body.dark input, body.dark select, body.dark textarea {
    background:#16181d; color:#e2e6ec; border-color:#3a414c;
}
body.dark table thead { background:#2a2f38 !important; }
body.dark td, body.dark th { color:#c7cdd6; }
</style>
</head>
<body>
<div class="max-w-5xl mx-auto px-4">

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold">⚙️ Settings</h1>
        <p class="text-gray-500 text-sm">Kelola pengaturan, role permission, dan akun pengguna.</p>
    </div>
    <a href="index.php" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition border-2 border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white">← Kembali</a>
</div>

<?php if($error): ?>
<div class="bg-red-100 text-red-800 border border-red-200 p-4 rounded-lg mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if($success): ?>
<div class="bg-green-100 text-green-800 border border-green-200 p-4 rounded-lg mb-4"><?= $success ?></div>
<?php endif; ?>

<!-- Tab Navigation -->
<div class="flex gap-2 mb-5">
    <button onclick="showTab('tabSettings')" id="tabBtnSettings" class="px-4 py-2 rounded-lg font-semibold bg-blue-600 text-white">Pengaturan</button>
    <button onclick="showTab('tabRoles')" id="tabBtnRoles" class="px-4 py-2 rounded-lg font-semibold bg-gray-300 text-gray-700 hover:bg-gray-400">Role & Permission</button>
    <button onclick="showTab('tabAccounts')" id="tabBtnAccounts" class="px-4 py-2 rounded-lg font-semibold bg-gray-300 text-gray-700 hover:bg-gray-400">Buat Akun</button>
</div>

<!-- ================= TAB 1: SETTINGS SESSION ================= -->
<div id="tabSettings" class="tab-content">
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold mb-3">Pengaturan Session & Remember Me</h3>
        <form method="POST">
            <input type="hidden" name="action" value="save_settings">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Session (menit)</label>
                <input type="number" name="session_minute" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= $session_minute ?>" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Durasi "Ingat Saya" (hari)</label>
                <input type="number" name="remember_day" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?= $remember_day ?>" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Admin Register Key (untuk registrasi admin pertama)</label>
                <input type="text" name="admin_key_view" value="<?= htmlspecialchars($adminKey) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" readonly>
                <p class="text-xs text-gray-500 mt-1">Key ini digunakan saat mendaftarkan admin pertama via register.php</p>
            </div>
            <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 w-full">Simpan</button>
        </form>
    </div>
</div>

<!-- ================= TAB 2: ROLE & PERMISSION ================= -->
<div id="tabRoles" class="tab-content hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-3">Daftar Role</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">ID</th>
                        <th class="text-left py-2">Role</th>
                        <th class="text-left py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($roles as $r): ?>
                    <tr class="border-b">
                        <td class="py-2"><?= $r['id'] ?></td>
                        <td class="py-2 font-medium">
                            <?= htmlspecialchars($r['role_name']) ?>
                            <?php if(in_array($r['id'], [1,2])): ?>
                                <span class="text-xs text-gray-400">(default)</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2">
                            <?php if(!in_array($r['id'], [1,2])): ?>
                                <a href="?delete_role=<?= $r['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Hapus role ini?')"><i class="fas fa-trash"></i></a>
                            <?php else: ?>
                                <span class="text-gray-400"><i class="fas fa-lock"></i></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-3">Tambah Role Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_role">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Role</label>
                <input type="text" name="role_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-3" placeholder="contoh: editor" required>
                <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-green-600 text-white hover:bg-green-700 w-full"><i class="fas fa-plus"></i> Tambah Role</button>
            </form>
        </div>
    </div>
</div>

<!-- ================= TAB 3: BUAT AKUN ================= -->
<div id="tabAccounts" class="tab-content hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-3">Daftar Akun</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">ID</th>
                        <th class="text-left py-2">Username</th>
                        <th class="text-left py-2">Email</th>
                        <th class="text-left py-2">Role</th>
                        <th class="text-left py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr class="border-b">
                        <td class="py-2"><?= $u['id'] ?></td>
                        <td class="py-2 font-medium"><?= htmlspecialchars($u['username']) ?></td>
                        <td class="py-2 text-gray-500"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="py-2">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold <?= $u['role_name'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' ?>">
                                <?= htmlspecialchars($u['role_name']) ?>
                            </span>
                        </td>
                        <td class="py-2">
                            <?php if($u['role_name'] !== 'admin'): ?>
                                <a href="?delete_user=<?= $u['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Hapus akun ini?')"><i class="fas fa-trash"></i></a>
                            <?php else: ?>
                                <span class="text-gray-400"><i class="fas fa-shield-alt"></i></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-3">Buat Akun Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_account">
                <input type="text" name="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Username" required>
                <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Email" required>
                <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Password" required>
                <input type="password" name="password2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Konfirmasi Password" required>
                <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-3">
                    <option value="viewer">User / Viewer</option>
                    <option value="admin">Admin</option>
                    <?php foreach($roles as $r): ?>
                        <?php if(!in_array($r['id'], [1,2]) && strtolower($r['role_name']) !== 'viewer'): ?>
                            <option value="<?= htmlspecialchars($r['role_name']) ?>"><?= htmlspecialchars(ucfirst($r['role_name'])) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 w-full"><i class="fas fa-user-plus"></i> Buat Akun</button>
            </form>
        </div>
    </div>
</div>

</div>

<script>
function showTab(tabId){
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById(tabId).classList.remove('hidden');

    const buttons = {
        'tabSettings': 'tabBtnSettings',
        'tabRoles': 'tabBtnRoles',
        'tabAccounts': 'tabBtnAccounts'
    };

    Object.entries(buttons).forEach(([tab, btn]) => {
        const el = document.getElementById(btn);
        if(tab === tabId){
            el.className = 'px-4 py-2 rounded-lg font-semibold bg-blue-600 text-white';
        } else {
            el.className = 'px-4 py-2 rounded-lg font-semibold bg-gray-300 text-gray-700 hover:bg-gray-400';
        }
    });
}
</script>
<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>