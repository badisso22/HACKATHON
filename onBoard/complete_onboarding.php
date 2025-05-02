<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    error_log("User not authenticated");
    die(json_encode(['success' => false, 'error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("UPDATE users SET onboarding_complete = TRUE WHERE user_ID = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        error_log("Database error: " . $conn->error);
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} catch (Exception $e) {
    error_log("Error in complete_onboarding.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>