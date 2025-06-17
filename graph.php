<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'employee_evaluation');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

/**
 * Function to execute a database query and return the result.
 *
 * @param string $sql The SQL query to be executed.
 * @param string $column The column to fetch from the result (if applicable).
 * @return mixed The result of the query, or an error message.
 */
function fetchQueryResult($sql, $column = null) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return "Error preparing query: " . $conn->error;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false) {
        return "Error executing query: " . $conn->error;
    }
    $data = $result->fetch_assoc();
    return $column ? $data[$column] : $data;
}

// Fetching employee counts
$ojt_employees = fetchQueryResult("SELECT COUNT(*) AS ojt_count FROM ojt_employees", 'ojt_count');
$total_employees = fetchQueryResult("SELECT COUNT(*) AS total_employees FROM employees", 'total_employees');
$evaluated_employees = fetchQueryResult("SELECT COUNT(DISTINCT employee_id) AS evaluated_count FROM evaluations", 'evaluated_count');
$probationary_employees = fetchQueryResult("SELECT COUNT(*) AS probationary_count FROM employees WHERE status = 'Probationary'", 'probationary_count');
$regular_employees = fetchQueryResult("SELECT COUNT(*) AS regular_count FROM employees WHERE status = 'Regular'", 'regular_count');

// Calculating not evaluated employees
$not_evaluated = $total_employees - $evaluated_employees;

// Fetching branch-wise employee counts
$sql_branches = "SELECT branch, COUNT(*) AS branch_count FROM employees GROUP BY branch";
$stmt_branches = $conn->prepare($sql_branches);
$stmt_branches->execute();
$result_branches = $stmt_branches->get_result();
$branch_counts = [];
while ($row = $result_branches->fetch_assoc()) {
    $branch_counts[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Graph - Employee Evaluation Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        
.section-title {
            font-size: 22px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid green;
            color:rgb(61, 84, 31);
        }

        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stats-card {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 280px;
            height: 120px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f8f9fa;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            transition: background-color 0.3s ease, transform 0.2s ease;
            cursor: pointer;
        }

        .stats-card:hover {
            background-color: #007bff;
            color: white;
            transform: translateY(-5px);
        }

        .stats-card i {
            margin-right: 10px;
            font-size: 24px;
        }

        .stats-card .number {
            font-size: 36px;
            font-weight: bold;
            margin-left: 10px;
        }

        .btn-refresh {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            
            margin-top: 10px;
        }

        .btn-refresh:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>

    <div class="sidenav">
        <div class="logo-container">
            <img src="images/Logo.jpg" alt="Nina Trading Logo" class="logo">
        </div>
        <a href="index.php" class="active">Dashboard</a>
        <a href="employees.php">Probationary Employees</a>
        <a href="regular_employees.php">Regular Employees</a>
        <a href="ojt_employees.php">OJT Employees</a>
        <a href="ojt_hours.php">OJT HOURS</a>
        <a href="notification.php">Employee Evaluations</a>
        <a href="evaluated_employees.php">Evaluated Employees</a>
        <a href="employee_tracking.php">Employee Actions Tracking</a>
        <a href="payroll_data.php">View Payroll Data</a>
        <a href="logout.php" class="logout-button">Log Out</a>
    </div>

    <div class="main">
        <header>
            <h1>Employee Evaluation Dashboard - Graph</h1>
        </header>

        <button class="btn-refresh" onclick="window.location.reload()">Refresh Stats</button>

        <section>
            <h2 class="section-title">Employee Statistics Graph</h2>
            <canvas id="employeeChart" width="400" height="200"></canvas>
            <script>
                var ctx = document.getElementById('employeeChart').getContext('2d');
                var employeeChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Probationary Employees', 'Regular Employees', 'OJT Employees', 'Evaluated Employees', 'Not Evaluated Employees'],
                        datasets: [{
                            label: 'Employee Count',    
                            data: [
                                <?php echo $probationary_employees; ?>,
                                <?php echo $regular_employees; ?>,
                                <?php echo $ojt_employees; ?>,
                                <?php echo $evaluated_employees; ?>,
                                <?php echo $not_evaluated; ?>
                            ],
                            backgroundColor: [
                                '#007bff', 
                                '#ff7f50', 
                                '#4caf50', 
                                '#9c27b0', 
                                '#f44336'  
                            ],
                            borderColor: [
                                '#007bff', 
                                '#ff7f50', 
                                '#4caf50', 
                                '#9c27b0', 
                                '#f44336'
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
            </script>
        </section>
    </div>
</body>
</html>

<?php $conn->close(); ?>
