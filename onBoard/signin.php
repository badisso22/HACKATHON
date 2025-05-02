<?php
// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    require_once 'db.php';

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT onboarding_complete FROM users WHERE user_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['onboarding_complete']) {
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: check_onboarding.php");
            exit();
        }
    } else {
        // If user ID exists in session but not in the database, clear session and redirect to signin
        session_unset();
        session_destroy();
        header("Location: signin.php");
        exit();
    }
}

// Check for error messages
$error_message = "";
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In – MURA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="signup.css" />
  <style>
    .error-message {
      color: #ff3333;
      text-align: center;
      margin-bottom: 15px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="background-words-container" id="backgroundWords">
  </div>

  <div class="page-container">
    <div class="logo-container">
      <h1 class="logo">MURA</h1>
      <p class="tagline">Language learning reimagined</p>
    </div>

    <div class="auth-container">
      <form class="auth-form" id="signinForm" action="signin_process.php" method="POST">
        <div class="form-header">
          <h2>Welcome Back</h2>
          <p>Continue your language journey</p>
        </div>

        <?php if (!empty($error_message)) : ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="your@email.com">
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter your password">
          <span class="password-toggle" id="passwordToggle">👁️</span>
        </div>

        <div class="forgot-password">
          <a href="#">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">Sign In</button>

        <div class="auth-link">
          Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('passwordToggle').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
    });
  </script>
</body>
</html>