<?php
// Include the database connection file
include('../db_connect.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $username = $conn->real_escape_string($_POST['username']);
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $retypePassword = $conn->real_escape_string($_POST['retypePassword']);

    // Validate password match
    if ($password !== $retypePassword) {
        echo "Passwords do not match!";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO students (username, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");

        if ($stmt === false) {
            die("Error in SQL query: " . $conn->error);
        }

        $stmt->bind_param("sssss", $username, $firstName, $lastName, $email, $hashedPassword);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Registration successful!";
            header("Location: ../index/index.html");  // Redirect to the home page or a success page
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>
