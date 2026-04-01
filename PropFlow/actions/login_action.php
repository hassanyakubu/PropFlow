<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/login.php");
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

$db = new db_connection();
$user = $db->db_fetch_one("SELECT * FROM users WHERE email = '" . mysqli_real_escape_string($db->db_conn(), $email) . "'");

if ($user && password_verify($password, $user['password_hash'])) {
    if ($user['status'] === 'suspended') {
        header("Location: ../public/login.php?error=suspended");
        exit();
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Redirect based on role
    if ($user['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($user['role'] === 'landlord') {
        header("Location: ../landlord/dashboard.php");
    } else {
        header("Location: ../tenant/dashboard.php");
    }
    exit();
} else {
    header("Location: ../public/login.php?error=invalid");
    exit();
}
