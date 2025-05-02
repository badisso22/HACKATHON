<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language = $_POST['language'] ?? null;

    if ($language) {
        $stmt = $conn->prepare("UPDATE user_onboarding SET selected_language = ? WHERE user_ID = ?");
        $stmt->bind_param("si", $language, $user_id);
        $stmt->execute();

        header("Location: qst2.php");
        exit();
    }
}

try {
    $stmt = $conn->prepare("SELECT selected_language FROM user_onboarding WHERE user_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        if (!empty($data['selected_language'])) {
            header("Location: qst2.php");
            exit();
        }
    }

    // Count learners for each language dynamically
    $learnerCounts = [];
    $learnerStmt = $conn->prepare("SELECT selected_language, COUNT(*) as learner_count FROM user_onboarding GROUP BY selected_language");
    $learnerStmt->execute();
    $learnerResult = $learnerStmt->get_result();

    while ($row = $learnerResult->fetch_assoc()) {
        $learnerCounts[$row['selected_language']] = $row['learner_count'];
    }
} catch (Exception $e) {
    error_log("Error in lan1.php: " . $e->getMessage());
    header("Location: signin.php");
    exit();
}

// Store user_id in sessionStorage for JavaScript
echo "<script>sessionStorage.setItem('user_id', '" . $_SESSION['user_id'] . "');</script>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mura - Learn Languages</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="lan1.css">
</head>
<body>
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <header class="header">
        <div class="logo">
            <div class="logo-icon">🦉</div>
            <div class="logo-text">Mura</div>
        </div>
        <div class="language-selector">
            <span>SITE LANGUAGE:</span>
            <strong>ENGLISH</strong>
            <span>▼</span>
        </div>
    </header>

    <main class="main-content">
        <h1 class="page-title">I want to learn...</h1>

        <div class="language-grid">
            <form method="POST" action="">
                <button class="language-card" name="language" value="Spanish">
                    <div class="flag">
                        <div class="spanish-flag">
                            <div class="spanish-red"></div>
                            <div class="spanish-yellow">
                                <div class="spanish-emblem"></div>
                            </div>
                            <div class="spanish-red"></div>
                        </div>
                    </div>
                    <div class="language-name">Spanish</div>
                    <div class="learner-count"><?php echo $learnerCounts['Spanish'] ?? 0; ?> learners</div>
                </button>

                <button class="language-card" name="language" value="French">
                    <div class="flag">
                        <div class="french-flag">
                            <div class="french-blue"></div>
                            <div class="french-white"></div>
                            <div class="french-red"></div>
                        </div>
                    </div>
                    <div class="language-name">French</div>
                    <div class="learner-count"><?php echo $learnerCounts['French'] ?? 0; ?> learners</div>
                </button>

                <button class="language-card" name="language" value="English">
                    <div class="flag">
                        <div class="english-flag">
                            <div class="english-horizontal"></div>
                            <div class="english-vertical"></div>
                        </div>
                    </div>
                    <div class="language-name">English</div>
                    <div class="learner-count"><?php echo $learnerCounts['English'] ?? 0; ?> learners</div>
                </button>

                <button class="language-card" name="language" value="German">
                    <div class="flag">
                        <div class="german-flag">
                            <div class="german-black"></div>
                            <div class="german-red"></div>
                            <div class="german-gold"></div>
                        </div>
                    </div>
                    <div class="language-name">German</div>
                    <div class="learner-count"><?php echo $learnerCounts['German'] ?? 0; ?> learners</div>
                </button>

                <button class="language-card" name="language" value="Italian">
                    <div class="flag">
                        <div class="italian-flag">
                            <div class="italian-green"></div>
                            <div class="italian-white"></div>
                            <div class="italian-red"></div>
                        </div>
                    </div>
                    <div class="language-name">Italian</div>
                    <div class="learner-count"><?php echo $learnerCounts['Italian'] ?? 0; ?> learners</div>
                </button>
            </form>
        </div>
    </main>
</body>
</html>