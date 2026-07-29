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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
function toggleAdminKey(v){
    document.getElementById('adminKey').style.display =
        v === 'admin' ? 'block' : 'none';
}
</script>

</head>
<body class="bg-light">
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card shadow-lg">
<div class="card-body p-4">

<h3 class="text-center mb-3">Register</h3>

<?php if($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <input name="username" class="form-control mb-2" placeholder="Username" required>
    <input name="email" type="email" class="form-control mb-2" placeholder="Email" required>
    <input name="password" type="password" class="form-control mb-2" placeholder="Password" required>
    <input name="password2" type="password" class="form-control mb-2" placeholder="Konfirmasi Password" required>

    <select name="role" class="form-select mb-2" onchange="toggleAdminKey(this.value)">
        <option value="viewer">User</option>
        <?php if ($noAdminYet): ?>
            <option value="admin">Admin</option>
        <?php endif; ?>
    </select>

    <div id="adminKey" style="display:none">
        <input name="admin_key" class="form-control mb-2" placeholder="Admin Register Key">
    </div>

    <button class="btn btn-primary w-100">Register</button>
</form>


</div>
</div>
</div>
</div>
</div>
</body>
</html>
