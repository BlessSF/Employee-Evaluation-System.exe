<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}


$host = 'localhost';
$dbname = 'employee_evaluation';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 1977;


$emp_sql = "SELECT * FROM employees WHERE id = ?";
$emp_stmt = $conn->prepare($emp_sql);
$emp_stmt->bind_param("i", $employee_id);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();
$employee = $emp_result->fetch_assoc();


$pay_sql = "SELECT * FROM stella_payroll WHERE employee_id = ?";
$pay_stmt = $conn->prepare($pay_sql);
$pay_stmt->bind_param("i", $employee_id);
$pay_stmt->execute();
$pay_result = $pay_stmt->get_result();
$payroll = $pay_result->fetch_assoc();


function format_value($value) {
    return isset($value) ? number_format($value, 2) : "-";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month']) && isset($_POST['start_day']) && isset($_POST['end_day']) && isset($_POST['year'])) {
    $month = htmlspecialchars(trim($_POST['month']));
    $start_day = htmlspecialchars(trim($_POST['start_day']));
    $end_day = htmlspecialchars(trim($_POST['end_day']));
    $year = htmlspecialchars(trim($_POST['year']));


    $period_covered = "$month $start_day-$end_day, $year";


    $check_sql = "SELECT * FROM employee_period_cover WHERE employee_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $employee_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {

        $update_sql = "UPDATE employee_period_cover SET month = ?, start_day = ?, end_day = ?, year = ?, period_covered = ? WHERE employee_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("siisss", $month, $start_day, $end_day, $year, $period_covered, $employee_id);
        $update_stmt->execute();
    } else {

        $insert_sql = "INSERT INTO employee_period_cover (employee_id, month, start_day, end_day, year, period_covered) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isssss", $employee_id, $month, $start_day, $end_day, $year, $period_covered);
        $insert_stmt->execute();
    }
}


$default_month = date('F');
$default_start_day = '01'; 
$default_end_day = date('d');
$default_year = date('Y');


$current_period_sql = "SELECT month, start_day, end_day, year FROM employee_period_cover WHERE employee_id = ?";
$current_period_stmt = $conn->prepare($current_period_sql);
$current_period_stmt->bind_param("i", $employee_id);
$current_period_stmt->execute();
$current_period_result = $current_period_stmt->get_result();
$current_period = $current_period_result->fetch_assoc();


