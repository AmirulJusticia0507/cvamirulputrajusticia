<?php
include 'config.php';

$error = '';
$success = '';

/* =========================
   Cek apakah admin sudah ada
========================= */
$resAdmin = pg_query($conn, "SELECT 1 FROM users WHERE role_id = 1 LIMIT 1");
$noAdminYet = pg_num_rows($resAdmin) === 0;

/* =========================
   Ambil admin register key
========================= */
$adminKey = null;
if ($noAdminYet) {
    $resKey = pg_query(
        $conn,
        "SELECT value FROM settings WHERE key='admin_register_key'"
    );
    if ($row = pg_fetch_assoc($resKey)) {
        $adminKey = $row['value'];
    }
}

/* =========================
   Handle POST
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $role      = $_POST['role'] ?? 'viewer';
    $inputKey  = $_POST['admin_key'] ?? '';

    /* ========= VALIDASI ========= */
    if ($username === '' || $email === '' || $password === '') {
        $error = "Semua field wajib diisi.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    }
    elseif ($password !== $password2) {
        $error = "Password tidak cocok.";
    }
    else {
        /* ========= CEK DUPLIKAT ========= */
        $check = pg_query_params(
            $conn,
            "SELECT 1 FROM users WHERE username=$1 OR email=$2",
            [$username, $email]
        );

        if (pg_num_rows($check) > 0) {
            $error = "Username atau email sudah terdaftar.";
        }
        else {

            /* ========= ROLE HANDLING ========= */
            if ($role === 'admin') {

                if (!$noAdminYet) {
                    $error = "Registrasi admin sudah ditutup.";
                }
                elseif (!$adminKey || $inputKey !== $adminKey) {
                    $error = "Admin Register Key tidak valid.";
                }
                else {
                    $role_id = 1; // admin
                }

            } else {
                $role_id = 2; // viewer
            }

            /* ========= INSERT USER ========= */
            if (!$error) {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $insert = pg_query_params(
                    $conn,
                    "INSERT INTO users (username, email, password_hash, role_id)
                     VALUES ($1,$2,$3,$4)",
                    [$username, $email, $hash, $role_id]
                );

                if (!$insert) {
                    die(pg_last_error($conn));
                }

                /* ========= MATIKAN ADMIN KEY ========= */
                if ($role_id === 1) {
                    pg_query(
                        $conn,
                        "DELETE FROM settings WHERE key='admin_register_key'"
                    );
                }

                $success = "Registrasi berhasil. <a href='login.php'>Login</a>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<title>Register</title>
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script>
function toggleAdminKey(v){
    document.getElementById('adminKey').style.display =
        v === 'admin' ? 'block' : 'none';
}
</script>

</head>
<body class="bg-gray-100">
<div class="max-w-lg mx-auto px-4 mt-5">
<div class="flex justify-center">
<div class="w-full max-w-md">
<div class="bg-white rounded-xl shadow-lg p-6">

<h3 class="text-center mb-3">Register</h3>

<?php if($error): ?>
<div class="bg-red-100 text-red-800 border border-red-200 p-4 rounded-lg"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="bg-green-100 text-green-800 border border-green-200 p-4 rounded-lg"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <input name="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Username" required>
    <input name="email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Email" required>
    <input name="password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Password" required>
    <input name="password2" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Konfirmasi Password" required>

    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" onchange="toggleAdminKey(this.value)">
        <option value="viewer">User</option>
        <?php if ($noAdminYet): ?>
            <option value="admin">Admin</option>
        <?php endif; ?>
    </select>

    <div id="adminKey" style="display:none">
        <input name="admin_key" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none mb-2" placeholder="Admin Register Key">
    </div>

    <button class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 w-full">Register</button>
</form>


</div>
</div>
</div>
</div>
</body>
</html>
