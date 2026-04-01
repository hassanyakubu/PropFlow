<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../landlord/maintenance.php");
    exit();
}

$db = new db_connection();
$conn = $db->db_conn();
$request_id = (int) $_POST['request_id'];
$status = mysqli_real_escape_string($conn, $_POST['status']);
$landlord_notes = isset($_POST['landlord_notes']) ? mysqli_real_escape_string($conn, $_POST['landlord_notes']) : '';

// Update maintenance request
$sql = "UPDATE maintenance_requests SET status = '$status', landlord_notes = '$landlord_notes' WHERE request_id = '$request_id'";

if ($db->db_query($sql)) {
    // Get the tenant ID and property ID to send a notification
    $info_sql = "SELECT tenant_id, property_id FROM maintenance_requests WHERE request_id = '$request_id'";
    $info = $db->db_fetch_one($info_sql);

    if ($info) {
        $tenant_id = $info['tenant_id'];
        $prop_id = $info['property_id'];

        $status_label = ucwords(str_replace('_', ' ', $status));
        $message = "Your maintenance request status has been updated to: " . $status_label;
        if (!empty($landlord_notes)) {
            $message .= ". Notes: " . substr($landlord_notes, 0, 50) . "...";
        }

        $notif_sql = "INSERT INTO notifications (user_id, message) VALUES ('$tenant_id', '$message')";
        $db->db_query($notif_sql);
    }

    header("Location: ../landlord/maintenance.php?success=1");
} else {
    header("Location: ../landlord/maintenance.php?error=failed");
}
exit();
