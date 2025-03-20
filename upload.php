<?php
header('Content-Type: application/json');
require 'db.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method", 405);
    }

    // Validate input parameters
    $required = ['action', 'branch_name', 'role', 'username'];
    foreach ($required as $param) {
        if (empty($_POST[$param])) {
            throw new Exception("Missing parameter: $param", 400);
        }
    }

    // Sanitize inputs
    $branchName = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['branch_name']);
    $username = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['username']);
    $role = $_POST['role'];

    // Validate file upload
    if (empty($_FILES['file'])) {
        throw new Exception("No file uploaded", 400);
    }

    $file = $_FILES['file'];
    
    // Validate file type
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif',
        'video/mp4', 'video/mpeg', 'video/quicktime',
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Invalid file type", 400);
    }

    // Generate safe filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('upload_', true) . '.' . $fileExtension;

    // Create upload directory
    $uploadDir = __DIR__ . "/uploads/$branchName/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new Exception("Failed to save file", 500);
    }

    echo json_encode([
        "status" => "success",
        "message" => "File uploaded successfully",
        "path" => $uploadDir . $filename
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    error_log("Upload Error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>