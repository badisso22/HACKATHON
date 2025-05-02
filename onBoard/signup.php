<?php
// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
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
  <title>Sign Up – MURA</title>
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
      <form class="auth-form" id="signupForm" action="signup_process.php" method="POST">
        <div class="form-header">
          <h2>Create Account</h2>
          <p>Start your language journey today</p>
        </div>

        <?php if (!empty($error_message)) : ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="form-group">
          <label for="firstname">First Name</label>
          <input type="text" id="firstname" name="firstname" required placeholder="Enter your first name">
        </div>

        <div class="form-group">
          <label for="lastname">Last Name</label>
          <input type="text" id="lastname" name="lastname" required placeholder="Enter your last name">
        </div>

        <div class="form-group">
          <label for="dob">Date of birth</label>
          <input type="date" id="dob" name="dob" required>
        </div>

        <div class="form-group">
          <label for="gender">Gender</label>
          <select id="gender" name="gender" required>
              <option value="">Select gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
          </select>
        </div>

        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
        </div>

        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" required placeholder="Enter your address">
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="your@email.com">
        </div>

        <div class="form-group">
          <label for="password">Create Password</label>
          <input type="password" id="password" name="password" required placeholder="Minimum 8 characters">
          <span class="password-toggle" id="passwordToggle">👁️</span>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">Sign Up</button>

        <div class="auth-link">
          Already have an account? <a href="signin.php">Sign In</a>
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