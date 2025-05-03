<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../Configurations/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data including level, streak, and language
$stmt = $conn->prepare("
    SELECT us.level, ust.current_streak, uo.selected_language, uo.proficiency_level
    FROM users u 
    LEFT JOIN user_stats us ON u.user_ID = us.user_id
    LEFT JOIN user_streaks ust ON u.user_ID = ust.user_id
    LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID
    WHERE u.user_ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$user_level = $user_data['level'] ?? 1;
$streak = $user_data['current_streak'] ?? 0;
$selected_language = $user_data['selected_language'] ?? 'Spanish';
$proficiency_level = $user_data['proficiency_level'] ?? 'Beginner';

// Function to get language flag emoji
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
<html>
<head>
  <title>Video Chat</title>
  <script src="https://cdn.agora.io/sdk/web/AgoraRTC_N.js"></script>
  <link rel="stylesheet" href="../css/videochat.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <div class="video-chat-container">
    <div class="user-stats-bar">
      <div class="left-stats">
        <div class="level-indicator">
          <div class="level-badge">
            <span class="level-number"><?php echo $user_level; ?></span>
          </div>
          <span class="level-label">Level</span>
        </div>
        <div class="streak-counter">
          <span class="streak-number"><?php echo $streak; ?></span>
          <div class="streak-icon">
            <span class="fire-emoji">🔥</span>
          </div>
          <span class="streak-label">Streak</span>
        </div>
      </div>
      <div class="language-indicator">
        <span class="language-name"><?php echo htmlspecialchars($selected_language); ?></span>
        <div class="language-flag">
          <span><?php echo getLanguageFlag($selected_language); ?></span>
        </div>
      </div>
    </div>
    
    <h2>Video Chat Room</h2>
    <div id="videos">
      <div id="local-video"></div>
      <div id="remote-video"></div>
    </div>
    <div class="button-container">
      <button onclick="startCall()">Start Call</button>
      <button onclick="leaveCall()">Leave Call</button>
    </div>
    <div class="bottom-navigation">
      <a href="../Dashboard/dashboard.php" class="back-btn" id="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>
  </div>

  <script>
    const APP_ID = "fca49702e5d64960a1cfd5dc860decc1"; 
    const CHANNEL = "videoChat";     
    const TOKEN = null;          

    let client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

    async function startCall() {
      await client.join(APP_ID, CHANNEL, TOKEN, null);

      const localStream = await AgoraRTC.createCameraVideoTrack();
      localStream.play("local-video");

      client.on("user-published", async (user, mediaType) => {
        await client.subscribe(user, mediaType);
        if (mediaType === "video") {
          const remoteTrack = user.videoTrack;
          remoteTrack.play("remote-video");
        }
      });

      await client.publish([localStream]);
    }

    async function leaveCall() {
      await client.leave();
      document.getElementById("local-video").innerHTML = "";
      document.getElementById("remote-video").innerHTML = "";
    }
    document.getElementById("back-button").href = "../Dashboard/dashboard.php";
  </script>
</body>
</html>
