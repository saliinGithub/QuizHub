<?php
session_start();
include '../db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['student_username'])) {
    // Redirect to login page if not logged in
    header("Location: ../stdudentlogin/student-login.html");
    exit();
}

// Initialize quiz counters if they don't exist
if (!isset($_SESSION['quiz_attempts'])) {
    $_SESSION['quiz_attempts'] = 0;
}
if (!isset($_SESSION['correct_answers'])) {
    $_SESSION['correct_answers'] = 0;
}
if (!isset($_SESSION['incorrect_answers'])) {
    $_SESSION['incorrect_answers'] = 0;
}

// Fetch quiz statistics from the database
$studentId = $_SESSION['student_id']; // Make sure you have student_id set in the session

$query = "SELECT total_attempts, total_correct, total_incorrect 
          FROM student_dashboard_totals WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

// Check if we got any results and assign to session variables
if ($row = $result->fetch_assoc()) {
    $_SESSION['quiz_attempts'] = $row['total_attempts'];
    $_SESSION['correct_answers'] = $row['total_correct'];
    $_SESSION['incorrect_answers'] = $row['total_incorrect'];
} else {
    // Default values if no data found
    $_SESSION['quiz_attempts'] = 0;
    $_SESSION['correct_answers'] = 0;
    $_SESSION['incorrect_answers'] = 0;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome CDN for Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
 
</head>
<style>
  .sidebar {
    height: 100vh;
    background-color: #2c2c2c;
    color: #fff;
    padding-top: 20px;
    position: fixed;
    width: 250px;
    left: 0;
  }

  .sidebar h2 {
    padding: 10px;
    text-align: center;
    font-size: 1.5rem;
  }

  .sidebar a {
    color: #fff;
    padding: 15px;
    display: block;
    font-size: 1rem;
    text-decoration: none;
    margin-bottom: 10px;
  }

  .sidebar a i {
    margin-right: 10px;
  }

  .sidebar a:hover {
    background-color: #575757;
    text-decoration: none;
  }

  /* Dashboard main layout */
  .main-content {
    margin-left: 250px;
    padding: 20px;
  }

  .header {
    background-color: #fff;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
    margin-bottom: 20px;
  }

  .header .search-bar input {
    width: 300px;
    padding: 8px;
    border-radius: 20px;
    border: 2px solid #333;
  }

  .header .welcome {
    font-size: 1.2rem;
    font-weight: bold;
  }

  /* Stat Box Styling */
  .stat-card {
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    color: white;
    margin-bottom: 20px;
    transition: transform 0.2s;
  }

  .stat-card:hover {
    transform: scale(1.05);
  }

  .quiz-attempt-box {
    background-color: #007bff;
  }

  .correct-answer-box {
    background-color: #28a745;
  }

  .incorrect-answer-box {
    background-color: #dc3545;
  }

  .stat-card h5 {
    margin-bottom: 15px;
    font-size: 1.2rem;
  }

  .stat-card .stat-number {
    font-size: 2rem;
    font-weight: bold;
  }

  /* Subject Selection Styling */
  .subject-selection {
    margin-top: 40px;
    text-align: center;
  }

  .subject-title {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 20px;
  }

  .subject-box {
    background-color: white;
    border-radius: 10px;
    margin: 10px;
    width: 150px;
    height: 150px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
    transition: all 0.3s;
    cursor: pointer;
    color: black;
    font-weight: bold;
    font-size: 1.1rem;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .subject-box:hover {
    background-color: #6a11cb;
    color: white;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.6);
    transform: translateY(-5px);
  }

  .subject-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
  }

  .subject-container .subject-box {
    flex: 0 0 calc(33.33% - 20px);
  }

  .subject-container .subject-box:nth-child(n+4) {
    flex: 0 0 calc(50% - 20px);
  }

  .footer {
    background-color: #f1f1f1;
    padding: 20px;
    text-align: center;
  }

  /* Layout for the graph and to-do list */
  .graph-todo-container {
    display: flex;
    justify-content: space-between;
    margin-top: 50px;
  }

  /* To-Do List Styling */
  .todo-container {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    width: 35%; /* Adjust width for to-do list */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  .todo-title {
    font-size: 1.5rem;
    margin-bottom: 10px;
  }

  .todo-input {
    display: flex;
    margin-bottom: 10px;
  }

  .todo-input input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  .todo-input button {
    padding: 8px;
    margin-left: 5px;
    border: none;
    background-color: #007bff;
    color: white;
    border-radius: 5px;
    cursor: pointer;
  }

  .todo-input button:hover {
    background-color: #0056b3;
  }

  .todo-list {
    list-style: none;
    padding: 0;
  }

  .todo-list li {
    padding: 8px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .todo-list li button {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
  }

  /* Graph container styling */
  .graph-container {
    width: 60%; /* Adjust width for the graph */
  }
</style>

<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Quizhub</h2>
    <a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="#subjects"><i class="fas fa-file-alt"></i> Exam</a>
    <a href="calender.html"><i class="fas fa-calendar-alt"></i> Calendar</a>
    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
    <a href="student_quizzes.php"><i class="fas fa-chart-line"></i> Performance</a>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <h4>Welcome, <span id="username"><?php echo $_SESSION['student_username']; ?></span></h4>
      <div class="search-bar">
        <input type="text" placeholder="Search..."> <i class="fas fa-search"></i>
      </div>
      <button class="btn btn-danger" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <!-- Cards Section for Quiz Attempts -->
    <div class="row card-section">
  <div class="col-md-4">
    <div class="stat-card quiz-attempt-box">
      <h5>Quiz Attempts</h5>
      <div class="stat-number" id="quiz-attempts"><?php echo $_SESSION['quiz_attempts']; ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card correct-answer-box">
      <h5>Correct Answers</h5>
      <div class="stat-number" id="correct-answers"><?php echo $_SESSION['correct_answers']; ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card incorrect-answer-box">
      <h5>Incorrect Answers</h5>
      <div class="stat-number" id="incorrect-answers"><?php echo $_SESSION['incorrect_answers']; ?></div>
    </div>
  </div>
</div>


    <!-- Subject Selection -->
    <div class="subject-selection">
    <h2 class="subject-title" id="subjects">Select Subjects for Quiz</h2>
    <form id="subjectForm" action="fetch_questions.php" method="POST">
        <input type="hidden" id="selectedSubject" name="subject" value="">
    </form>
    <div class="subject-container">
        <div class="subject-box" onclick="startQuiz('Operating System', 1)">
            <h5>Operating System</h5>
        </div>
        <div class="subject-box" onclick="startQuiz('Database Management System', 2)">
            <h5>Database Management System</h5>
        </div>
        <div class="subject-box" onclick="startQuiz('Scripting Language', 3)">
            <h5>Scripting Language</h5>
        </div>
        <div class="subject-box" onclick="startQuiz('Numerical Method', 4)">
            <h5>Numerical Method</h5>
        </div>
        <div class="subject-box" onclick="startQuiz('Software Engineering', 5)">
            <h5>Software Engineering</h5>
        </div>
    </div>
</div>
    <!-- Graph and To-Do List Section -->
    <div class="graph-todo-container">
      <!-- Graph Section -->
      <div class="graph-container">
        <h4>Performance Graph</h4>
        <canvas id="performanceGraph"></canvas>
      </div>

      <!-- To-Do List Section -->
      <div class="todo-container">
        <h4 class="todo-title">To-Do List</h4>
        <div class="todo-input">
          <input type="text" id="todoInput" placeholder="Add new task">
          <button onclick="addTodo()">Add</button>
        </div>
        <ul class="todo-list" id="todoList"></ul>
      </div>
    </div>

  </div>

  <!-- Footer -->
  <div class="footer">
    &copy; 2024 Quizhub. All rights reserved.
  </div>

  
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Chart.js for Graph -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  

  <script>

    
    
 // Function to create the performance graph
 function createPerformanceGraph() {
      // Get values from the session variables
      const quizAttempts = <?php echo json_encode($_SESSION['quiz_attempts']); ?>;
      const correctAnswers = <?php echo json_encode($_SESSION['correct_answers']); ?>;
      const incorrectAnswers = <?php echo json_encode($_SESSION['incorrect_answers']); ?>;

      // Create a context for the chart
      const ctx = document.getElementById('performanceGraph').getContext('2d');

      // Create the chart
      const performanceGraph = new Chart(ctx, {
          type: 'bar', // Choose the type of graph you want (bar, line, pie, etc.)
          data: {
              labels: ['Total Attempts', 'Correct Answers', 'Incorrect Answers'],
              datasets: [{
                  label: 'Quiz Performance',
                  data: [quizAttempts, correctAnswers, incorrectAnswers],
                  backgroundColor: [
                      'rgba(54, 162, 235, 0.6)', // Color for Total Attempts
                      'rgba(75, 192, 192, 0.6)', // Color for Correct Answers
                      'rgba(255, 99, 132, 0.6)'   // Color for Incorrect Answers
                  ],
                  borderColor: [
                      'rgba(54, 162, 235, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(255, 99, 132, 1)'
                  ],
                  borderWidth: 1
              }]
          },
          options: {
              scales: {
                  y: {
                      beginAtZero: true
                  }
              }
          }
      });
  }

  // Call the function to create the graph when the page loads
  window.onload = createPerformanceGraph;

// To-Do List Functionality
function addTodo() {
  const todoInput = document.getElementById('todoInput');
  const todoList = document.getElementById('todoList');
  
  if (todoInput.value.trim() !== "") {
    // Create new list item
    const newTodo = document.createElement('li');
    newTodo.textContent = todoInput.value;
    
    // Add delete button to the new to-do item
    const deleteButton = document.createElement('button');
    deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
    deleteButton.onclick = function () {
      todoList.removeChild(newTodo);
    };
    
    newTodo.appendChild(deleteButton);
    
    // Add the new to-do item to the list
    todoList.appendChild(newTodo);
    
    // Clear input field
    todoInput.value = "";
  }
}
function startQuiz(subject, quizId) {
    document.getElementById('selectedSubject').value = quizId;  // Set the quiz ID based on the selected subject
    document.getElementById('subjectForm').submit();  // Submit the form
}

</script>

    


</body>

</html>
