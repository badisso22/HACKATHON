<?php
session_start();
require_once '../Configurations/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // First check if onboarding is already marked as complete in users table
    $stmt = $conn->prepare("SELECT onboarding_complete FROM users WHERE user_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: signin.php");
        exit();
    }

    $user = $result->fetch_assoc();

    // If onboarding is already complete, redirect to dashboard
    if ($user['onboarding_complete']) {
        header("Location: ../Dashboard/dashboard.php");
        exit();
    }

    // Check if user_onboarding record exists
    $stmt = $conn->prepare("SELECT * FROM user_onboarding WHERE user_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If no onboarding record exists, create one
    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO user_onboarding (user_ID) VALUES (?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Start from the beginning
        header("Location: lan.php");
        exit();
    }

    $data = $result->fetch_assoc();

    // Redirect based on which step is incomplete
    if (empty($data['selected_language'])) {
        header("Location: lan.php");
        exit();
    } elseif (empty($data['reason'])) {
        header("Location: Qst.php");
        exit();
    } elseif (empty($data['daily_goal'])) {
        header("Location: time.php");
        exit();
    } elseif (empty($data['proficiency_level'])) {
        header("Location: Adv.php");
        exit();
    }

    // If we get here, all steps are complete but the flag wasn't set
    // Update both completion flags
    $conn->begin_transaction();
    
    try {
        // Update is_complete in user_onboarding
        $stmt = $conn->prepare("UPDATE user_onboarding SET is_complete = 1 WHERE user_ID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Update onboarding_complete in users
        $stmt = $conn->prepare("UPDATE users SET onboarding_complete = 1 WHERE user_ID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating completion flags: " . $e->getMessage());
    }

    header("Location: ../Dashboard/dashboard.php");
    exit();
} catch (Exception $e) {
    error_log("Onboarding check error: " . $e->getMessage());
    header("Location: signin.php");
    exit();
}
?>
