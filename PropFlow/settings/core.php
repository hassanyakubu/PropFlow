<?php
/**
 * Core Session Management & Authorization Functions
 * This file handles session management and user privilege checking
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 * @return bool - Returns true if user is logged in, false otherwise
 */
function is_logged_in()
{
    // Check if user_id exists in session and is not empty
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user has administrative privileges
 * @return bool - Returns true if user is admin (role = 1), false otherwise
 */
function is_admin()
{
    // First check if user is logged in
    if (!is_logged_in()) {
        return false;
    }

    // Check if user role exists and equals 'admin'
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if the logged-in user is a landlord
 * @return bool
 */
function is_landlord()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'landlord';
}

/**
 * Check if the logged-in user is a tenant
 * @return bool
 */
function is_tenant()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'tenant';
}

/**
 * Get current user's role
 * @return int|null - Returns user role or null if not logged in
 */
function get_user_role()
{
    if (is_logged_in()) {
        return $_SESSION['user_role'] ?? null;
    }
    return null;
}

/**
 * Get current user's ID
 * @return int|null - Returns user ID or null if not logged in
 */
function get_user_id()
{
    if (is_logged_in()) {
        return $_SESSION['user_id'] ?? null;
    }
    return null;
}

/**
 * Get current user's name
 * @return string|null - Returns user name or null if not logged in
 */
function get_user_name()
{
    if (is_logged_in()) {
        return $_SESSION['user_name'] ?? null;
    }
    return null;
}

/**
 * Get current user's first name
 * @return string|null - Returns user first name or null if not logged in
 */
function get_user_first_name()
{
    if (is_logged_in()) {
        $full_name = $_SESSION['user_name'] ?? '';
        $parts = explode(' ', trim($full_name));
        return $parts[0] ?? null;
    }
    return null;
}

/**
 * Get current user's email
 * @return string|null - Returns user email or null if not logged in
 */
function get_user_email()
{
    if (is_logged_in()) {
        return $_SESSION['user_email'] ?? null;
    }
    return null;
}

/**
 * Require user to be logged in - redirect if not
 * @param string $redirect_url - URL to redirect to if not logged in (default: login page)
 */
function require_login($redirect_url = 'login/login.php')
{
    if (!is_logged_in()) {
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Require admin privileges - redirect if not admin
 * @param string $redirect_url - URL to redirect to if not admin (default: index page)
 */
function require_admin($redirect_url = 'index.php')
{
    if (!is_admin()) {
        // Log unauthorized access attempt
        error_log("Unauthorized admin access attempt by user ID: " . (get_user_id() ?? 'guest'));
        header("Location: $redirect_url?error=access_denied");
        exit();
    }
}

/**
 * Check if current user can access a specific resource
 * @param string $required_role - 'admin' or 'customer' or 'any'
 * @return bool - Returns true if user can access, false otherwise
 */
function can_access($required_role = 'any')
{
    switch ($required_role) {
        case 'admin':
            return is_admin();
        case 'landlord':
            return is_landlord();
        case 'tenant':
            return is_tenant();
        case 'any':
            return is_logged_in();
        default:
            return false;
    }
}

/**
 * Get user role name as string
 * @return string - Returns 'Admin', 'Customer', or 'Guest'
 */
function get_user_role_name()
{
    if (!is_logged_in()) {
        return 'Guest';
    }

    if (is_admin())
        return 'Admin';
    if (is_landlord())
        return 'Landlord';
    if (is_tenant())
        return 'Tenant';
    return is_logged_in() ? 'User' : 'Guest';
}

/**
 * Log user activity (optional function for tracking)
 * @param string $activity - Description of the activity
 */
function log_user_activity($activity)
{
    $user_info = is_logged_in()
        ? "User ID: " . get_user_id() . " (" . get_user_name() . ")"
        : "Guest user";

    error_log("User Activity - $user_info - $activity");
}

/**
 * Get the current active property ID for a tenant
 * @param int $tenant_id
 * @return int|null
 */
function get_tenant_current_property_id($tenant_id)
{
    // We need database access. Including db_class.php safely.
    require_once __DIR__ . '/db_class.php';

    $db = new db_connection();
    $conn = $db->db_conn(); // Returns mysqli object

    if (!$conn) {
        return null;
    }

    $sql = "SELECT property_id FROM tenancies WHERE tenant_id = ? AND status = 'active' LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['property_id'];
        }
        $stmt->close();
    }
    return null;
}
?>