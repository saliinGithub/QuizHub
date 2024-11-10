<?php
// Include the database connection file
include('../db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $contact_no  = $conn->real_escape_string($_POST['contact_no']);
    $qualification = $conn->real_escape_string($_POST['qualifications']);  // Changed variable to match
    $subject = $conn->real_escape_string($_POST['subject']);
    $password = $conn->real_escape_string($_POST['password']);
    $retypePassword = $conn->real_escape_string($_POST['retypePassword']);

    // Check if passwords match
    if ($password !== $retypePassword) {
        echo "Passwords do not match!";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert query
    $sql = "INSERT INTO teachers (username, email, first_name, last_name, contact_no, qualification, subject, password) 
            VALUES ('$username', '$email', '$firstName', '$lastName', '$contact_no', '$qualification', '$subject', '$hashed_password')";

    // Execute the query and check for success
    if ($conn->query($sql) === TRUE) {
        header("Location: ../index/index.html");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>
