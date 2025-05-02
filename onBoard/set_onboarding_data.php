<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    error_log("User not authenticated");
    die(json_encode(['success' => false, 'error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Check if onboarding record exists
    $check = $conn->prepare("SELECT 1 FROM user_onboarding WHERE user_ID = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        // Create new record
        $stmt = $conn->prepare("INSERT INTO user_onboarding (user_ID) VALUES (?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    // Build update query based on provided data
    $updates = [];
    $params = [];
    $types = "";
    
    if (isset($data['language'])) {
        $updates[] = "selected_language = ?";
        $params[] = $data['language'];
        $types .= "s";
    }
    
    if (isset($data['daily_goal'])) {
        $updates[] = "daily_goal = ?";
        $params[] = $data['daily_goal'];
        $types .= "i";
    }
    
    if (isset($data['proficiency_level'])) {
        $updates[] = "proficiency_level = ?";
        $params[] = $data['proficiency_level'];
        $types .= "s";
    }
    
    if (!empty($updates)) {
        $query = "UPDATE user_onboarding SET " . implode(", ", $updates) . " WHERE user_ID = ?";
        $params[] = $user_id;
        $types .= "i";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error in set_onboarding_data.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>