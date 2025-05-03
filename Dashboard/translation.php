<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../Configurations/db.php';

if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    unset($_SESSION['text']);
    unset($_SESSION['language']);
    unset($_SESSION['translatedText']);
    header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}
$translatedText = '';
$apiError = false;

$languageFullNames = [
    'fr' => 'French',
    'es' => 'Spanish',
    'de' => 'German',
    'ja' => 'Japanese'
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    $text = trim($_POST["text"]);
    $language = $_POST["language"];
    
    $_SESSION['text'] = $text;
    $_SESSION['language'] = $language;

    // Try API translation with Gemini
    try {
        $apiKey = "AIzaSyCKSDqxeEsq4UWQCHstFmyquxg5YHi8y6Q";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        // Get the full language name
        $languageName = isset($languageFullNames[$language]) ? $languageFullNames[$language] : $language;

        // Prepare data for Gemini API
        $data = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => "Translate the following text to $languageName. Only respond with the translation, nothing else: \"$text\""
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.2,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 1024
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (!curl_errno($ch) && $httpCode >= 200 && $httpCode < 300) {
            $decoded = json_decode($response, true);

            if (isset($decoded["candidates"][0]["content"]["parts"][0]["text"])) {
                $translatedText = trim($decoded["candidates"][0]["content"]["parts"][0]["text"]);
                
                $_SESSION['translatedText'] = $translatedText;
                
                $translatedText = preg_replace('/^["\'](.*)["\']\s*$/', '$1', $translatedText);
                
                if (strpos(strtolower($translatedText), 'translation:') !== false) {
                    $parts = preg_split('/translation:\s*/i', $translatedText, 2);
                    if (count($parts) > 1) {
                        $translatedText = trim($parts[1]);
                    }
                }
            } else {
                $apiError = true;
                $translatedText = "Translation failed. Please try again.";
                error_log("Gemini API invalid response format: " . $response);
            }
        } else {
            $apiError = true;
            $translatedText = "Translation service unavailable. Please try again later.";
            error_log("Gemini API error: Status code $httpCode, Response: $response");
        }

        curl_close($ch);
    } catch (Exception $e) {
        $apiError = true;
        $translatedText = "An error occurred during translation.";
        error_log("Translation error: " . $e->getMessage());
    }
} else {
    
    if (isset($_SESSION['translatedText'])) {
        $translatedText = $_SESSION['translatedText'];
    }
}


$languageNames = [
    'fr' => 'French',
    'es' => 'Spanish',
    'de' => 'German',
    'ja' => 'Japanese'
];

// Get current date for greeting
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// Replace this section:
// Mock user data - in a real app, this would come from your database
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT u.username, u.onboarding_complete, uo.selected_language, uo.daily_goal, uo.proficiency_level,
           l.first_name, l.last_name, COALESCE(us.level, 1) as user_level
    FROM users u 
    LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID 
    LEFT JOIN learner l ON u.user_ID = l.user_ID
    LEFT JOIN user_stats us ON u.user_ID = us.user_id
    WHERE u.user_ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../Login/signin.php");
    exit();
}

$user = $result->fetch_assoc();

// Get user data from the database result
$first_name = $user['first_name'] ?? 'User';
$selected_language = $user['selected_language'] ?? 'English';
$user_level = $user['user_level'] ?? 1;

