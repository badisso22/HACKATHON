<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../Configurations/db.php';
function requireLogin() {
  session_start(); // Start the session to access user data
  if (!isset($_SESSION['user_id'])) { // Check if the user is logged in
      header("Location: login.php"); // Redirect to login page if not logged in
      exit(); // Stop script execution
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
    <a href="../Dashboard/dashboard.php" class="back-btn" id="back-button">Back</a>
    <h2>Video Chat Room</h2>
    <div id="videos">
      <div id="local-video"></div>
      <div id="remote-video"></div>
    </div>
    <div class="button-container">
      <button onclick="startCall()">Start Call</button>
      <button onclick="leaveCall()">Leave Call</button>
    </div>
  </div>

  <script>
    const APP_ID = "fca49702e5d64960a1cfd5dc860decc1"; // Replace with your real Agora App ID
    const CHANNEL = "videoChat";      // You can generate dynamic names too
    const TOKEN = null;               // Use null for no-token (dev mode)

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
