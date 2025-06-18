<?php
// login.php

// PENTING: Pindahkan session_start() ke includes/config.php
// Pastikan includes/config.php di-require di awal file ini atau di index.php yang memanggil file ini
// Hapus baris session_start() di bawah ini jika sudah ada di config.php
// session_start(); 

require 'includes/config.php'; // Memastikan koneksi $conn tersedia

if (isset($_SESSION['username'])) {
    // Jika sudah login, cek role dan arahkan ke dashboard yang sesuai
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin/dashboard.php"); // Arahkan ke dashboard admin
            exit;
        } elseif ($_SESSION['role'] === 'user') {
            header("Location: user/dashboard.php"); // Arahkan ke dashboard user
            exit;
        }
    }
    // Fallback jika role tidak ditemukan atau tidak sesuai
    header("Location: index.php"); // Atau halaman default lain jika role tidak jelas
    exit;
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

    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 0 40px rgba(0,0,0,0.05);
            background-color: #ffffff;
        }

        .login-header h4 {
            font-weight: 600;
            color: #0d6efd;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .form-control {
            border-radius: 0.5rem;
        }

        .btn-primary {
            border-radius: 0.5rem;
        }

        .card-footer {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .alert {
            font-size: 0.875rem;
        }

        .logo-circle {
            background-color: #0d6efd;
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
    </style>
</head>
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

<?php
// Logic POST request ada di sini, di bagian bawah file
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Menggunakan prepared statement untuk menghindari SQL Injection
    $query_user = "SELECT id, username, password, nama, role FROM user WHERE username = ?";
    $stmt_user = mysqli_prepare($conn, $query_user);
    
    if ($stmt_user === false) {
        header("Location: login.php?error=Terjadi kesalahan sistem. (User Query)");
        exit;
    }

    mysqli_stmt_bind_param($stmt_user, 's', $username);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    $user = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_user);

    if ($user && $password === $user['password']) { // Ingat: idealnya gunakan password_verify() untuk hashed password
        // Login berhasil dari tabel 'user'
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // =====================================================================
        // BAGIAN PENTING: Ambil ID karyawan yang benar dari tabel 'karyawan'
        // Ini DIBAGI berdasarkan ROLE user
        // =====================================================================
        if ($user['role'] === 'user') { // Untuk role 'user', ambil id dari tabel karyawan
            $query_karyawan = "SELECT id FROM karyawan WHERE nama = ?";
            $stmt_karyawan = mysqli_prepare($conn, $query_karyawan);

            if ($stmt_karyawan === false) {
                session_destroy(); // Destroy sesi jika ada kesalahan kritis
                header("Location: login.php?error=Terjadi kesalahan sistem saat query karyawan.");
                exit;
            }

            mysqli_stmt_bind_param($stmt_karyawan, 's', $user['nama']); // Gunakan 'nama' dari tabel user
            mysqli_stmt_execute($stmt_karyawan);
            $result_karyawan = mysqli_stmt_get_result($stmt_karyawan);
            $karyawan_data = mysqli_fetch_assoc($result_karyawan);
            mysqli_stmt_close($stmt_karyawan);

            if ($karyawan_data) {
                $_SESSION['user_id'] = $karyawan_data['id']; // Ini adalah ID dari tabel 'karyawan' (misal: 4)
                $_SESSION['nama_lengkap'] = $user['nama']; // Simpan nama lengkap untuk tampilan
            } else {
                // Jika data karyawan tidak ditemukan di tabel karyawan berdasarkan nama
                session_destroy(); // Hancurkan sesi karena data inkonsisten
                header("Location: login.php?error=Data karyawan tidak lengkap. Hubungi Admin.");
                exit;
            }
        } else { // Untuk role 'admin' (dan role lain yang tidak spesifik 'user')
            $_SESSION['user_id'] = $user['id']; // ID dari tabel 'user' (misal: 1 untuk admin)
            $_SESSION['nama_lengkap'] = $user['nama']; // Nama admin dari tabel 'user'
        }

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