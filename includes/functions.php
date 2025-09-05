<?php
// Utility Functions

// Start session if not already started
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in as admin
function isAdminLoggedIn() {
    startSession();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Redirect to login if not authenticated
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format NIK for display
function formatNIK($nik) {
    return chunk_split($nik, 4, ' ');
}

// Get progress status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'berhasil':
            return 'badge-success';
        case 'gagal':
            return 'badge-danger';
        case 'pending':
        default:
            return 'badge-warning';
    }
}

// Get progress status text
function getStatusText($status) {
    switch ($status) {
        case 'berhasil':
            return 'Berhasil';
        case 'gagal':
            return 'Gagal';
        case 'pending':
        default:
            return 'Pending';
    }
}

// Generate CSRF token
function generateCSRFToken() {
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Flash message functions
function setFlashMessage($type, $message) {
    startSession();
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    startSession();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Pagination helper
function getPaginationData($total_records, $records_per_page, $current_page) {
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'records_per_page' => $records_per_page,
        'total_records' => $total_records
    ];
}
?>

