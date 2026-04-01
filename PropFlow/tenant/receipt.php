<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

if (!is_tenant()) {
    header("Location: ../public/login.php");
    exit();
}

$reference = isset($_GET['reference']) ? $_GET['reference'] : '';

if (empty($reference)) {
    header("Location: dashboard.php");
    exit();
}

$db = new db_connection();
$user_id = get_user_id();

// Fetch payment details securely ensuring it belongs to this tenant
$sql = "SELECT p.*, prop.title, prop.address, prop.city, u.full_name as landlord_name
        FROM payments p
        JOIN tenancies t ON p.tenancy_id = t.tenancy_id
        JOIN properties prop ON t.property_id = prop.property_id
        JOIN users u ON prop.owner_id = u.user_id
        WHERE p.transaction_reference = '$reference' AND t.tenant_id = '$user_id'";

$payment = $db->db_fetch_one($sql);

if (!$payment) {
    die("Receipt not found or access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt -
        <?php echo $reference; ?>
    </title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
        }

        .receipt-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .receipt-header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .receipt-header p {
            color: #7f8c8d;
            margin-top: 5px;
        }

        .receipt-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .receipt-item label {
            display: block;
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .receipt-item span {
            display: block;
            font-weight: 600;
            font-size: 16px;
        }

        .amount-box {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 4px;
            margin-bottom: 30px;
        }

        .amount-box .label {
            font-size: 14px;
            color: #7f8c8d;
        }

        .amount-box .amount {
            font-size: 32px;
            color: #27ae60;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #95a5a6;
        }

        .actions {
            text-align: center;
            margin-top: 40px;
        }

        .btn {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-print {
            background: #2c3e50;
            margin-right: 10px;
        }

        @media print {
            body {
                background: white;
            }

            .receipt-container {
                box-shadow: none;
                border: 1px solid #eee;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Payment Receipt</h1>
            <p>PropFlow Property Management</p>
        </div>

        <div class="amount-box">
            <div class="label">Amount Paid</div>
            <div class="amount">GHS
                <?php echo number_format($payment['amount'], 2); ?>
            </div>
        </div>

        <div class="receipt-grid">
            <div class="receipt-item">
                <label>Date</label>
                <span>
                    <?php echo date('F d, Y, h:i A', strtotime($payment['payment_date'])); ?>
                </span>
            </div>
            <div class="receipt-item">
                <label>Reference ID</label>
                <span>
                    <?php echo htmlspecialchars($payment['transaction_reference']); ?>
                </span>
            </div>
            <div class="receipt-item">
                <label>Property</label>
                <span>
                    <?php echo htmlspecialchars($payment['title']); ?>
                </span>
            </div>
            <div class="receipt-item">
                <label>Location</label>
                <span>
                    <?php echo htmlspecialchars($payment['city']); ?>
                </span>
            </div>
            <div class="receipt-item">
                <label>Landlord</label>
                <span>
                    <?php echo htmlspecialchars($payment['landlord_name']); ?>
                </span>
            </div>
            <div class="receipt-item">
                <label>Payment Method</label>
                <span>
                    <?php echo ucfirst($payment['payment_method']); ?>
                </span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your payment.<br>This is a computer-generated receipt.</p>
        </div>

        <div class="actions">
            <button onclick="window.print()" class="btn btn-print">Print Receipt</button>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </div>

</body>

</html>