// Function to get flag emoji based on language
function getLanguageFlag($language) {
    $flags = [
        'Spanish' => '🇪🇸',
        'French' => '🇫🇷',
        'English' => '🇬🇧',
        'German' => '🇩🇪',
        'Italian' => '🇮🇹',
        'Portuguese' => '🇵🇹',
        'Russian' => '🇷🇺',
        'Japanese' => '🇯🇵',
        'Chinese' => '🇨🇳',
        'Korean' => '🇰🇷',
        'Arabic' => '🇸🇦',
    ];
    
    return $flags[$language] ?? '🌐';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translation | Mura</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            min-height: 100vh;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #5a3b5d 0%, #3f3d56 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Logo container styles */
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mura-logo {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;

        }

        .mura-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: bold;
            color: #b39ddb;
            letter-spacing: 2px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #e8e8e8;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-item a:hover {
            background-color: rgba(179, 157, 219, 0.1);
            border-left: 3px solid #b39ddb;
        }

        .nav-item.active a {
            background-color: rgba(179, 157, 219, 0.2);
            border-left: 3px solid #b39ddb;
            color: #b39ddb;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            color: #e8e8e8;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logout-btn i {
            margin-right: 10px;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            background-color: #f5f7fa;
            margin-left: 250px;
            position: relative;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
            margin-right: 15px;
        }

        /* Left Stats (Level and Streak) */
        .left-stats {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Level Indicator Styles */
        .level-indicator {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }

        .level-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #7e57c2, #5a3b5d);
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .level-number {
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        .level-label {
            font-size: 14px;
            color: #666;
        }

        /* Language Indicator Styles */
        .language-indicator {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .language-indicator:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .language-name {
            font-size: 14px;
            font-weight: 500;
            color: #5a3b5d;
            margin-right: 8px;
        }

        .language-flag {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Content Styles */
        .content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-section {
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            font-size: 24px;
            color: #5a3b5d;
            margin-bottom: 10px;
        }

        /* Translation Container */
        .translation-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }

        .translation-container h3 {
            font-size: 18px;
            color: #5a3b5d;
            margin-bottom: 20px;
            text-align: center;
        }

        .translation-form {
            display: flex;
            flex-direction: column;
        }

        .translation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .translation-box {
            display: flex;
            flex-direction: column;
        }

        .translation-box label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .translation-input {
            width: 100%;
            height: 180px;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background-color: #fff;
            color: #333;
            resize: none;
            font-size: 16px;
            transition: all 0.3s;
        }

        .translation-input:focus {
            border-color: #7e57c2;
            box-shadow: 0 0 0 2px rgba(126, 87, 194, 0.2);
            outline: none;
        }

        .translation-output {
            width: 100%;
            height: 180px;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background-color: #f9f4ff;
            color: #333;
            font-size: 16px;
            overflow-y: auto;
            position: relative;
        }

        .language-select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background-color: #fff;
            color: #333;
            font-size: 16px;
            margin-bottom: 20px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%235a3b5d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            cursor: pointer;
        }

        .language-select:focus {
            border-color: #7e57c2;
            box-shadow: 0 0 0 2px rgba(126, 87, 194, 0.2);
            outline: none;
        }

        .translation-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: #5a3b5d;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #7e57c2;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background-color: #f0e6ff;
            color: #5a3b5d;
        }

        .btn-secondary:hover {
            background-color: #e6d9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .copy-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: #5a3b5d;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0.8;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .copy-btn:hover {
            opacity: 1;
        }

        .translation-note {
            text-align: center;
            font-size: 13px;
            color: #666;
            margin-top: 15px;
            font-style: italic;
        }

        .translation-status {
            text-align: center;
            font-size: 14px;
            color: #5a3b5d;
            margin-top: 10px;
            font-weight: 500;
        }

        /* Background words animation */
        #backgroundWords {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .translation-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                height: 100%;
                z-index: 999;
            }

            .sidebar.active {
                left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .main-content {
                width: 100%;
                margin-left: 0;
            }

            .top-bar {
                padding: 15px 20px;
            }

            .left-stats {
                gap: 10px;
            }

            .level-indicator,
            .language-indicator {
                padding: 6px 10px;
            }

            .level-label,
            .language-name {
                display: none;
            }

            .logo-container {
                flex-direction: column;
            }

            .mura-logo {
                margin-right: 0;
                margin-bottom: 5px;
            }

            .content {
                padding: 20px 15px;
            }

            .translation-container {
                padding: 15px;
            }

            .translation-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div id="backgroundWords"></div>
    
    <div class="app-container">
        <div class="sidebar">
            <div class="logo">
                <div class="logo-container">
                    <div class="mura-logo">
                        <img src="../image/mura.png" alt="Mura Logo">
                    </div>
                    <h1>Mura</h1>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="../Dashboard/dashboard.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Lessons/lessons.php">
                            <i class="fas fa-book-open"></i>
                            Lessons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../chatai/chatai.php">
                            <i class="fas fa-robot"></i>
                            TutorBot
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Game/index.php">
                            <i class="fas fa-gamepad"></i>
                            Language Combat
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="translation.php">
                            <i class="fas fa-language"></i>
                            Translation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../ChatRoom/videochat.php">
                            <i class="fas fa-video"></i>
                            Video Chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Achievements/achievements.php">
                            <i class="fas fa-trophy"></i>
                            Achievements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Community/community.php">
                            <i class="fas fa-users"></i>
                            Community
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Settings/settings.php">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="left-stats">
                    <div class="level-indicator">
                        <div class="level-badge">
                            <span class="level-number"><?php echo $user_level; ?></span>
                        </div>
                        <span class="level-label">Level</span>
                    </div>
                </div>
                <div class="language-indicator">
                    <span class="language-name"><?php echo htmlspecialchars($selected_language); ?></span>
                    <div class="language-flag">
                        <span><?php echo getLanguageFlag($selected_language); ?></span>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="welcome-section">
                    <h2><?php echo $greeting; ?>, <?php echo htmlspecialchars($first_name); ?>!</h2>
                    <p>Translate text between multiple languages instantly.</p>
                </div>

                <div class="translation-container">
                    <h3>Translation Tool</h3>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="translationForm" class="translation-form">
                        <div class="translation-grid">
                            <div class="translation-box">
                                <label for="text">Enter text to translate:</label>
                                <textarea id="text" name="text" class="translation-input" required><?php echo isset($_SESSION['text']) ? htmlspecialchars($_SESSION['text']) : ''; ?></textarea>
                            </div>
                            
                            <div class="translation-box">
                                <label for="result">Translation:</label>
                                <div id="show" class="translation-output">
                                    <?php if (!empty($translatedText)): ?>
                                        <?php echo htmlspecialchars($translatedText); ?>
                                        <button class="copy-btn" onclick="copyTranslation()">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <select id="language" name="language" class="language-select" required>
                            <option value="">--Select Target Language--</option>
                            <option value="fr" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'fr') ? 'selected' : ''; ?>>French</option>
                            <option value="es" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'es') ? 'selected' : ''; ?>>Spanish</option>
                            <option value="de" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'de') ? 'selected' : ''; ?>>German</option>
                            <option value="ja" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'ja') ? 'selected' : ''; ?>>Japanese</option>
                        </select>
                        
                        <div class="translation-actions">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-language"></i> Translate
                            </button>
                            <button type="button" onclick="clearAll();" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                        </div>
                        
                        <div class="translation-note">
                            Common phrases are available offline. Advanced translations require internet connection.
                        </div>
                        
                        <?php if (!empty($translatedText) && isset($_SESSION['language']) && array_key_exists($_SESSION['language'], $languageNames)): ?>
                            <div class="translation-status">
                                <?php if (!$apiError): ?>
                                    <span>Successfully translated to <?php echo $languageNames[$_SESSION['language']]; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Background floating words animation
    const backgroundWordsContainer = document.getElementById("backgroundWords");

    const words = [
      "Hello", "Hola", "Bonjour", "Ciao", "Hallo", "こんにちは", "Adiós", "Merci",
      "Gracias", "Danke", "ありがとう", "Oui", "Sí", "Ja", "はい",
      "Non", "No", "Nein", "いいえ", "Au revoir", "Auf Wiedersehen", "さようなら"
    ];

    function createFloatingWord(text) {
      const word = document.createElement("span");
      word.textContent = text;

      const x = Math.random() * window.innerWidth;
      const y = window.innerHeight + Math.random() * 100;

      word.style.position = "fixed";
      word.style.left = `${x}px`;
      word.style.top = `${y}px`;
      word.style.fontSize = `${14 + Math.random() * 20}px`;
      word.style.opacity = "0.08";
      word.style.color = "#5a3b5d";
      word.style.fontWeight = "600";
      word.style.pointerEvents = "none";
      word.style.zIndex = "0";
      word.style.transition = "top 0.1s linear";

      backgroundWordsContainer.appendChild(word);

      let currentY = y;
      const speed = 0.8 + Math.random() * 0.6; 

      function animate() {
        currentY -= speed;
        
        // If the word reaches the top, remove it instead of repositioning
        if (currentY < -50) {
          word.remove(); // Remove the word from the DOM
          return; // Stop the animation for this word
        }
        
        word.style.top = `${currentY}px`;
        requestAnimationFrame(animate);
      }

      animate();
    }

    // Create new floating words at intervals
    setInterval(() => {
      const wordText = words[Math.floor(Math.random() * words.length)];
      createFloatingWord(wordText);
    }, 400);

    // Copy translation to clipboard
    function copyTranslation() {
      const translationText = document.getElementById('show').innerText.replace('Copy', '').trim();
      
      if (navigator.clipboard) {
        navigator.clipboard.writeText(translationText)
          .then(() => {
            const copyBtn = document.querySelector('.copy-btn');
            copyBtn.textContent = 'Copied!';
            setTimeout(() => {
              copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
            }, 2000);
          })
          .catch(err => {
            console.error('Failed to copy: ', err);
          });
      } else {
        // Fallback for browsers that don't support clipboard API
        const textarea = document.createElement('textarea');
        textarea.value = translationText;
        
        textarea.style.position = 'fixed';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        
        try {
          document.execCommand('copy');
          const copyBtn = document.querySelector('.copy-btn');
          copyBtn.textContent = 'Copied!';
          setTimeout(() => {
            copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
          }, 2000);
        } catch (err) {
          console.error('Failed to copy: ', err);
        }
        
        document.body.removeChild(textarea);
      }
    }

    // Clear all data and destroy session
    function clearAll() {
        // Clear inputs
        document.getElementById('text').value = '';
        document.getElementById('language').value = '';
        document.getElementById('show').innerHTML = '';

        // Clear optional status (if it exists)
        const statusElement = document.querySelector('.translation-status');
        if (statusElement) {
            statusElement.innerHTML = '';
        }

        // Redirect to clear session data
        window.location.href = '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?action=clear';
    }
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
      const menuToggle = document.querySelector('.menu-toggle');
      const sidebar = document.querySelector('.sidebar');
      
      if (menuToggle) {
        menuToggle.addEventListener('click', function() {
          sidebar.classList.toggle('active');
        });
      }
    });
    </script>
</body>
</html>
