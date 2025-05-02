<?php
session_start();
require_once '../Configurations/db.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_ID FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // In a real application, you would:
            // 1. Generate a unique token
            // 2. Store it in the database with an expiration time
            // 3. Send an email with a reset link containing the token
            
            // For this demo, we'll just show a success message
            $success = "If an account exists with this email, you will receive password reset instructions.";
        } else {
            // Don't reveal that the email doesn't exist for security reasons
            $success = "If an account exists with this email, you will receive password reset instructions.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password – MURA</title>
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
      <form class="auth-form" id="forgotPasswordForm" method="POST" action="">
        <div class="form-header">
          <h2>Forgot Password</h2>
          <p>Enter your email to reset your password</p>
        </div>

        <?php if (!empty($error)): ?>
          <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div class="success-message">
            <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="your@email.com">
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">Reset Password</button>

        <div class="auth-link">
          Remember your password? <a href="signin.php">Sign In</a>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/auth.js"></script>
</body>
</html>
