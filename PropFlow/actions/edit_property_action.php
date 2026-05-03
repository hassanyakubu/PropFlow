<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Check login and role
if (!is_landlord()) {
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../landlord/dashboard.php");
    exit();
}

$db = new db_connection();
$conn = $db->db_conn();

$property_id = (int) $_POST['property_id'];
$owner_id = get_user_id();

// Verify ownership
$check = $db->db_fetch_one("SELECT property_id FROM properties WHERE property_id = '$property_id' AND owner_id = '$owner_id'");
if (!$check) {
    die("Unauthorized access");
}

// Sanitize inputs
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
if (stripos($raw_map_location, '<iframe') !== false) {
    if (preg_match('/src=["\']([^"\']+)["\']/i', $raw_map_location, $matches)) {
        if (!empty($matches[1])) {
            $raw_map_location = $matches[1];
        }
    }
} elseif (stripos($raw_map_location, 'google.com/maps') !== false || stripos($raw_map_location, 'goo.gl/maps') !== false || stripos($raw_map_location, 'maps.app.goo.gl') !== false) {
    // Standard URLs can't be framed easily without the /embed endpoint, 
    // but the frontend now handles them gracefully by providing an external link button
}

$map_location = mysqli_real_escape_string($conn, $raw_map_location);

// Update property
$sql = "UPDATE properties 
        SET title = '$title', 
            description = '$description', 
            address = '$address', 
            city = '$city', 
            price = '$price', 
            property_type = '$property_type', 
            status = '$status',
            lease_type = '$lease_type',
            payment_period = '$payment_period',
            map_location = '$map_location'
        WHERE property_id = '$property_id' AND owner_id = '$owner_id'";

if ($db->db_query($sql)) {
    // Handle new image uploads if provided
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = '../uploads/property_images/';

        // Ensure directory exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_count = count($_FILES['images']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $filename = basename($_FILES['images']['name'][$i]);
            $tmp_name = $_FILES['images']['tmp_name'][$i];

            // Generate unique filename to avoid overwriting
            $new_filename = uniqid() . '_' . $filename;
            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $destination)) {
                // Insert into DB
                $img_sql = "INSERT INTO property_images (property_id, image_path) VALUES ('$property_id', '$new_filename')";
                $db->db_query($img_sql);
            }
        }
    }

    header("Location: ../landlord/edit_property.php?id=$property_id&success=1");
} else {
    header("Location: ../landlord/edit_property.php?id=$property_id&error=failed");
}
exit();
