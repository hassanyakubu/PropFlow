<?php
require_once '../settings/core.php';
require_once '../settings/paystack_config.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../tenant/pay_rent.php");
    exit();
}

$tenancy_id = (int) $_POST['tenancy_id'];
$amount = (float) $_POST['amount'];
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// Initialize transaction
$response = paystack_initialize_transaction($amount, $email);

if ($response && $response['status']) {
    $auth_url = $response['data']['authorization_url'];
    $reference = $response['data']['reference'];

    // Store reference and tenancy_id in session to link them later during verification
    // OR insert into DB as pending. Let's insert as pending.

    require_once '../settings/db_class.php';
    $db = new db_connection();

    $sql = "INSERT INTO payments (tenancy_id, amount, payment_method, payment_status, transaction_reference) 
            VALUES ('$tenancy_id', '$amount', 'paystack', 'pending', '$reference')";

    $db->db_query($sql);

    // Redirect to Paystack
    header("Location: " . $auth_url);
} else {
    header("Location: ../tenant/pay_rent.php?error=api_error");
}
exit();
