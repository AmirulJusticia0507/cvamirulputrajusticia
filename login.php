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
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">

<div class="max-w-lg mx-auto px-4 mt-5">
    <div class="flex justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-center mb-3">Login Sistem</h3>

                    <?php if(isset($error)): ?>
                        <div class="bg-red-100 text-red-800 border border-red-200 p-4 rounded-lg"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" name="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                        </div>

                        <div class="flex items-center mb-3">
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded" type="checkbox" name="remember" id="remember">
                            <label class="ml-2 text-sm text-gray-700" for="remember">
                                Ingat saya (<?= $rememberDay ?> hari)
                            </label>
                        </div>

                        <button class="inline-block px-4 py-2 rounded-lg font-semibold text-center transition bg-blue-600 text-white hover:bg-blue-700 w-full">Login</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="register.php">Register</a> •
                        <a href="forgot_password.php">Lupa Password?</a>
                    </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/darkmode.php'; ?>
</body>
</html>
