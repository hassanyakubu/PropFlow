<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/index.php");
    exit();
}

$db = new db_connection();
$conn = $db->db_conn();
$tenant_id = get_user_id();
$property_id = (int) $_POST['property_id'];
$message = mysqli_real_escape_string($conn, $_POST['message']);

// Check if already applied
$check_sql = "SELECT application_id FROM applications WHERE tenant_id = '$tenant_id' AND property_id = '$property_id' AND status != 'rejected'";
$check = $db->db_fetch_one($check_sql);

if ($check) {
    header("Location: ../public/property.php?id=$property_id&error=already_applied");
    exit();
}

$sql = "INSERT INTO applications (tenant_id, property_id, message, status) 
        VALUES ('$tenant_id', '$property_id', '$message', 'pending')";

if ($db->db_query($sql)) {
    // Notify landlord (Internal notification)
    // First get owner_id
    $prop = $db->db_fetch_one("SELECT owner_id, title FROM properties WHERE property_id = '$property_id'");
    if ($prop) {
        $owner_id = $prop['owner_id'];
        $notif_msg = "New application for property: " . $prop['title'];
        $db->db_query("INSERT INTO notifications (user_id, message, is_read) VALUES ('$owner_id', '$notif_msg', 0)");
    }

    header("Location: ../public/property.php?id=$property_id&success=applied");
} else {
    header("Location: ../public/property.php?id=$property_id&error=failed");
}
exit();
