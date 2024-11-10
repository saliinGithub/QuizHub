<?php
session_start(); // Start session
require '../db_connect.php'; // Include database connection

// Check if the teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login/teacher_login.html");
    exit();
}

// Get the teacher's ID from the session
$teacher_id = $_SESSION['teacher_id'];

// Get the form data from the POST request
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$contact_no = trim($_POST['contact_no']);
$qualification = trim($_POST['qualification']);
$subject = trim($_POST['subject']);

// Prepare SQL statement to update teacher profile data
$sql = "UPDATE teachers SET username = ?, email = ?, first_name = ?, last_name = ?, contact_no = ?, qualification = ?, subject = ? WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssisi", $username, $email, $first_name, $last_name, $contact_no, $qualification, $subject, $teacher_id);

// Execute the statement
if ($stmt->execute()) {
    // Redirect back to the profile page after successful update
    header("Location: profiles.php");
} else {
    // Redirect back to the profile page on failure
    header("Location: profiles.php");
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
