<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: index.php"); // redirect ke index.php supaya diarahkan ke dashboard sesuai role
    exit;
}

require 'includes/config.php';  // gunakan require agar error kalau gagal load

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Absensi Pekerjaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h4>Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                    <div class="card-footer text-center small text-muted">
                        © <?= date("Y") ?> Absensi Pekerjaan
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM user WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    // Ganti cek password sesuai hash atau plain text
    // Contoh kalau kamu pakai password_hash():
    // if ($user && password_verify($password, $user['password'])) {
    
    // Kalau password masih plain text, ini pengecekan sederhana:
    if ($user && $password === $user['password']) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];      // simpan role
        $_SESSION['user_id'] = $user['id'];

        header("Location: index.php");  // redirect ke index.php supaya diarahkan sesuai role
        exit;
    } else {
        header("Location: login.php?error=Username atau password salah!");
        exit;
    }
}
?>
