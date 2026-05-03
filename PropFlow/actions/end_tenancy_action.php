<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_logged_in()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/index.php");
    exit();
}

$db = new db_connection();
$conn = $db->db_conn();

$tenancy_id = (int) $_POST['tenancy_id'];
$user_id = get_user_id();
$user_role = $_SESSION['user_role'] ?? 'tenant';

// Get tenancy and property details
$sql = "SELECT t.*, p.owner_id, p.title 
        FROM tenancies t 
        JOIN properties p ON t.property_id = p.property_id 
        WHERE t.tenancy_id = '$tenancy_id' AND t.status = 'active'";

$tenancy = $db->db_fetch_one($sql);

if (!$tenancy) {
    die("Active tenancy not found.");
}

// Permission check
if ($user_role !== 'admin' && $tenancy['owner_id'] != $user_id) {
    die("Unauthorized access to end tenancy.");
}

// 1. Update tenancies status
$db->db_query("UPDATE tenancies SET status = 'ended' WHERE tenancy_id = '$tenancy_id'");

// 2. Update property status
$db->db_query("UPDATE properties SET status = 'available' WHERE property_id = '{$tenancy['property_id']}'");

// 3. Notify tenant
$property_title = mysqli_real_escape_string($conn, $tenancy['title']);
$msg = "Your tenancy at " . $property_title . " has been ended.";
if ($user_role === 'admin') {
    $msg .= " This action was taken by the platform administration.";
} else {
    $msg .= " This action was taken by the landlord.";
}
$db->db_query("INSERT INTO notifications (user_id, message) VALUES ('{$tenancy['tenant_id']}', '$msg')");

// Redirect back to referring page
if ($user_role === 'admin') {
    header("Location: ../admin/tenancies.php?msg=tenancy_ended");
} else {
    header("Location: ../landlord/tenancies.php?msg=tenancy_ended");
}
exit();
