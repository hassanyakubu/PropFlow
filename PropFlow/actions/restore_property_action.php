<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Check login and role
if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../landlord/history.php");
    exit();
}

$property_id = (int) $_GET['id'];
$owner_id = get_user_id();
$db = new db_connection();

// Verify ownership
$check = $db->db_fetch_one("SELECT property_id FROM properties WHERE property_id = '$property_id' AND owner_id = '$owner_id'");
if (!$check) {
    die("Unauthorized access");
}

// Restore property
$sql = "UPDATE properties SET status = 'available' WHERE property_id = '$property_id' AND owner_id = '$owner_id'";

if ($db->db_query($sql)) {
    header("Location: ../landlord/history.php?msg=restored");
} else {
    header("Location: ../landlord/history.php?error=restore_failed");
}
exit();
