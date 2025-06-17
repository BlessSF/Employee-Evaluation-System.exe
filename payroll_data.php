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

$sql = "SELECT e.id, e.surname, e.first_name, e.position, e.status, e.branch FROM employees e";
$result = $conn->query($sql);

function safe_htmlspecialchars($value) {
    return htmlspecialchars($value === NULL ? '' : $value, ENT_QUOTES, 'UTF-8');
}

function ucfirst_safe($value) {
    return ucfirst(safe_htmlspecialchars($value));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Data</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        h1 {
            color: white;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        thead {
            background-color: #007bff;
            color: white;
        }
        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        th {
            font-weight: bold;
            color: black;
        }

        .sidenav a.active {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        @media (max-width: 768px), (max-width: 480px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        .pub-container, .stella-container, .dois-container {
            display: inline-block;
            margin: 10px;
        }
        .pub-box, .stella-box, .dois-box {
            display: block;
            padding: 20px 40px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 200px;
            color: white;
        }
        .pub-box { background-color: red; margin-right: 5px; }
        .stella-box { background-color: rgb(30, 255, 0); margin-left: 45px; }
        .dois-box { background-color: #ff5722; margin-left: 1px; }
        .pub-container, .stella-container, .dois-container {
            margin-right: 80px;
            margin-top: 20px;
        }

        .status-ongoing { color:rgb(146, 3, 3); font-weight: bold; }
        .status-done { color: #28a745; font-weight: bold; }

      

        #branchFilter, #nameSearch, #exportBtn {
            padding: 8px 12px;
            font-size: 16px;
            margin: 10px 5px;
        }
        .export-btn {
    background-color: #28a745;
    color: white;
    font-size: 16px;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: bold;
}

.export-btn:hover {
    background-color: #218838;
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
    <a href="ojt_employees.php">OJT Employees</a>
    <a href="ojt_hours.php">OJT HOURS</a>
    <a href="notification.php">Employee Evaluations</a>
    <a href="evaluated_employees.php">Evaluated Employees</a>
    <a href="employee_tracking.php">Employee Actions Tracking</a>
    <a href="payroll_data.php" class="active">View Payroll Data</a>
    <a href="logout.php" class="logout-button">Log Out</a>
</div>

<div class="main">
    <header>
        <h1>Payroll Data</h1>
    </header>

    <div class="pub-container">
        <a href="pub_payroll.php" class="pub-box">PUB</a> 
    </div>
    <div class="stella-container">
        <a href="stella_payroll.php" class="stella-box">STELLA</a> 
    </div>
    <div class="dois-container">
        <a href="dois_payroll.php" class="dois-box">DOIS</a> 
    </div>

    <!-- Filter Controls -->
    <div style="text-align: center;">
        <label for="branchFilter"><strong>Filter by Branch:</strong></label>
        <select id="branchFilter">
            <option value="ALL">All</option>
            <option value="STELLA">STELLA</option>
            <option value="DOIS">DOIS</option>
            <option value="PUB">PUB</option>
        </select>

        <label for="nameSearch"><strong>Search by Name:</strong></label>
        <input type="text" id="nameSearch" placeholder="Enter employee name...">

        <button id="exportBtn" class="export-btn">Download Sheet</button>

    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Employee Name</th>
                <th>Position</th>
                <th>Status</th>
                <th>Branch</th>
                <th>Payslip Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            $counter = 1;
            while ($row = $result->fetch_assoc()) {
                $branch = strtoupper($row['branch']);
                $payslip_status = 'ONGOING';

                switch ($branch) {
                    case 'STELLA':
                        $checkSql = "SELECT payslip_status FROM stella_payroll WHERE employee_id = " . (int)$row['id'] . " ORDER BY id DESC LIMIT 1";
                        $branch_class = 'branch-stella';
                        break;
                    case 'DOIS':
                        $checkSql = "SELECT payslip_status FROM dois_payroll WHERE employee_id = " . (int)$row['id'] . " ORDER BY id DESC LIMIT 1";
                        $branch_class = 'branch-dois';
                        break;
                    case 'PUB':
                        $checkSql = "SELECT payslip_status FROM pub_payroll WHERE employee_id = " . (int)$row['id'] . " ORDER BY id DESC LIMIT 1";
                        $branch_class = 'branch-pub';
                        break;
                    default:
                        $checkSql = "";
                        $branch_class = '';
                        break;
                }

                if (!empty($checkSql)) {
                    $checkResult = $conn->query($checkSql);
                    if ($checkResult && $checkResult->num_rows > 0) {
                        $statRow = $checkResult->fetch_assoc();
                        $payslip_status = $statRow['payslip_status'];
                    }
                }

                $statusClass = ($payslip_status === 'DONE') ? 'status-done' : 'status-ongoing';

                echo "<tr data-branch='$branch'>";
                echo "<td>" . $counter++ . "</td>";
                echo "<td>" . ucfirst_safe($row['first_name']) . " " . ucfirst_safe($row['surname']) . "</td>";
                echo "<td>" . ucfirst_safe($row['position']) . "</td>";
                echo "<td>" . ucfirst_safe($row['status']) . "</td>";
                echo "<td class='branch-cell $branch_class'>" . ucfirst_safe($row['branch']) . "</td>";
                echo "<td class='$statusClass'>" . safe_htmlspecialchars($payslip_status) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No records found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById("branchFilter").addEventListener("change", filterTable);
document.getElementById("nameSearch").addEventListener("input", filterTable);

function filterTable() {
    const selectedBranch = document.getElementById("branchFilter").value;
    const searchText = document.getElementById("nameSearch").value.toLowerCase();
    const rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
        const branch = row.getAttribute("data-branch");
        const name = row.cells[1].textContent.toLowerCase();

        const matchBranch = selectedBranch === "ALL" || branch === selectedBranch;
        const matchName = name.includes(searchText);

        row.style.display = (matchBranch && matchName) ? "" : "none";
    });
}

document.getElementById("exportBtn").addEventListener("click", function () {
    const rows = document.querySelectorAll("tbody tr");
    let csvContent = "No.,Employee Name,Position,Status,Branch,Payslip Status\n";

    rows.forEach(row => {
        if (row.style.display !== "none") {
            const cols = row.querySelectorAll("td");
            const rowData = Array.from(cols).map(td => `"${td.textContent.trim()}"`).join(",");
            csvContent += rowData + "\n";
        }
    });

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "payroll_data_filtered.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});
</script>
</body>
</html>
