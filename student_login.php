<?php
session_start();
include '../db_connect.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SQL query to find student by username
    $sql = "SELECT * FROM students WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['student_id'] = $row['student_id']; // Ensure you use the correct column name
            $_SESSION['student_username'] = $row['username'];
            echo "Student logged in successfully!";
            // Redirect to student dashboard
            header("Location: ../studentdashboard/studentdashboard.php");
            exit(); // Make sure to exit after redirection
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "Student not found!";
    }
}
?>
