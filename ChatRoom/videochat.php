<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../Configurations/db.php';
function requireLogin() {
  session_start(); 
  if (!isset($_SESSION['user_id'])) { 
      header("Location: login.php"); // Redirect to login page if not logged in
      exit(); 
  }
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Video Chat</title>
  <script src="https://cdn.agora.io/sdk/web/AgoraRTC_N.js"></script>
  <link rel="stylesheet" href="../css/videochat.css">
</head>
<body>
  <div class="video-chat-container">
    
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
    document.getElementById("back-button").href = "../Dashboard/dashboard.php"; // Set your desired link here
  </script>
</body>
</html>
