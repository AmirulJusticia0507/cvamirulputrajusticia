<?php
include 'config.php';

/* =========================
   Helper: ambil setting
========================= */
function getSetting($conn, $key, $default){
    $res = pg_query_params($conn, "SELECT value FROM settings WHERE key=$1", [$key]);
    if($row = pg_fetch_assoc($res)){
        return $row['value'];
    }
    return $default;
}

/* =========================
   Load settings
========================= */
$sessionMinute = (int) getSetting($conn, 'session_minute', 60);
$rememberDay   = (int) getSetting($conn, 'remember_day', 7);

/* =========================
   Session config (WAJIB SEBELUM session_start)
========================= */
ini_set('session.gc_maxlifetime', $sessionMinute * 60);

session_set_cookie_params([
    'lifetime' => $sessionMinute * 60,
    'path'     => '/',
    'httponly' => true,
    'secure'   => !empty($_SERVER['HTTPS']),
    'samesite' => 'Lax'
]);

session_start(); // ✅ BARU DI SINI


/* =========================
   Auto login via cookie
========================= */
if(!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'], $_COOKIE['remember_token'])){
    $res = pg_query_params(
        $conn,
        "SELECT u.*, r.role_name 
         FROM users u 
         JOIN roles r ON u.role_id=r.id
         WHERE u.id=$1 AND u.remember_token=$2 AND u.is_locked=false",
        [$_COOKIE['remember_user'], $_COOKIE['remember_token']]
    );

    if($user = pg_fetch_assoc($res)){
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role_name'];
        header("Location: index.php");
        exit;
    }
}

/* =========================
   Handle Login
========================= */
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $res = pg_query_params(
        $conn,
        "SELECT u.*, r.role_name 
         FROM users u 
         JOIN roles r ON u.role_id=r.id 
         WHERE username=$1",
        [$username]
    );

    if($user = pg_fetch_assoc($res)){
        if($user['is_locked']){
            $error = "Akun dikunci, hubungi admin";
        } elseif(password_verify($password, $user['password_hash'])){
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role_name'];

            pg_query_params($conn, "UPDATE users SET failed_login=0 WHERE id=$1", [$user['id']]);

            /* Remember Me */
            if(!empty($_POST['remember'])){
                $token = bin2hex(random_bytes(32));
                $expire = time() + ($rememberDay * 86400);

                setcookie("remember_user", $user['id'], [
                    'expires'  => $expire,
                    'path'     => '/',
                    'httponly' => true,
                    'secure'   => isset($_SERVER['HTTPS']),
                    'samesite' => 'Lax'
                ]);

                setcookie("remember_token", $token, [
                    'expires'  => $expire,
                    'path'     => '/',
                    'httponly' => true,
                    'secure'   => isset($_SERVER['HTTPS']),
                    'samesite' => 'Lax'
                ]);

                pg_query_params(
                    $conn,
                    "UPDATE users SET remember_token=$1 WHERE id=$2",
                    [$token, $user['id']]
                );
            }

            header("Location: index.php");
            exit;
        } else {
            $failed = $user['failed_login'] + 1;
            $lock   = $failed >= 5;

            pg_query_params(
                $conn,
                "UPDATE users SET failed_login=$1, is_locked=$2 WHERE id=$3",
                [$failed, $lock, $user['id']]
            );

            $error = "Password salah. Percobaan ke-$failed";
        }
    } else {
        $error = "Username tidak ditemukan";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-4">
                    <h3 class="text-center mb-3">Login Sistem</h3>

                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                Ingat saya (<?= $rememberDay ?> hari)
                            </label>
                        </div>

                        <button class="btn btn-primary w-100">Login</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="register.php">Register</a> •
                        <a href="forgot_password.php">Lupa Password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
