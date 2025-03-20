<?php
session_start();
include 'db.php'; // Database connection

// Check if HOD is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'HOD') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch HOD details from the database
$query = "SELECT name, branch FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$hod = $result->fetch_assoc();

if ($hod) {
    echo json_encode($hod);
} else {
    echo json_encode(['error' => 'HOD details not found']);
}
?>
