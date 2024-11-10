<?php
session_start();
require '../db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare and execute SQL statement
    $sql = "SELECT * FROM teachers WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Store teacher ID and username in session
            $_SESSION['teacher_id'] = $row['teacher_id'];
            $_SESSION['teacher_username'] = $row['username'];
            
            // Regenerate session ID
            session_regenerate_id(true);

            // Redirect to teacher dashboard
            header("Location: ../teacherdashboard/teacherdashboard.html");
            exit();
        } else {
            // General feedback
            echo "Invalid username or password!";
        }
    } else {
        // General feedback
        echo "Invalid username or password!";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
