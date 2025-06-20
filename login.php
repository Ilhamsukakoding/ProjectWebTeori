<?php
require 'includes/config.php'; 

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Cek apakah user sudah login. Jika sudah, arahkan ke dashboard yang sesuai
if (isset($_SESSION['username'])) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin/dashboard.php");
            exit;
        } elseif ($_SESSION['role'] === 'user') {
            header("Location: user/dashboard.php");
            exit;
        }
    }
    // Fallback jika role tidak ditemukan atau tidak sesuai, mungkin ke dashboard default atau logout
    header("Location: index.php"); 
    exit;
}

// Logika pemrosesan form login setelah POST request
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? ''); 
    $password = trim($_POST['password'] ?? ''); 

    $query_user = "SELECT id, username, password, nama, jabatan, email, role FROM user WHERE username = ?";
    $stmt_user = mysqli_prepare($conn, $query_user);
    
    if ($stmt_user === false) {
        header("Location: login.php?error=Terjadi kesalahan sistem. (Query user gagal)");
        exit;
    }

    mysqli_stmt_bind_param($stmt_user, 's', $username);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    $user = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_user);

    // Verifikasi password 
    if ($user && $password === $user['password']) { 
        // Login berhasil
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['nama_lengkap'] = $user['nama'];
        $_SESSION['jabatan'] = $user['jabatan']; 
        $_SESSION['email'] = $user['email'];     

        // Redirect sesuai role
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin/dashboard.php");
            exit;
        } else { // Termasuk role 'user'
            header("Location: user/dashboard.php");
            exit;
        }
    } else {
        // Username atau password salah
        header("Location: login.php?error=Username atau password salah!");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Absensi Pekerjaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet"> </head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card p-4">
                <div class="card-body">
                    <div class="text-center login-header mb-4">
                        <div class="logo-circle mx-auto mb-2">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                        <h4>Login</h4>
                        <p class="text-muted small">Silakan masuk untuk melanjutkan</p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error']) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required autofocus placeholder="Masukkan username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Masuk</button>
                    </form>
                </div>
                <div class="card-footer text-center mt-3">
                    © <?= date("Y") ?> Absensi Pekerjaan
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>