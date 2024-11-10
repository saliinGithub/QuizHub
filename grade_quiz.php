<?php
// Start the session
session_start();

// Include the database connection
include '../db_connect.php';

// Initialize counters
$correctCount = 0;
$incorrectCount = 0;
$unansweredCount = 0;
$questionsWithAnswers = [];

// Loop through submitted answers
foreach ($_POST as $key => $value) {
    if (strpos($key, 'question_') === 0) {
        $question_id = (int)str_replace('question_', '', $key);
        $userAnswerOption = trim($value);

        // Fetch question from the database
        $query = "SELECT question_text, option_a, option_b, option_c, option_d, correct_answer 
                  FROM questions WHERE question_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
        $stmt->close();

        if (!$question) continue;

        $options = [
            'A' => $question['option_a'],
            'B' => $question['option_b'],
            'C' => $question['option_c'],
            'D' => $question['option_d']
        ];

        // Determine the user's answer and check against the correct answer
        if (isset($options[strtoupper($userAnswerOption)])) {
            $userAnswerText = $options[strtoupper($userAnswerOption)];
            $correctAnswerText = trim($question['correct_answer']);

            if ($userAnswerText === $correctAnswerText) {
                $correctCount++;
            } else {
                $incorrectCount++;
            }
        } else {
            $unansweredCount++;
            $userAnswerText = 'Not answered';
        }

        // Store question and answers
        $questionsWithAnswers[] = [
            'question_text' => $question['question_text'],
            'user_answer' => $userAnswerText,
            'correct_answer' => trim($question['correct_answer'])
        ];
    }
}

$totalQuestions = $correctCount + $incorrectCount;

// Save the results to the database
if (isset($_SESSION['student_id'])) {  
    $studentId = $_SESSION['student_id'];

    // Insert or update total attempts, correct, incorrect, and unanswered
    $query = "INSERT INTO student_dashboard_totals (student_id, total_attempts, total_correct, total_incorrect, total_unanswered) 
              VALUES (?, 1, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE total_attempts = total_attempts + 1, 
                                      total_correct = total_correct + ?, 
                                      total_incorrect = total_incorrect + ?, 
                                      total_unanswered = total_unanswered + ?";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("MySQL prepare failed: " . htmlspecialchars($conn->error));
    }

    // Bind parameters correctly
    $stmt->bind_param("iiiiiii", $studentId, $correctCount, $incorrectCount, $unansweredCount, $correctCount, $incorrectCount, $unansweredCount);

    if (!$stmt->execute()) {
        die("Execute failed: " . htmlspecialchars($stmt->error));
    }

    $stmt->close();

    // Insert the quiz results into quiz_results table
    if (isset($_POST['quiz_id'])) {
        $quizId = (int)$_POST['quiz_id'];
        $status = ($correctCount >= 0.5 * $totalQuestions) ? 'Pass' : 'Fail';

        // Insert quiz results into the database
        $query = "INSERT INTO quiz_results (student_id, quiz_id, total_attempts, correct_answers, incorrect_answers, status, taken_at)
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("MySQL prepare failed: " . htmlspecialchars($conn->error));
        }

        // Bind the parameters for the quiz results
        $stmt->bind_param("iiiii", $studentId, $quizId, $totalQuestions, $correctCount, $incorrectCount);

        if (!$stmt->execute()) {
            die("Execute failed: " . htmlspecialchars($stmt->error));
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #007bff;
            font-size: 2rem;
            margin: 0;
        }
        .dashboard-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .dashboard-btn:hover {
            background-color: #0056b3;
        }
        .summary {
            text-align: center;
            margin: 30px 0;
        }
        .summary p {
            font-size: 1.2rem;
        }
        .pass-status {
            font-size: 1.4rem;
            font-weight: bold;
            color: <?= $totalQuestions > 0 && $correctCount / $totalQuestions >= 0.5 ? 'green' : 'red' ?>;
        }
        .question {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f1f3f5;
            border-radius: 8px;
        }
        .question h4 {
            margin: 0 0 5px;
        }
        .user-answer {
            color: red;
            font-weight: bold;
        }
        .correct-answer {
            color: green;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quiz Results</h1>
            <a href="studentdashboard.php" class="dashboard-btn">Return to Dashboard</a>
        </div>
        
        <h2>Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Total Attempts</th>
                    <th>Correct Answers</th>
                    <th>Incorrect Answers</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $totalQuestions ?></td>
                    <td><?= $correctCount ?></td>
                    <td><?= $incorrectCount ?></td>
                    <td class="pass-status"><?= $totalQuestions > 0 && $correctCount / $totalQuestions >= 0.5 ? 'Pass' : 'Fail' ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Question Review</h2>
        <?php foreach ($questionsWithAnswers as $question): ?>
            <div class="question">
                <h4><?= htmlspecialchars($question['question_text']) ?></h4>
                <p class="user-answer">Your Answer: <?= htmlspecialchars($question['user_answer']) ?></p>
                <p class="correct-answer">Correct Answer: <?= htmlspecialchars($question['correct_answer']) ?></p>
            </div>
        <?php endforeach; ?>
        
    </div>
</body>
</html>
