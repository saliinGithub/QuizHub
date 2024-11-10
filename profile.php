<?php
session_start();
include '../db_connect.php';

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student information from the database
$sql = "SELECT username, email, first_name, last_name, profile_pic FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "Student profile not found!";
    exit();
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_first_name = $_POST['first_name'];
    $new_last_name = $_POST['last_name'];

    // Profile picture upload handling
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_pic']['tmp_name'];
        $file_name = basename($_FILES['profile_pic']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_exts)) {
            $upload_dir = "../uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $new_file_path = $upload_dir . "profile_" . $student_id . "." . $file_ext;

            if (move_uploaded_file($file_tmp_path, $new_file_path)) {
                $profile_pic = "profile_" . $student_id . "." . $file_ext;
            } else {
                echo "Failed to upload profile picture!";
                $profile_pic = $student['profile_pic'];
            }
        } else {
            echo "Invalid file type!";
            $profile_pic = $student['profile_pic'];
        }
    } else {
        $profile_pic = $student['profile_pic'];
    }

    $update_sql = "UPDATE students SET username = ?, email = ?, first_name = ?, last_name = ?, profile_pic = ? WHERE student_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssi", $new_username, $new_email, $new_first_name, $new_last_name, $profile_pic, $student_id);

    if ($update_stmt->execute()) {
        header("Location: profile.php");
        exit();
    } else {
        echo "Error updating profile!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            text-align: center;
            position: relative;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        .profile-container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .cover-photo {
            width: 100%;
            height: 150px;
            background: url('../assets/cover.jpg') center/cover no-repeat;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            margin-bottom: -30px;
            position: relative;
            z-index: 1;
        }
        .profile-pic-container {
            position: relative;
            margin: -60px auto 20px; /* Center the profile picture */
            cursor: pointer;
            display: inline-block;
            z-index: 2;
        }
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        h2 {
            font-size: 26px;
            margin: 10px 0;
            color: #333;
        }
        p {
            margin: 5px 0;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            margin: 10px 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .edit-btn {
            background-color: #007bff;
            color: #fff;
        }
        .edit-btn:hover {
            background-color: #0056b3;
        }
        .save-btn {
            background-color: #28a745;
            color: #fff;
        }
        .save-btn:hover {
            background-color: #218838;
        }
        .cancel-btn {
            background-color: #dc3545;
            color: #fff;
        }
        .cancel-btn:hover {
            background-color: #c82333;
        }
        .edit-form {
            display: none;
            margin-top: 20px;
            text-align: left;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .back-to-dashboard {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            margin-top: 15px;
            display: inline-block;
        }
        .back-to-dashboard:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function toggleEditForm() {
            const form = document.querySelector('.edit-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function uploadProfilePic() {
            document.getElementById("profile_pic").click();
        }
    </script>
</head>
<body>

<header>
    <h1>Student Profile</h1>
</header>

<div class="profile-container">
    <div class="cover-photo"></div>
    <div class="profile-pic-container" onclick="uploadProfilePic()">
        <img src="<?php echo '../uploads/' . htmlspecialchars($student['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
    </div>
    
    <h2><?php echo htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?></h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($student['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
    <button class="btn edit-btn" onclick="toggleEditForm()">Edit Profile</button>

    <form class="edit-form" method="POST" action="profile.php" enctype="multipart/form-data">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($student['username']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>

        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>

        <input type="file" name="profile_pic" id="profile_pic" style="display:none;" accept="image/*" onchange="document.querySelector('.profile-pic').src = window.URL.createObjectURL(this.files[0])">
        
        <button type="submit" class="btn save-btn">Save Changes</button>
        <button type="button" class="btn cancel-btn" onclick="toggleEditForm()">Cancel</button>
    </form>

    <a href="studentdashboard.php" class="back-to-dashboard">Back to Dashboard</a>
</div>

</body>
</html>
