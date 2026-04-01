<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/login.php");
    exit();
}

if (!is_logged_in() || $_SESSION['user_role'] !== 'landlord') {
    header("Location: ../public/login.php?error=required");
    exit();
}

$user_id = $_SESSION['user_id'];
$tenancy_id = isset($_POST['tenancy_id']) ? intval($_POST['tenancy_id']) : 0;

if ($tenancy_id > 0) {
    $db = new db_connection();

    // Verify landlord owns this tenancy
    $query = "SELECT t.tenant_id, p.title 
              FROM tenancies t 
              JOIN properties p ON t.property_id = p.property_id 
              WHERE t.tenancy_id = $tenancy_id AND p.owner_id = $user_id AND t.status = 'active'";

    $tenancy = $db->db_fetch_one($query);

    if ($tenancy) {
        $tenant_id = $tenancy['tenant_id'];
        $property_title = mysqli_real_escape_string($db->db_conn(), $tenancy['title']);

        $message = "REMINDER: Your rent payment for '$property_title' is due or upcoming. Please ensure your payments are up to date.";

        $insert_query = "INSERT INTO notifications (user_id, message) VALUES ($tenant_id, '$message')";
        if ($db->db_query($insert_query)) {
            header("Location: ../landlord/tenancies.php?msg=reminder_sent");
            exit();
        } else {
            header("Location: ../landlord/tenancies.php?error=db_error");
            exit();
        }
    } else {
        header("Location: ../landlord/tenancies.php?error=invalid_tenancy");
        exit();
    }
} else {
    header("Location: ../landlord/tenancies.php?error=missing_id");
    exit();
}
?>