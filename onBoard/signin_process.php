<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        $sql = "SELECT u.user_ID, u.username, u.password, l.first_name 
                FROM users u 
                LEFT JOIN learner l ON u.user_ID = l.user_ID 
                WHERE u.email = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['user_ID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['firstname'] = $user['first_name'] ?? $user['username'];
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Password is incorrect
                $_SESSION['error'] = "Invalid email or password";
                header("Location: signin.php");
                exit();
            }
        } else {
            // User not found
            $_SESSION['error'] = "Invalid email or password";
            header("Location: signin.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Login failed: " . $e->getMessage();
        header("Location: signin.php");
        exit();
    }
} else {
    // Not a POST request, redirect to signin page
    header("Location: signin.php");
    exit();
}
?>