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
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($firstname) || empty($lastname) || empty($dob) || empty($gender) || 
        empty($phone) || empty($address) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_ID FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
          // Check if username already exists
        $stmt = $conn->prepare("SELECT user_ID FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already taken. Please choose a different one.";
        }


        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email or sign in.";
        } else {
            // Begin transaction
            $conn->begin_transaction();

            try {
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                $stmt->execute();
                
                // Get the new user ID
                $user_id = $conn->insert_id;
                
                // Insert into learner table
                $stmt = $conn->prepare("INSERT INTO learner (user_ID, first_name, last_name, date_of_birth, phone_number, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssss", $user_id, $firstname, $lastname, $dob, $phone, $gender, $address);
                $stmt->execute();
                
                // Create user_onboarding record
                $stmt = $conn->prepare("INSERT INTO user_onboarding (user_ID) VALUES (?)");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                
                // Redirect to onboarding
                header("Location: check_onboarding.php");
                exit();
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
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
      <form class="auth-form" id="signupForm" method="POST" action="">
        <div class="form-header">
          <h2>Create Account</h2>
          <p>Start your language journey today</p>
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
          <label for="username">username</label>
          <input type="text" id="username" name="username" required placeholder="Enter your username">
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

  <script src="../js/auth.js"></script>
</body>
</html>
