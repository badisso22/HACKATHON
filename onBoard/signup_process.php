<?php
// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $firstname = isset($_POST['firstname']) ? $conn->real_escape_string($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? $conn->real_escape_string($_POST['lastname']) : '';
        $dob = isset($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : '';
        $gender = isset($_POST['gender']) ? $conn->real_escape_string($_POST['gender']) : '';
        $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
        $address = isset($_POST['address']) ? $conn->real_escape_string($_POST['address']) : '';
        $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
        $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : ''; // Hash the password

        // Sanitize phone number - remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Generate a username from email (before the @ symbol)
        $username = strstr($email, '@', true);
        if (!$username) {
            $username = "user" . time();
        }

        // Begin transaction
        $conn->begin_transaction();

        // First insert into users table
        // After successful user creation in signup_process.php
        $sql_users = "INSERT INTO users (username, email, password, onboarding_complete) VALUES (?, ?, ?, FALSE)";
        $stmt_users = $conn->prepare($sql_users);
        if (!$stmt_users) {
            throw new Exception("Prepare failed for users table: " . $conn->error);
        }
        
        $stmt_users->bind_param("sss", $username, $email, $password);
        
        if (!$stmt_users->execute()) {
            throw new Exception("Error inserting user: " . $stmt_users->error);
        }
        
        // Get the newly created user_ID
        $user_id = $conn->insert_id;
        
        // Then insert into learner table
        $sql_learner = "INSERT INTO learner (user_ID, first_name, last_name, date_of_birth, phone_number, gender, address) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_learner = $conn->prepare($sql_learner);
        if (!$stmt_learner) {
            throw new Exception("Prepare failed for learner table: " . $conn->error);
        }
        
        // Convert phone to integer with safety check
        $phone_int = (int)$phone;
        
        $stmt_learner->bind_param("issssss", $user_id, $firstname, $lastname, $dob, $phone, $gender, $address);
        
        if (!$stmt_learner->execute()) {
            throw new Exception("Error inserting learner: " . $stmt_learner->error);
        }
        
        // If we got here, commit the transaction
        $conn->commit();
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['firstname'] = $firstname;
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
        
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        if (isset($conn) && $conn->ping()) {
            $conn->rollback();
        }
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: signup.php");
        exit();
    }
} else {
    // Not a POST request, redirect to signup page
    header("Location: signup.php");
    exit();
}
?>