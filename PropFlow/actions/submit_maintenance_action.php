<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../tenant/maintenance_request.php");
    exit();
}

$db = new db_connection();
$conn = $db->db_conn();
$user_id = get_user_id();

$property_id = (int) $_POST['property_id'];
$category = mysqli_real_escape_string($conn, $_POST['category']);
$priority = mysqli_real_escape_string($conn, $_POST['priority']);
$description = mysqli_real_escape_string($conn, $_POST['description']);

// Insert request
$sql = "INSERT INTO maintenance_requests (tenant_id, property_id, issue_category, description, priority, status)
        VALUES ('$user_id', '$property_id', '$category', '$description', '$priority', 'pending')";

if ($db->db_query($sql)) {
    // Notify Landlord
    $property = $db->db_fetch_one("SELECT owner_id, title FROM properties WHERE property_id = '$property_id'");
    if ($property) {
        $landlord_msg = "New maintenance request ({$category}) submitted for {$property['title']}.";
        $db->db_query("INSERT INTO notifications (user_id, message) VALUES ('{$property['owner_id']}', '" . mysqli_real_escape_string($conn, $landlord_msg) . "')");
    }

    header("Location: ../tenant/maintenance_request.php?success=1");
} else {
    header("Location: ../tenant/maintenance_request.php?error=failed");
}
exit();
