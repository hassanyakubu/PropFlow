<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../landlord/applications.php");
    exit();
}

$db = new db_connection();
$conn = $db->db_conn();

$application_id = (int) $_POST['application_id'];
$action = $_POST['action']; // 'accept' or 'reject'

// Fetch application to get details
$app = $db->db_fetch_one("SELECT * FROM applications WHERE application_id = '$application_id'");

if (!$app) {
    die("Application not found");
}

if ($action === 'accept') {
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);

    // Fetch property details to determine rent amount and frequency
    $property = $db->db_fetch_one("SELECT price, payment_period FROM properties WHERE property_id = '$app[property_id]'");
    if (!$property) {
        die("Property not found");
    }

    $rent_amount = (float) $property['price'];
    $frequency = $property['payment_period'] ?? 'monthly';

    // 1. Create Tenancy
    $sql_tenancy = "INSERT INTO tenancies (tenant_id, property_id, start_date, rent_amount, payment_frequency, status)
                    VALUES ('$app[tenant_id]', '$app[property_id]', '$start_date', '$rent_amount', '$frequency', 'active')";

    if ($db->db_query($sql_tenancy)) {
        // 2. Update Application Status
        $db->db_query("UPDATE applications SET status = 'accepted' WHERE application_id = '$application_id'");

        // 3. Update Property Status
        $db->db_query("UPDATE properties SET status = 'occupied' WHERE property_id = '$app[property_id]'");

        // 4. Notify Tenant
        $msg = "Congratulations! Your application has been accepted. You can now login to pay rent.";
        $db->db_query("INSERT INTO notifications (user_id, message) VALUES ('$app[tenant_id]', '$msg')");

        // 5. Reject other pending applications for this property? Optional, but good practice.
        // For simplicity, we leave them pending or let landlord decide.

        header("Location: ../landlord/applications.php?msg=accepted");
    } else {
        header("Location: ../landlord/applications.php?error=failed");
    }

} elseif ($action === 'reject') {
    // Update Application Status
    $db->db_query("UPDATE applications SET status = 'rejected' WHERE application_id = '$application_id'");

    // Notify Tenant
    $msg = "Update on your application: It has been declined by the landlord.";
    $db->db_query("INSERT INTO notifications (user_id, message) VALUES ('$app[tenant_id]', '$msg')");

    header("Location: ../landlord/applications.php?msg=rejected");
}
exit();
