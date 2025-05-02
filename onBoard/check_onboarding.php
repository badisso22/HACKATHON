<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT onboarding_complete FROM users WHERE user_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: signin.php");
        exit();
    }

    $user = $result->fetch_assoc();

    if ($user['onboarding_complete']) {
        header("Location: dashboard.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT selected_language, daily_goal, proficiency_level FROM user_onboarding WHERE user_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: lan1.php");
        exit();
    }

    $data = $result->fetch_assoc();

    if (empty($data['selected_language'])) {
        header("Location: lan1.php");
        exit();
    } elseif (empty($data['daily_goal'])) {
        header("Location: qst2.php");
        exit();
    } elseif (empty($data['proficiency_level'])) {
        header("Location: adv4.php");
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET onboarding_complete = TRUE WHERE user_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
} catch (Exception $e) {
    error_log("Onboarding check error: " . $e->getMessage());
    header("Location: signin.php");
    exit();
}
?>