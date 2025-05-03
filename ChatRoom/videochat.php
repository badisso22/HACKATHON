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
$debug_info = []; // For storing debug information

// Helper function to log debug info
function debug_log($message) {
    global $debug_info;
    $debug_info[] = date('H:i:s') . ': ' . $message;
}

debug_log("Script started with user ID: $user_id");

// Fetch user data including level, streak, and language
try {
    $stmt = $conn->prepare("
        SELECT u.username, us.level, ust.current_streak, uo.selected_language, uo.proficiency_level
        FROM users u 
        LEFT JOIN user_stats us ON u.user_ID = us.user_id
        LEFT JOIN user_streaks ust ON u.user_ID = ust.user_id
        LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID
        WHERE u.user_ID = ?
    ");
    
    if (!$stmt) {
        debug_log("Prepare statement failed: " . $conn->error);
        throw new Exception("Database query preparation failed");
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        debug_log("Query execution failed: " . $stmt->error);
        throw new Exception("Database query execution failed");
    }
    
    $user_data = $result->fetch_assoc();
    debug_log("User data fetched: " . json_encode($user_data));
    
    $username = $user_data['username'] ?? 'User';
    $user_level = $user_data['level'] ?? 1;
    $streak = $user_data['current_streak'] ?? 0;
    $selected_language = $user_data['selected_language'] ?? 'Spanish';
    $proficiency_level = $user_data['proficiency_level'] ?? 'Beginner';
    
} catch (Exception $e) {
    debug_log("Error: " . $e->getMessage());
    // Continue with default values if there's an error
    $username = 'User';
    $user_level = 1;
    $streak = 0;
    $selected_language = 'Spanish';
    $proficiency_level = 'Beginner';
}

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

// Generate a unique channel name based on user ID to avoid conflicts
$channel_name = "videoChat_" . $user_id . "_" . time();
debug_log("Channel name generated: $channel_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Chat | Mura</title>
    <script src="https://cdn.agora.io/sdk/web/AgoraRTC_N-4.17.2.js"></script>
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
        }

        .video-chat-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .user-stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .left-stats {
            display: flex;
            align-items: center;
            gap: 15px;
        }

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

        .streak-counter {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }

        .streak-number {
            font-size: 18px;
            font-weight: bold;
            color: #5a3b5d;
            margin-right: 5px;
        }

        .streak-icon {
            position: relative;
            width: 24px;
            height: 24px;
            margin: 0 5px;
        }

        .fire-emoji {
            font-size: 20px;
            position: absolute;
            top: 0;
            left: 0;
            animation: flame 0.8s infinite alternate;
        }

        @keyframes flame {
            0% {
                transform: scale(1) rotate(-5deg);
                text-shadow: 0 0 5px rgba(255, 100, 0, 0.5);
            }
            100% {
                transform: scale(1.1) rotate(5deg);
                text-shadow: 0 0 10px rgba(255, 100, 0, 0.8), 0 0 20px rgba(255, 200, 0, 0.4);
            }
        }

        .streak-label {
            font-size: 14px;
            color: #666;
        }

        .language-indicator {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
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

        h2 {
            text-align: center;
            color: #5a3b5d;
            margin-bottom: 20px;
            font-size: 28px;
        }

        #videos {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        #local-video, #remote-video {
            width: 480px;
            height: 360px;
            background-color: #000;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .video-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #2c2c2c;
            color: #fff;
        }

        .video-placeholder i {
            font-size: 48px;
            margin-bottom: 10px;
            color: #5a3b5d;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        button {
            background-color: #5a3b5d;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            background-color: #7e57c2;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        button i {
            font-size: 18px;
        }

        .bottom-navigation {
            display: flex;
            justify-content: center;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            color: #5a3b5d;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .back-btn:hover {
            background-color: rgba(90, 59, 93, 0.1);
        }

        .back-btn i {
            margin-right: 8px;
        }

        .status-message {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
            color: #5a3b5d;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
        }

        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .control-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #fff;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .control-btn:hover {
            background-color: #f5f5f5;
            transform: scale(1.1);
        }

        .control-btn.active {
            background-color: #5a3b5d;
            color: #fff;
        }

        .control-btn.end-call {
            background-color: #e53935;
            color: #fff;
        }

        .control-btn.end-call:hover {
            background-color: #c62828;
        }

        .debug-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }

        .debug-info {
            font-family: monospace;
            white-space: pre-wrap;
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 12px;
        }

        .collapsible {
            cursor: pointer;
            padding: 10px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 16px;
            background-color: #f1f1f1;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .collapsible:after {
            content: '\002B';
            font-weight: bold;
            float: right;
            margin-left: 5px;
        }

        .active:after {
            content: "\2212";
        }

        .collapsible-content {
            padding: 0 18px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.2s ease-out;
            background-color: #f8f9fa;
        }

        @media (max-width: 992px) {
            #videos {
                flex-direction: column;
                align-items: center;
            }

            #local-video, #remote-video {
                width: 100%;
                max-width: 480px;
            }
        }

        @media (max-width: 576px) {
            .user-stats-bar {
                flex-direction: column;
                gap: 10px;
            }

            .button-container {
                flex-direction: column;
                align-items: center;
            }

            .controls {
                flex-wrap: wrap;
            }
        }
    </style>
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
        <div id="status-message" class="status-message">
            Welcome, <?php echo htmlspecialchars($username); ?>! Click "Start Call" to begin a video chat session.
        </div>
        
        <div id="videos">
            <div id="local-video">
                <div class="video-placeholder">
                    <i class="fas fa-user"></i>
                    <p>Your video will appear here</p>
                </div>
            </div>
            <div id="remote-video">
                <div class="video-placeholder">
                    <i class="fas fa-user-friends"></i>
                    <p>Another's video will appear here</p>
                </div>
            </div>
        </div>
        
        <div class="controls" id="call-controls" style="display: none;">
            <button class="control-btn active" id="toggle-video" title="Toggle Video">
                <i class="fas fa-video"></i>
            </button>
            <button class="control-btn active" id="toggle-audio" title="Toggle Audio">
                <i class="fas fa-microphone"></i>
            </button>
            <button class="control-btn end-call" id="end-call" title="End Call">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>
        
        <div class="button-container">
            <button id="start-call-btn" onclick="startCall()">
                <i class="fas fa-phone"></i> Start Call
            </button>
            <button id="leave-call-btn" onclick="leaveCall()" disabled>
                <i class="fas fa-phone-slash"></i> Leave Call
            </button>
        </div>
        
        <div class="bottom-navigation">
            <a href="../Dashboard/dashboard.php" class="back-btn" id="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        // Status message function
        function showStatus(message, isError = false) {
            const statusElement = document.getElementById('status-message');
            statusElement.textContent = message;
            statusElement.className = 'status-message';
            if (isError) {
                statusElement.classList.add('error-message');
            } else {
                statusElement.classList.add('success-message');
            }
        }

        // Collapsible functionality
        const coll = document.getElementsByClassName("collapsible");
        for (let i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                const content = this.nextElementSibling;
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                }
            });
        }

        // Agora client setup
        const APP_ID = "fca49702e5d64960a1cfd5dc860decc1"; // Your Agora App ID
        const CHANNEL = "<?php echo $channel_name; ?>";
        const TOKEN = null; // Use null for testing or provide a token for production

        let client = null;
        let localTracks = {
            videoTrack: null,
            audioTrack: null
        };
        let remoteUsers = {};
        let isCallActive = false;

        // Initialize the Agora client
        async function initializeAgoraClient() {
            try {
                logDebug("Initializing Agora client");
                client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
                
                // Register event handlers
                client.on("user-published", handleUserPublished);
                client.on("user-unpublished", handleUserUnpublished);
                client.on("user-joined", handleUserJoined);
                client.on("user-left", handleUserLeft);
                client.on("exception", handleException);
                
                logDebug("Agora client initialized successfully");
                return true;
            } catch (error) {
                logDebug(`Error initializing Agora client: ${error.message}`);
                showStatus(`Failed to initialize video chat: ${error.message}`, true);
                return false;
            }
        }

        // Start the call
        async function startCall() {
            try {
                // Disable the start call button and enable the leave call button
                document.getElementById('start-call-btn').disabled = true;
                showStatus("Initializing video call...");
                
                // Initialize the client if not already done
                if (!client) {
                    const initialized = await initializeAgoraClient();
                    if (!initialized) {
                        document.getElementById('start-call-btn').disabled = false;
                        return;
                    }
                }
                
                // Join the channel
                logDebug(`Joining channel: ${CHANNEL}`);
                const uid = await client.join(APP_ID, CHANNEL, TOKEN, null);
                logDebug(`Joined channel with UID: ${uid}`);
                
                // Create and publish local tracks
                logDebug("Creating local tracks");
                [localTracks.audioTrack, localTracks.videoTrack] = await Promise.all([
                    AgoraRTC.createMicrophoneAudioTrack(),
                    AgoraRTC.createCameraVideoTrack()
                ]);
                
                // Play local video track
                localTracks.videoTrack.play("local-video");
                document.querySelector("#local-video .video-placeholder").style.display = "none";
                
                // Publish local tracks
                logDebug("Publishing local tracks");
                await client.publish(Object.values(localTracks));
                logDebug("Published local tracks successfully");
                
                // Update UI
                showStatus("Connected to video chat. Waiting for others to join...");
                document.getElementById('leave-call-btn').disabled = false;
                document.getElementById('call-controls').style.display = "flex";
                isCallActive = true;
                
                // Set up control buttons
                setupControlButtons();
                
            } catch (error) {
                logDebug(`Error starting call: ${error.message}`);
                showStatus(`Failed to start video call: ${error.message}`, true);
                document.getElementById('start-call-btn').disabled = false;
                
                // Clean up if there was an error
                if (localTracks.audioTrack) {
                    localTracks.audioTrack.close();
                }
                if (localTracks.videoTrack) {
                    localTracks.videoTrack.close();
                }
                await client?.leave();
            }
        }

        // Leave the call
        async function leaveCall() {
            try {
                // Close local tracks
                for (let trackName in localTracks) {
                    let track = localTracks[trackName];
                    if (track) {
                        track.stop();
                        track.close();
                        localTracks[trackName] = null;
                    }
                }
                
                // Reset remote users
                remoteUsers = {};
                
                // Leave the channel
                if (client) {
                    await client.leave();
                }
                
                // Update UI
                document.querySelector("#local-video .video-placeholder").style.display = "flex";
                document.querySelector("#remote-video .video-placeholder").style.display = "flex";
                document.getElementById('start-call-btn').disabled = false;
                document.getElementById('leave-call-btn').disabled = true;
                document.getElementById('call-controls').style.display = "none";
                showStatus("You have left the video chat.");
                isCallActive = false;
                
                logDebug("Successfully left the channel");
            } catch (error) {
                logDebug(`Error leaving call: ${error.message}`);
                showStatus(`Error leaving call: ${error.message}`, true);
            }
        }

        // Handle when a remote user publishes a track
        async function handleUserPublished(user, mediaType) {
            logDebug(`User ${user.uid} published ${mediaType} track`);
            
            // Subscribe to the remote user
            await client.subscribe(user, mediaType);
            
            // If this is a new user, add them to our tracking
            if (!remoteUsers[user.uid]) {
                remoteUsers[user.uid] = user;
            }
            
            // Handle the subscribed track
            if (mediaType === "video") {
                // Play the remote video
                user.videoTrack.play("remote-video");
                document.querySelector("#remote-video .video-placeholder").style.display = "none";
                showStatus("Another user has joined the call!");
            }
            
            if (mediaType === "audio") {
                // Play the remote audio
                user.audioTrack.play();
            }
        }

        // Handle when a remote user unpublishes a track
        function handleUserUnpublished(user, mediaType) {
            logDebug(`User ${user.uid} unpublished ${mediaType} track`);
            
            if (mediaType === "video") {
                // Show placeholder when video is unpublished
                document.querySelector("#remote-video .video-placeholder").style.display = "flex";
            }
        }

        // Handle when a user joins
        function handleUserJoined(user) {
            logDebug(`User ${user.uid} joined the channel`);
            showStatus("Another user has joined the channel!");
        }

        // Handle when a user leaves
        function handleUserLeft(user) {
            logDebug(`User ${user.uid} left the channel`);
            showStatus("The other participant has left the call.");
            
            // Remove the user from our tracking
            if (remoteUsers[user.uid]) {
                delete remoteUsers[user.uid];
            }
            
            // Show placeholder when user leaves
            document.querySelector("#remote-video .video-placeholder").style.display = "flex";
        }

        // Handle exceptions
        function handleException(event) {
            logDebug(`Exception: ${event.code}, ${event.msg}`);
            showStatus(`Video chat error: ${event.msg}`, true);
        }

        // Set up control buttons
        function setupControlButtons() {
            const toggleVideoBtn = document.getElementById('toggle-video');
            const toggleAudioBtn = document.getElementById('toggle-audio');
            const endCallBtn = document.getElementById('end-call');
            
            toggleVideoBtn.addEventListener('click', async () => {
                if (localTracks.videoTrack) {
                    if (toggleVideoBtn.classList.contains('active')) {
                        // Turn off video
                        await localTracks.videoTrack.setEnabled(false);
                        toggleVideoBtn.classList.remove('active');
                        toggleVideoBtn.innerHTML = '<i class="fas fa-video-slash"></i>';
                    } else {
                        // Turn on video
                        await localTracks.videoTrack.setEnabled(true);
                        toggleVideoBtn.classList.add('active');
                        toggleVideoBtn.innerHTML = '<i class="fas fa-video"></i>';
                    }
                }
            });
            
            toggleAudioBtn.addEventListener('click', async () => {
                if (localTracks.audioTrack) {
                    if (toggleAudioBtn.classList.contains('active')) {
                        // Mute audio
                        await localTracks.audioTrack.setEnabled(false);
                        toggleAudioBtn.classList.remove('active');
                        toggleAudioBtn.innerHTML = '<i class="fas fa-microphone-slash"></i>';
                    } else {
                        // Unmute audio
                        await localTracks.audioTrack.setEnabled(true);
                        toggleAudioBtn.classList.add('active');
                        toggleAudioBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                    }
                }
            });
            
            endCallBtn.addEventListener('click', () => {
                leaveCall();
            });
        }

        // Check for browser support
        function checkBrowserSupport() {
            if (!AgoraRTC.checkSystemRequirements()) {
                logDebug("Browser does not support Agora RTC");
                showStatus("Your browser does not fully support video chat functionality. Please use Chrome, Firefox, or Safari for the best experience.", true);
                return false;
            }
            return true;
        }

        // Initialize when page loads
        window.addEventListener('load', () => {
            logDebug("Page loaded");
            checkBrowserSupport();
            
            // Initialize Agora client
            initializeAgoraClient();
            
            // Warn before leaving if call is active
            window.addEventListener('beforeunload', (e) => {
                if (isCallActive) {
                    e.preventDefault();
                    e.returnValue = 'You are currently in a video call. Are you sure you want to leave?';
                }
            });
        });
    </script>
</body>
</html>
