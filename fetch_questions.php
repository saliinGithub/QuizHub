<?php
include '../db_connect.php'; 

// Fetch all quiz subjects
$subjects = [
    1 => 'Operating System',
    2 => 'Database Operating System',
    3 => 'Scripting Language',
    4 => 'Numerical Method',
    5 => 'Software Engineering'
];

if (isset($_POST['subject']) && !empty($_POST['subject'])) {
    $quiz_id = (int)$_POST['subject'];

    // Fetch questions randomly from the database
    $query = "SELECT question_id, question_text, option_a, option_b, option_c, option_d, correct_answer 
              FROM questions 
              WHERE quiz_id = ? 
              ORDER BY RAND()"; 

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }

    $stmt->close();
    $conn->close();
} else {
    die("No subject selected.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            background-color: #f5f5f5; 
            position: relative;
        }

        #quiz-container {
            width: 90%;
            max-width: 800px;
            background-color: rgba(255, 255, 255, 0.9); 
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            margin: auto;
            top: 50%;
            transform: translateY(-50%);
            animation: fadeIn 0.5s ease-in-out;
            position: relative;
            z-index: 2;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
            color: #007BFF; 
        }

        .timer {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .question {
            display: none;
        }

        .question.active {
            display: block;
        }

        input[type="radio"] {
            margin-right: 10px;
            transform: scale(1.2);
        }

        .option-box {
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid #007BFF;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            transition: background 0.3s, border 0.3s;
        }

        .option-box:hover {
            background: rgba(0, 123, 255, 0.1);
            border: 1px solid #0056b3;
        }

        #submit-btn, #next-btn, #back-btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            font-size: 16px;
            margin-right: 10px;
        }

        #submit-btn:hover, #next-btn:hover, #back-btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div id="quiz-container">
        <h1>Quiz on <?= $subjects[$quiz_id] ?></h1> 
        <div class="timer">
            <i class="fas fa-clock"></i> 
            <span id="timer">10:00</span>
        </div>

        <form id="quizForm" method="POST" action="grade_quiz.php">
    <?php foreach ($questions as $index => $question): ?>
        <div class="question <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
            <h4 style="font-size: 1.5rem;"><?= $question['question_text'] ?></h4>
            <div class="option-box"><label><input type="radio" name="question_<?= $question['question_id'] ?>" value="A"> A: <?= $question['option_a'] ?></label></div>
            <div class="option-box"><label><input type="radio" name="question_<?= $question['question_id'] ?>" value="B"> B: <?= $question['option_b'] ?></label></div>
            <div class="option-box"><label><input type="radio" name="question_<?= $question['question_id'] ?>" value="C"> C: <?= $question['option_c'] ?></label></div>
            <div class="option-box"><label><input type="radio" name="question_<?= $question['question_id'] ?>" value="D"> D: <?= $question['option_d'] ?></label></div>
            <input type="hidden" name="correct_<?= $question['question_id'] ?>" value="<?= $question['correct_answer'] ?>"> <!-- Hidden input for the correct answer -->
        </div>
    <?php endforeach; ?>
    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
        <button type="button" id="back-btn" style="display: none;">Back</button>
        <button type="button" id="next-btn">Next</button>
        <button type="submit" id="submit-btn" style="display: none;">Submit</button>
    </div>
</form>

    </div>

    <script>
        const questions = document.querySelectorAll('.question');
        const nextButton = document.getElementById('next-btn');
        const backButton = document.getElementById('back-btn');
        const submitButton = document.getElementById('submit-btn');
        const timerElement = document.getElementById('timer');

        let currentQuestionIndex = 0;
        let timeLeft = 600; // 10 minutes in seconds

        function startTimer() {
            const timerInterval = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert('Time is up!');
                    document.getElementById('quizForm').submit();
                } else {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                    timeLeft--;
                }
            }, 1000);
        }

        nextButton.addEventListener('click', () => {
            const currentQuestion = questions[currentQuestionIndex];
            const selectedOption = currentQuestion.querySelector('input[type="radio"]:checked');

            if (!selectedOption) {
                alert('Please select an answer before proceeding to the next question.');
                return;
            }

            currentQuestion.classList.remove('active');
            currentQuestionIndex++;

            if (currentQuestionIndex < questions.length) {
                questions[currentQuestionIndex].classList.add('active');
                backButton.style.display = currentQuestionIndex > 0 ? 'block' : 'none';
                submitButton.style.display = currentQuestionIndex === questions.length - 1 ? 'block' : 'none';
            }
        });

        backButton.addEventListener('click', () => {
            questions[currentQuestionIndex].classList.remove('active');
            currentQuestionIndex--;

            if (currentQuestionIndex >= 0) {
                questions[currentQuestionIndex].classList.add('active');
                backButton.style.display = currentQuestionIndex > 0 ? 'block' : 'none';
                submitButton.style.display = currentQuestionIndex === questions.length - 1 ? 'block' : 'none';
            }
        });

        // Hide the back button for the first question
        backButton.style.display = 'none';

        // Start the timer
        startTimer();
    </script>
</body>
</html>
