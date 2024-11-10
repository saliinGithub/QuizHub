<?php
session_start(); // Start session at the very beginning
require '../db_connect.php';

// Check if the teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login/teacher_login.html");
    exit();
}

// Get the teacher's ID from the session
$teacher_id = $_SESSION['teacher_id'];

// Prepare SQL statement to fetch teacher profile data
$sql = "SELECT username, email, first_name, last_name, contact_no, qualification, subject FROM teachers WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the teacher's data
    $teacher = $result->fetch_assoc();
} else {
    echo "Profile information not found.";
    exit();
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            height: 100vh; /* Full height */
            display: flex;
            justify-content: center; /* Centering content */
            align-items: center; /* Centering content vertically */
        }

        .profile-container {
            width: 100%; /* Full width */
            max-width: 800px; /* Maximum width */
            margin: 20px;
            padding: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .two-column-layout {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .left-column, .right-column {
            width: 48%; /* Adjusted width to have space in between */
        }

        .left-column p, .right-column p {
            background-color: #e9ecef;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }

        .contact-number {
            text-align: center; /* Centered text */
            margin: 20px 90px; /* Margin for spacing */
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
        }

        .button-container {
            display: flex;
            justify-content: space-between; /* Space between buttons */
            margin-top: 20px;
        }

        button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            flex: 1; /* Make buttons take equal width */
            margin: 0 10px; /* Margin for spacing between buttons */
        }

        button:hover {
            background-color: #0056b3;
        }

        .edit-form {
            display: none; /* Hidden by default */
            margin-top: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .edit-form input {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Teacher Profile</h1>
        <div class="two-column-layout">
            <div class="left-column">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($teacher['username']); ?></p>
                <p><strong>First Name:</strong> <?php echo htmlspecialchars($teacher['first_name']); ?></p>
                <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($teacher['contact_no']); ?></p>
            </div>
            <div class="right-column">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($teacher['email']); ?></p>
                <p><strong>Last Name:</strong> <?php echo htmlspecialchars($teacher['last_name']); ?></p>
               <p><strong>Qualification:</strong> <?php echo htmlspecialchars($teacher['qualification']); ?></p>
            </div>
        </div>
        
        <div class="contact-number">
        <strong>Subject:</strong> <?php echo htmlspecialchars($teacher['subject']); ?>
            
        </div>

        <div class="button-container">
            <button id="editProfileButton">Edit Profile</button>
            <button onclick="window.location.href='teacherdashboard.html'">Return Dashboard</button>
        </div>
        
        <div class="edit-form" id="editForm">
            <h2>Edit Profile</h2>
            <form action="update_profile.php" method="POST">
                <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">
                <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($teacher['username']); ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                <input type="text" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required>
                <input type="text" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required>
                <input type="text" name="contact_no" placeholder="Contact Number" value="<?php echo htmlspecialchars($teacher['contact_no']); ?>" required>
                <input type="text" name="qualification" placeholder="Qualification" value="<?php echo htmlspecialchars($teacher['qualification']); ?>" required>
                <input type="text" name="subject" placeholder="Subject" value="<?php echo htmlspecialchars($teacher['subject']); ?>" required>
                <button type="submit">Update Profile</button>
            </form>
        </div>
    </div>
    <script>
        // JavaScript to toggle the edit form visibility
        document.getElementById('editProfileButton').addEventListener('click', function() {
            const editForm = document.getElementById('editForm');
            editForm.style.display = editForm.style.display === 'block' ? 'none' : 'block';
        });

        // JavaScript for any future enhancements or dynamic functionalities
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Profile page loaded.");
        });
    </script>
</body>
</html>
