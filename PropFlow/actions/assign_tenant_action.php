<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../landlord/tenancies.php");
    exit();
}

$db = new db_connection();
$conn = $db->db_conn();

$property_id = (int) $_POST['property_id'];
$tenant_id = (int) $_POST['tenant_id'];
$start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
$rent_amount = (float) $_POST['rent_amount'];
$frequency = mysqli_real_escape_string($conn, $_POST['frequency']);

// Verify ownership of property
$check_sql = "SELECT * FROM properties WHERE property_id = '$property_id' AND owner_id = '" . get_user_id() . "'";
$prop = $db->db_fetch_one($check_sql);

if (!$prop) {
    die("Unauthorized or invalid property");
}

// Insert tenancy
$sql = "INSERT INTO tenancies (tenant_id, property_id, start_date, rent_amount, payment_frequency, status)
        VALUES ('$tenant_id', '$property_id', '$start_date', '$rent_amount', '$frequency', 'active')";

if ($db->db_query($sql)) {
    // Update property status to occupied
    $update_sql = "UPDATE properties SET status = 'occupied' WHERE property_id = '$property_id'";
    $db->db_query($update_sql);

    // Add notification for tenant
    $msg = "You have been assigned to property: " . $prop['title'];
    $notif_sql = "INSERT INTO notifications (user_id, message) VALUES ('$tenant_id', '$msg')";
    $db->db_query($notif_sql);

    header("Location: ../landlord/tenancies.php?success=1");
} else {
    header("Location: ../landlord/tenancies.php?error=failed");
}
exit();
