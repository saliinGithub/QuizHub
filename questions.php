<?php
// Include the database connection
include('../db_connect.php');

// Check if a quiz ID is provided, if not, redirect to a safe page or show an error
if (!isset($_GET['quiz_id']) || empty($_GET['quiz_id'])) {
    echo "Quiz ID not specified.";
    exit;
}

$quiz_id = $_GET['quiz_id'];

// Handle form submissions for adding, updating, or deleting questions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add a new question
    if (isset($_POST['add_question'])) {
        $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
        $option_a = mysqli_real_escape_string($conn, $_POST['option_a']);
        $option_b = mysqli_real_escape_string($conn, $_POST['option_b']);
        $option_c = mysqli_real_escape_string($conn, $_POST['option_c']);
        $option_d = mysqli_real_escape_string($conn, $_POST['option_d']);
        $correct_answer = mysqli_real_escape_string($conn, $_POST['correct_answer']);

        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer);
        
        if ($stmt->execute()) {
            echo "<script>alert('Question added successfully.');</script>";
        } else {
            echo "<script>alert('Error adding question: " . $stmt->error . "');</script>"; // Improved error handling
        }
        $stmt->close();
    }

    // Update an existing question
    if (isset($_POST['update_question'])) {
        $question_id = $_POST['question_id'];
        $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
        $option_a = mysqli_real_escape_string($conn, $_POST['option_a']);
        $option_b = mysqli_real_escape_string($conn, $_POST['option_b']);
        $option_c = mysqli_real_escape_string($conn, $_POST['option_c']);
        $option_d = mysqli_real_escape_string($conn, $_POST['option_d']);
        $correct_answer = mysqli_real_escape_string($conn, $_POST['correct_answer']);

        $stmt = $conn->prepare("UPDATE questions SET question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_answer=? WHERE question_id=?");
        $stmt->bind_param("ssssssi", $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $question_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Question updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating question: " . $stmt->error . "');</script>"; // Improved error handling
        }
        $stmt->close();
    }

    // Delete a question
    if (isset($_POST['delete_question'])) {
        $question_id = $_POST['question_id'];

        $stmt = $conn->prepare("DELETE FROM questions WHERE question_id=?");
        $stmt->bind_param("i", $question_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Question deleted successfully.');</script>";
        } else {
            echo "<script>alert('Error deleting question: " . $stmt->error . "');</script>"; // Improved error handling
        }
        $stmt->close();
    }
}

// Fetch all questions for the selected quiz
$query = "SELECT * FROM questions WHERE quiz_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error); // Error handling
}
$stmt->bind_param("i", $quiz_id);
if (!$stmt->execute()) {
    die("Query execution failed: " . $stmt->error); // Error handling
}
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc;
        }
        .container {
            margin-top: 50px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: white;
            padding: 20px;
        }
        h2, h4 {
            color: #343a40;
        }
        .btn-dashboard {
            background-color: #007bff;
            color: white;
            margin-bottom: 20px;
        }
        .btn-dashboard:hover {
            background-color: #0056b3;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Manage Questions for Quiz ID: <?php echo htmlspecialchars($quiz_id); ?></h2>

        <!-- Dashboard Button -->
        <a href="teacherdashboard.html" class="btn btn-dashboard">Go to Dashboard</a>

        <!-- Form to add a new question -->
        <form method="post" class="mb-5">
            <h4>Add a new question</h4>
            <div class="form-group">
                <label for="question_text">Question Text</label>
                <input type="text" name="question_text" id="question_text" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="option_a">Option A</label>
                <input type="text" name="option_a" id="option_a" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="option_b">Option B</label>
                <input type="text" name="option_b" id="option_b" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="option_c">Option C</label>
                <input type="text" name="option_c" id="option_c" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="option_d">Option D</label>
                <input type="text" name="option_d" id="option_d" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="correct_answer">Correct Answer</label>
                <input type="text" name="correct_answer" id="correct_answer" class="form-control" required>
            </div>
            <button type="submit" name="add_question" class="btn btn-primary">Add Question</button>
        </form>

        <!-- Display all questions in a table -->
        <h4>Existing Questions</h4>
        <?php if (count($questions) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Options</th>
                        <th>Correct Answer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                            <td>
                                A: <?php echo htmlspecialchars($question['option_a']); ?><br>
                                B: <?php echo htmlspecialchars($question['option_b']); ?><br>
                                C: <?php echo htmlspecialchars($question['option_c']); ?><br>
                                D: <?php echo htmlspecialchars($question['option_d']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($question['correct_answer']); ?></td>
                            <td>
                                <!-- Button to open modal for updating question -->
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#updateModal<?php echo $question['question_id']; ?>">Update</button>

                                <!-- Form to delete the question -->
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                    <button type="submit" name="delete_question" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal for updating question -->
                        <div class="modal fade" id="updateModal<?php echo $question['question_id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Question</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post">
                                            <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                            <div class="form-group">
                                                <label for="question_text">Question Text</label>
                                                <input type="text" name="question_text" class="form-control" value="<?php echo htmlspecialchars($question['question_text']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="option_a">Option A</label>
                                                <input type="text" name="option_a" class="form-control" value="<?php echo htmlspecialchars($question['option_a']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="option_b">Option B</label>
                                                <input type="text" name="option_b" class="form-control" value="<?php echo htmlspecialchars($question['option_b']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="option_c">Option C</label>
                                                <input type="text" name="option_c" class="form-control" value="<?php echo htmlspecialchars($question['option_c']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="option_d">Option D</label>
                                                <input type="text" name="option_d" class="form-control" value="<?php echo htmlspecialchars($question['option_d']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="correct_answer">Correct Answer</label>
                                                <input type="text" name="correct_answer" class="form-control" value="<?php echo htmlspecialchars($question['correct_answer']); ?>" required>
                                            </div>
                                            <button type="submit" name="update_question" class="btn btn-primary">Update Question</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No questions found for this quiz.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