$period_covered_display = isset($current_period['month']) ? $current_period['month'] . ' ' . str_pad($current_period['start_day'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($current_period['end_day'], 2, "0", STR_PAD_LEFT) . ', ' . $current_period['year'] : "$default_month $default_start_day-$default_end_day, $default_year";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - Stella Fusion Buffet</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .download-button {
            display: block;
            width: 250px;
            background-color: #007bff;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            text-align: center;
            margin: 20px auto; 
            border-radius: 5px;
        }
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(203, 248, 167);
            margin: 20px;
            padding: 20px;
        }

        .payslip-container {
            width: 450px;
            margin: auto;
            border: 1px solid #000;
            padding: 20px;
            background-color: white;
        }

        .company-header {
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
            position: relative;
        }

        .logo1 {
            position: absolute;
            left: 0;
            top: 0;
            width: 60px;
            height: auto;
        }

        .payslip-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .employee-details {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .table-section {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .table-section th, .table-section td {
            padding: 5px;
            text-align: left;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 15px;
            font-size: 14px;
        }

        .total {
            font-weight: bold;
        }

        .signature-section {
            margin-top: 20px;
            font-size: 14px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 5px;
        }

        table th{
            background-color: white;
        }
        form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

button {
    display: block;
    width: 250px;
    background-color: #007bff; 
    color: white;
    padding: 12px;
    font-size: 16px;
    border: none;
    cursor: pointer;
    text-align: center;
    margin: 20px auto;
    border-radius: 5px;
}

button:hover {
    background-color: #0056b3; 
}

button:active {
    background-color: #004085; 
}

select,
button {
    padding: 10px;
    font-size: 14px;
    border-radius: 4px;
    border: 1px solid #ddd;
    outline: none;
    background-color: #f9f9f9;
}

select:focus,
button:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

button[type="submit"] {
    display: block;
    width: 250px;
    background-color: #007bff; 
    color: white;
    padding: 12px;
    font-size: 16px;
    border: none;
    cursor: pointer;
    text-align: center;
    margin: 20px auto;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}


button[type="submit"]:hover {
    background-color: #0056b3; 
}


button[type="submit"]:active {
    background-color: #004085; 
}


button[type="submit"]:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}
    </style>
</head>
<body>
  
    <div class="sidenav">
        <div class="logo-container">
            <img src="images/Logo.jpg" alt="Nina Trading Logo" class="logo">
        </div>
        <a href="index.php">Dashboard</a>
        <a href="employees.php">Probationary Employees</a>
        <a href="regular_employees.php">Regular Employees</a>
      
        
        <a href="stella_payroll.php" class="active">View Stella Payroll</a>
        <a href="notification.php">Employee Evaluations</a>
        <a href="evaluated_employees.php">Evaluated Employees</a>
        <a href="employee_tracking.php">Employee Actions Tracking</a>
        <a href="logout.php" class="logout-button">Log Out</a>
    </div>

    <div class="payslip-container">
    <div class="company-header">
        <img src="images/Stella.jpg" alt="Stella Logo" class="logo1">
        <strong>STELLA FUSION BUFFET</strong><br>
        Owned and Managed by: MULTIPLIERS CORP.<br>
        Greenfield Building, Benigno Aquino Ave.,<br>
        Diversion Road, Mandurriao, Iloilo City<br>
        Tel No: (033) 328-9995
    </div>

    <div class="payslip-title">PAYSLIP</div>

    <div class="employee-details">
        <strong>NAME:</strong> <?= htmlspecialchars($employee['surname'] . ', ' . $employee['first_name']); ?><br>

        <form method="POST" action="">
            <label for="month">Month:</label>
            <select name="month" id="month">
                <?php
                $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                foreach ($months as $month) {
                    echo "<option value='$month' " . ($month == $default_month ? 'selected' : '') . ">$month</option>";
                }
                ?>
            </select>

            <label for="start_day">Start Day:</label>
            <select name="start_day" id="start_day">
                <?php
                for ($i = 1; $i <= 31; $i++) {
                    $day = str_pad($i, 2, "0", STR_PAD_LEFT);
                    echo "<option value='$day' " . ($day == $default_start_day ? 'selected' : '') . ">$day</option>";
                }
                ?>
            </select>

            <label for="end_day">End Day:</label>
            <select name="end_day" id="end_day">
                <?php
                for ($i = 1; $i <= 31; $i++) {
                    $day = str_pad($i, 2, "0", STR_PAD_LEFT);
                    echo "<option value='$day' " . ($day == $default_end_day ? 'selected' : '') . ">$day</option>";
                }
                ?>
            </select>

            <label for="year">Year:</label>
            <select name="year" id="year">
                <?php
                $current_year = date('Y');
                for ($i = 2000; $i <= $current_year + 80; $i++) {
                    echo "<option value='$i' " . ($i == $default_year ? 'selected' : '') . ">$i</option>";
                }
                ?>
            </select>

            <button type="submit">Update Period</button>
        </form>
        
        <strong>PERIOD COVERED:</strong> <?= htmlspecialchars($period_covered_display); ?>
    </div>

    <table class="table-section">
        <tr class="section-title"><td colspan="2">Earnings</td></tr>
        <tr><th>No. of Days:</th><td><?= format_value($payroll['no_of_days'] ?? null); ?></td></tr>
        <tr><th>No. of Hours:</th><td><?= format_value($payroll['no_of_hours'] ?? null); ?></td></tr>
        <tr><th>Amount (Leave WI Pay):</th><td><?= format_value($payroll['leave_with_pay'] ?? null); ?></td></tr>
        <tr><th>Basic Salary:</th><td><?= format_value($payroll['basic_pay'] ?? null); ?></td></tr>
        <tr><th>Total/OVT:</th><td><?= format_value($payroll['overtime'] ?? null); ?></td></tr>
        <tr><th>10% (Night Diff):</th><td><?= format_value($payroll['night_diff'] ?? null); ?></td></tr>
        <tr><th>Holiday Pay 100%:</th><td><?= format_value($payroll['hundred_percent'] ?? null); ?></td></tr>
        <tr><th>Holiday Pay 30%:</th><td><?= format_value($payroll['thirty_percent'] ?? null); ?></td></tr>
        <tr><th>Allowance:</th><td><?= format_value($payroll['allowance'] ?? null); ?></td></tr>
        <tr class="total"><th>Gross Pay:</th><td><?= format_value($payroll['gross_pay'] ?? null); ?></td></tr>
    </table>

    <table class="table-section">
        <tr class="section-title"><td colspan="2">Deductions</td></tr>
        <tr><th>SSS Contribution:</th><td><?= format_value($payroll['sss_d'] ?? null); ?></td></tr>
        <tr><th>PAG-IBIG Contribution:</th><td><?= format_value($payroll['hdmf_contribution'] ?? null); ?></td></tr>
        <tr><th>PHIC Contribution:</th><td><?= format_value($payroll['phic'] ?? null); ?></td></tr>
        <tr><th>SSS Loan:</th><td><?= format_value($payroll['sss_loan'] ?? null); ?></td></tr>
        <tr><th>HDMF Loan:</th><td><?= format_value($payroll['hdmf_loan'] ?? null); ?></td></tr>
        <tr><th>Insurance:</th><td><?= format_value($payroll['insurance'] ?? null); ?></td></tr>
        <tr><th>Advances:</th><td><?= format_value($payroll['cash_advance'] ?? null); ?></td></tr>
        <tr><th>Others:</th><td><?= format_value($payroll['others'] ?? null); ?></td></tr>
        <tr class="total"><th>Total Deductions:</th><td><?= format_value($payroll['total_deductions'] ?? null); ?></td></tr>
    </table>

    <table class="table-section">
        <tr><th>Adjustment:</th><td><?= format_value($payroll['adjustment'] ?? null); ?></td></tr>
        <tr class="total"><th>Net Pay:</th><td><?= format_value($payroll['net_pay'] ?? null); ?></td></tr>
    </table>


    <div class="signature-section">
        <table class="table-section">
        <tr><th>Prepared by:  _______________________</th></tr>
        <tr><th>Received by:  _______________________</th></tr>
        </table>
    </div>


    <form action="download_payslip.php" method="GET">
        <input type="hidden" name="id" value="<?= htmlspecialchars($employee_id); ?>">
        <button type="submit" class="download-button">Download Payslip (PDF)</button>
    </form>

</div>

</body>
</html>

<?php
$conn->close();
?>
