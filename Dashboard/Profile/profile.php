<?php
// Include configuration and functions
require_once 'config.php';
require_once 'functions.php';

// Ensure user is logged in
requireLogin();

// Get user ID from session
$userId = $_SESSION['user_id'];

// Initialize variables for notifications
$notification = '';
$notificationType = '';

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine which form was submitted
    if (isset($_POST['personal_submit'])) {
        // Process personal information form
        $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
        $lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING);
        $dateOfBirth = filter_input(INPUT_POST, 'dateOfBirth', FILTER_SANITIZE_STRING);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
        $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_STRING);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
        
        // Validate inputs
        $errors = [];
        if (empty($firstName)) {
            $errors[] = "First name is required";
        }
        if (empty($lastName)) {
            $errors[] = "Last name is required";
        }
        if (empty($dateOfBirth)) {
            $errors[] = "Date of birth is required";
        }
        if (empty($gender)) {
            $errors[] = "Gender is required";
        }
        
        // If no errors, update user data
        if (empty($errors)) {
            $data = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'dateOfBirth' => $dateOfBirth,
                'gender' => $gender,
                'phoneNumber' => $phoneNumber,
                'address' => $address
            ];
            
            if (updatePersonalInfo($conn, $userId, $data)) {
                $notification = "Personal information updated successfully!";
                $notificationType = "success";
            } else {
                $notification = "Error updating personal information. Please try again.";
                $notificationType = "error";
            }
        } else {
            $notification = "Please fix the following errors: " . implode(", ", $errors);
            $notificationType = "error";
        }
    } elseif (isset($_POST['account_submit'])) {
        // Process account settings form
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        // Validate inputs
        $errors = [];
        if (empty($username) || strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = "Username must be between 3 and 30 characters";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }
        
        // If no errors, update user data
        if (empty($errors)) {
            $data = [
                'username' => $username,
                'email' => $email
            ];
            
            if (updateAccountSettings($conn, $userId, $data)) {
                $notification = "Account settings updated successfully!";
                $notificationType = "success";
            } else {
                $notification = "Error updating account settings. Please try again.";
                $notificationType = "error";
            }
        } else {
            $notification = "Please fix the following errors: " . implode(", ", $errors);
            $notificationType = "error";
        }
    } elseif (isset($_POST['security_submit'])) {
        // Process security form
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];
        
        // Validate inputs
        $errors = [];
        if (empty($currentPassword)) {
            $errors[] = "Current password is required";
        }
        if (empty($newPassword) || strlen($newPassword) < 8) {
            $errors[] = "New password must be at least 8 characters";
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }
        
        // If no errors, update password
        if (empty($errors)) {
            if (updatePassword($conn, $userId, $currentPassword, $newPassword)) {
                $notification = "Password updated successfully!";
                $notificationType = "success";
            } else {
                $notification = "Error updating password. Please check your current password and try again.";
                $notificationType = "error";
            }
        } else {
            $notification = "Please fix the following errors: " . implode(", ", $errors);
            $notificationType = "error";
        }
    }
}

// Get current user data
$user = getUserData($conn, $userId);

