<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Check login and role
if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../landlord/add_property.php");
    exit();
}

// Sanitize inputs
$db = new db_connection();
$conn = $db->db_conn();

$owner_id = get_user_id();
$title = mysqli_real_escape_string($conn, $_POST['title']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$city = mysqli_real_escape_string($conn, $_POST['city']);
$price = (float) $_POST['price'];
$property_type = mysqli_real_escape_string($conn, $_POST['property_type']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$lease_type = mysqli_real_escape_string($conn, $_POST['lease_type'] ?? 'renting');
$payment_period = mysqli_real_escape_string($conn, $_POST['payment_period'] ?? 'monthly');
$raw_map_location = $_POST['map_location'] ?? '';

// Extract src if user pasted full iframe
// If they pasted a URL directly, leave it as is or handle it.
if (stripos($raw_map_location, '<iframe') !== false) {
    if (preg_match('/src=["\']([^"\']+)["\']/i', $raw_map_location, $matches)) {
        if (!empty($matches[1])) {
            $raw_map_location = $matches[1];
        }
    }
} elseif (stripos($raw_map_location, 'google.com/maps') !== false || stripos($raw_map_location, 'goo.gl/maps') !== false || stripos($raw_map_location, 'maps.app.goo.gl') !== false) {
    // Standard URL processing is handled by frontend.
}

$map_location = mysqli_real_escape_string($conn, $raw_map_location);

// Insert property
$sql = "INSERT INTO properties (owner_id, title, description, address, city, price, property_type, status, lease_type, payment_period, map_location) 
        VALUES ('$owner_id', '$title', '$description', '$address', '$city', '$price', '$property_type', '$status', '$lease_type', '$payment_period', '$map_location')";

if ($db->db_query($sql)) {
    $property_id = $db->last_insert_id();

    // Handle Images
    $upload_dir = '../uploads/property_images/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (isset($_FILES['images'])) {
        $total_files = count($_FILES['images']['name']);

        for ($i = 0; $i < $total_files; $i++) {
            $file_name = $_FILES['images']['name'][$i];
            $file_tmp = $_FILES['images']['tmp_name'][$i];
            $file_error = $_FILES['images']['error'][$i];

            if ($file_error === 0) {
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($file_ext, $allowed)) {
                    $new_name = uniqid('', true) . "." . $file_ext;
                    $dest = $upload_dir . $new_name;

                    if (move_uploaded_file($file_tmp, $dest)) {
                        $img_sql = "INSERT INTO property_images (property_id, image_path) VALUES ('$property_id', '$new_name')";
                        $db->db_query($img_sql);
                    }
                }
            }
        }
    }

    header("Location: ../landlord/add_property.php?success=1");
} else {
    header("Location: ../landlord/add_property.php?error=failed");
}
exit();
