<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch onboarding completion status and daily goal
$stmt = $conn->prepare("SELECT onboarding_complete, `daily goal` FROM users u LEFT JOIN user_onboarding o ON u.user_ID = o.user_ID WHERE u.user_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: signin.php");
    exit();
}

$user = $result->fetch_assoc();

if (!$user['onboarding_complete']) {
    header("Location: check_onboarding.php");
    exit();
}

$daily_goal = $user['daily goal'] ?? 'Not set';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <header>
        <h1>Welcome to Your Dashboard</h1>
    </header>
    <main>
        <section>
            <h2>Your Daily Goal</h2>
            <p><?php echo htmlspecialchars($daily_goal); ?></p>
        </section>
    </main>
</body>
</html>