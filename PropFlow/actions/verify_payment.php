<?php
require_once '../settings/core.php';
require_once '../settings/paystack_config.php';
require_once '../settings/db_class.php';

$reference = isset($_GET['reference']) ? $_GET['reference'] : '';

if (!$reference) {
    die("No reference supplied");
}

$response = paystack_verify_transaction($reference);

if ($response && $response['status'] && $response['data']['status'] === 'success') {
    $db = new db_connection();

    // Update payment status
    $sql = "UPDATE payments SET payment_status = 'paid' WHERE transaction_reference = '$reference'";
    $db->db_query($sql);

    // Fetch payment and tenancy details for notifications
    $payment_info = $db->db_fetch_one("SELECT p.*, t.tenant_id, pr.owner_id, pr.title as property_title 
        FROM payments p 
        JOIN tenancies t ON p.tenancy_id = t.tenancy_id 
        JOIN properties pr ON t.property_id = pr.property_id 
        WHERE p.transaction_reference = '$reference'");

    if ($payment_info) {
        $amount_formatted = number_format($payment_info['amount'], 2);

        // Notify Tenant
        $tenant_msg = "Your payment of GH₵ {$amount_formatted} for {$payment_info['property_title']} was successful. Thank you!";
        $db->db_query("INSERT INTO notifications (user_id, message) VALUES ('{$payment_info['tenant_id']}', '" . mysqli_real_escape_string($db->db_conn(), $tenant_msg) . "')");

        // Notify Landlord
        $landlord_msg = "You received a rent payment of GH₵ {$amount_formatted} for {$payment_info['property_title']}.";
        $db->db_query("INSERT INTO notifications (user_id, message) VALUES ('{$payment_info['owner_id']}', '" . mysqli_real_escape_string($db->db_conn(), $landlord_msg) . "')");
    }

    // Redirect to receipt page
    header("Location: ../tenant/receipt.php?reference=" . $reference);
} else {
    // Update to failed
    $db = new db_connection();
    $sql = "UPDATE payments SET payment_status = 'failed' WHERE transaction_reference = '$reference'";
    $db->db_query($sql);

    header("Location: ../tenant/pay_rent.php?error=verification_failed");
}
exit();
