<?php
session_start();
require_once '../Configurations/db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to check_onboarding to determine next step
    header("Location: check_onboarding.php");
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT user_ID, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_regenerate_id();
                $_SESSION['user_id'] = $user['user_ID'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect to check onboarding status
                header("Location: check_onboarding.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
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
  <link rel="stylesheet" href="../css/auth.css" />
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
      <form class="auth-form" id="signinForm" method="POST" action="">
        <div class="form-header">
          <h2>Welcome Back</h2>
          <p>Continue your language journey</p>
        </div>

        <?php if (!empty($error)): ?>
          <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
          </div>
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
          <a href="forgot-password.php">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">Sign In</button>

        <div class="auth-link">
          Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/auth.js"></script>
</body>
</html>
