<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard Totals</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 12px;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .edit-btn, .delete-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Dashboard Totals</h1>

        <table id="dashboardTable">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Total Attempts</th>
                    <th>Total Correct</th>
                    <th>Total Incorrect</th>
                    <th>Total Unanswered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr data-student-id="1">
                    <td>1</td>
                    <td>1</td>
                    <td>10</td>
                    <td>20</td>
                    <td>30</td>
                    <td>
                        <button class="edit-btn" onclick="editRow(this)">Edit</button>
                        <button class="delete-btn" onclick="deleteRow(this)">Delete</button>
                    </td>
                </tr>
                <tr data-student-id="2">
                    <td>2</td>
                    <td>6</td>
                    <td>23</td>
                    <td>76</td>
                    <td>0</td>
                    <td>
                        <button class="edit-btn" onclick="editRow(this)">Edit</button>
                        <button class="delete-btn" onclick="deleteRow(this)">Delete</button>
                    </td>
                </tr>
                <!-- Add more rows as needed -->
            </tbody>
        </table>
    </div>

    <script>
        // Simulate login by setting the logged-in student's ID
        const loggedInStudentId = 1; // Change this to 2 for student ID 2

        // Function to filter the table based on logged-in student ID
        function filterByStudent() {
            const rows = document.querySelectorAll('#dashboardTable tbody tr');

            rows.forEach(row => {
                const studentId = row.getAttribute('data-student-id');
                if (studentId === String(loggedInStudentId)) {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
            });
        }

        // Initial filter call on page load
        window.onload = filterByStudent;

        function editRow(button) {
            var row = button.closest("tr");
            var cells = row.querySelectorAll("td");

            cells.forEach((cell, index) => {
                if (index < cells.length - 1) { // Exclude the last cell (Actions)
                    var input = document.createElement("input");
                    input.value = cell.textContent;
                    cell.innerHTML = ""; // Clear the cell
                    cell.appendChild(input);
                }
            });

            button.textContent = "Save";
            button.setAttribute("onclick", "saveRow(this)");
        }

        function saveRow(button) {
            var row = button.closest("tr");
            var cells = row.querySelectorAll("td");

            cells.forEach((cell, index) => {
                if (index < cells.length - 1) { // Exclude the last cell (Actions)
                    cell.textContent = cell.querySelector("input").value; // Save the input value
                }
            });

            button.textContent = "Edit";
            button.setAttribute("onclick", "editRow(this)");
        }

        function deleteRow(button) {
            var row = button.closest("tr");
            row.remove();
        }
    </script>
</body>
</html>
