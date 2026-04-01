<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/register.php");
    exit();
}

$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$role = $_POST['role'];
$password = $_POST['password'];

// Validate Password Strength
// More than 6 characters (7+), at least one number, at least one special character
if (strlen($password) <= 6 || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
    header("Location: ../public/register.php?error=weak_password");
    exit();
}

// Validate role
if (!in_array($role, ['landlord', 'tenant'])) {
    header("Location: ../public/register.php?error=invalid");
    exit();
}

$db = new db_connection();

// Check if email exists
$existing = $db->db_fetch_one("SELECT user_id FROM users WHERE email = '" . mysqli_real_escape_string($db->db_conn(), $email) . "'");

if ($existing) {
    header("Location: ../public/register.php?error=exists");
    exit();
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$sql = "INSERT INTO users (full_name, email, phone, password_hash, role, status) 
        VALUES (
            '" . mysqli_real_escape_string($db->db_conn(), $full_name) . "',
            '" . mysqli_real_escape_string($db->db_conn(), $email) . "',
            '" . mysqli_real_escape_string($db->db_conn(), $phone) . "',
            '" . mysqli_real_escape_string($db->db_conn(), $password_hash) . "',
            '" . mysqli_real_escape_string($db->db_conn(), $role) . "',
            'active'
        )";

if ($db->db_query($sql)) {
    // Login the user automatically
    $user_id = $db->last_insert_id();

    // Ensure session is started before setting variables (in case core.php didn't catch it correctly)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_name'] = $full_name; // Core uses 'user_name'
    $_SESSION['user_email'] = $email;

    // Redirect to specific dashboard based on role
    if ($role === 'landlord') {
        header("Location: ../landlord/dashboard.php");
    } else {
        header("Location: ../tenant/dashboard.php");
    }
} else {
    header("Location: ../public/register.php?error=failed");
}
exit();
