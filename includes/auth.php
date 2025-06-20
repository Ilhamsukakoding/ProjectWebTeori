<?php
// includes/auth.php
// Pastikan session_start() sudah dipanggil di includes/config.php

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}
?>