// If user data couldn't be retrieved, redirect to login
if (!$user) {
    header("Location: login.php");
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Mura Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <div class="logo">
                <div class="logo-container">
                    <img src="https://via.placeholder.com/40" alt="Mura Logo" class="mura-logo">
                    <h1>Mura</h1>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="lessons.php">
                            <i class="fas fa-book"></i>
                            <span>Lessons</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="achievements.php">
                            <i class="fas fa-trophy"></i>
                            <span>Achievements</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="help.php">
                            <i class="fas fa-question-circle"></i>
                            <span>Help</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="top-bar">
                <div class="left-side">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="left-stats">
                        <div class="level-indicator">
                            <div class="level-badge">
                                <span class="level-number"><?php echo htmlspecialchars($user['level']); ?></span>
                            </div>
                            <span class="level-label">Level</span>
                        </div>
                        <?php if (isset($user['language'])): ?>
                        <div class="language-indicator">
                            <span class="language-name"><?php echo htmlspecialchars($user['language']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($user['currentStreak'])): ?>
                        <div class="streak-counter">
                            <i class="fas fa-fire"></i>
                            <span class="streak-number"><?php echo htmlspecialchars($user['currentStreak']); ?></span>
                            <span class="streak-label">day streak</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="right-side">
                    <!-- You can add user profile dropdown here if needed -->
                </div>
            </div>
            
            <div class="content">
                <div class="header">
                    <h1>My Mura Profile</h1>
                    <p class="subtitle">View and manage your personal information</p>
                </div>
                
                <?php if (!empty($notification)): ?>
                <div class="notification notification-<?php echo $notificationType; ?> fade-in">
                    <?php echo $notification; ?>
                </div>
                <?php endif; ?>
                
                <div class="tabs">
                    <div class="tab active" data-tab="personal">Personal Information</div>
                    <div class="tab" data-tab="account">Account Settings</div>
                    <div class="tab" data-tab="security">Security</div>
                </div>
                
                <!-- Personal Information Tab -->
                <div id="personal" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Personal Information</h2>
                            <p class="card-description">Manage your personal details. This information will be displayed publicly.</p>
                        </div>
                        <form id="personalForm" method="POST" action="">
                            <div class="card-content">
                                <div class="form-row">
                                    <div class="form-col form-col-md-6">
                                        <div class="form-group">
                                            <label for="firstName">First Name</label>
                                            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter your first name" value="<?php echo htmlspecialchars($user['firstName'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-col form-col-md-6">
                                        <div class="form-group">
                                            <label for="lastName">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter your last name" value="<?php echo htmlspecialchars($user['lastName'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="dateOfBirth">Date of Birth</label>
                                    <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($user['dateOfBirth'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select your gender</option>
                                        <option value="male" <?php echo (isset($user['gender']) && $user['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo (isset($user['gender']) && $user['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo (isset($user['gender']) && $user['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                        <option value="prefer-not-to-say" <?php echo (isset($user['gender']) && $user['gender'] === 'prefer-not-to-say') ? 'selected' : ''; ?>>Prefer not to say</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phoneNumber">Phone Number</label>
                                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Enter your phone number" value="<?php echo htmlspecialchars($user['phoneNumber'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="4" placeholder="Enter your address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="personal_submit" class="btn btn-primary" id="personalSubmitBtn">
                                    <span class="btn-text">Save Changes</span>
                                    <span class="loading hidden">
                                        <svg class="loading-spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="32" stroke-dashoffset="8"></circle>
                                        </svg>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Account Settings Tab -->
                <div id="account" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Account Settings</h2>
                            <p class="card-description">Update your account information and email preferences.</p>
                        </div>
                        <form id="accountForm" method="POST" action="">
                            <div class="card-content">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                                    <small class="form-text">This is your public display name. It can only contain letters, numbers, and underscores.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                    <small class="form-text">We'll never share your email with anyone else.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="account_submit" class="btn btn-primary" id="accountSubmitBtn">
                                    <span class="btn-text">Save Changes</span>
                                    <span class="loading hidden">
                                        <svg class="loading-spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="32" stroke-dashoffset="8"></circle>
                                        </svg>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Security Tab -->
                <div id="security" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Security</h2>
                            <p class="card-description">Update your password and manage your account security.</p>
                        </div>
                        <form id="securityForm" method="POST" action="">
                            <div class="card-content">
                                <div class="form-group">
                                    <label for="currentPassword">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword" name="currentPassword" placeholder="Enter your current password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Enter your new password" required>
                                    <small class="form-text">Password must be at least 8 characters long.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirmPassword">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your new password" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="security_submit" class="btn btn-primary" id="securitySubmitBtn">
                                    <span class="btn-text">Update Password</span>
                                    <span class="loading hidden">
                                        <svg class="loading-spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="32" stroke-dashoffset="8"></circle>
                                        </svg>
                                        Updating...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="profile.js"></script>
</body>
</html>
