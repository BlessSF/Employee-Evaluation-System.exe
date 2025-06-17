<?php
session_start();


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}


$changed_by = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Unknown'; 


$conn = new mysqli('localhost', 'root', '', 'employee_evaluation');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}


if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Employee ID is required.");
}
$employee_id = intval($_GET['id']);


$sql = "SELECT id, surname, first_name, date_hired, status, position, branch, sub_branch FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Employee not found.");
}
$employee = $result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname    = $_POST['surname'];
    $first_name = $_POST['first_name'];
    $date_hired = $_POST['date_hired'];
    $status     = strtolower($_POST['status']); 
    $position   = $_POST['position'];
    $branch     = $_POST['branch'];
    $sub_branch = isset($_POST['sub_branch']) ? $_POST['sub_branch'] : null;

  
    $old_sql = "SELECT * FROM employees WHERE id = ?";
    $old_stmt = $conn->prepare($old_sql);
    $old_stmt->bind_param("i", $employee_id);
    $old_stmt->execute();
    $old_result = $old_stmt->get_result();
    $old_row = $old_result->fetch_assoc();

    $old_value = "surname: {$old_row['surname']}, first_name: {$old_row['first_name']}, date_hired: {$old_row['date_hired']}, status: {$old_row['status']}, position: {$old_row['position']}, branch: {$old_row['branch']}, sub_branch: {$old_row['sub_branch']}";
    $new_value = "surname: $surname, first_name: $first_name, date_hired: $date_hired, status: $status, position: $position, branch: $branch, sub_branch: $sub_branch";


    $update_sql = "UPDATE employees SET surname = ?, first_name = ?, date_hired = ?, status = ?, position = ?, branch = ?, sub_branch = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssssi", $surname, $first_name, $date_hired, $status, $position, $branch, $sub_branch, $employee_id);

    if ($update_stmt->execute()) {
 
        $action = 'Update';
        
  
        $log_sql = "INSERT INTO employee_audit_log (employee_id, action, old_value, new_value, changed_by) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("issss", $employee_id, $action, $old_value, $new_value, $changed_by);

        if ($log_stmt->execute()) {
      
            header("Location: employees.php");
            exit;
        } else {
            echo "Error inserting log: " . $conn->error;
        }
    } else {
        echo "Error updating employee: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        h1 {
            text-align: center;
            background-color: #087356;
            color: white;
            padding: 15px;
            border-radius: 5px;
        }
         body {
        font-family: Arial, sans-serif;
        background-color:hsl(158, 34.30%, 86.30%);
        margin: 0;
        padding: 0;
    }

    .main {
        max-width: 530px;
        margin: 50px auto;
        background: white;
        padding: 30px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .form-container {
        padding: 10px;
    }

    label {
        font-weight: bold;
        display: block;
        margin: 5px 0 5px;
        color: #333;
        font-size: 14px;
    }

    input, select {
        width: 420px;
        padding: 12px;
        border: 2px solid black;
        margin-top: 10px;
        margin-bottom: 5px;
        border-radius: 5px;
        font-size: 16px;
        background-color: white;
        outline: none;
        transition: 0.3s;
    }

    input:focus, select:focus {
        border-color: #007bff;
        box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5);
    }

    .error-message {
        color: red;
        background-color: #ffd6d6;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        text-align: center;
    }

    .button-container {
        text-align: center;
        margin-top: 30px;  
    }

    button {
        background-color: #007bff;
        color: white;
        border: none;
        margin-top: 30px;  
        padding: 15px 20px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        width: 250px;
        display: block;
        margin: auto;
        margin-top: 20px;
    }

    button:hover {
        background-color: #0056b3;
    }


    #subBranchContainer, #hoursContainer {
        display: none;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .main {
            width: 90%;
            padding: 20px;
        }
        
        button {
            width: 100%;
        }
    }

    </style>
    <script>
      
        function toggleSubBranch() {
            const branchSelect = document.getElementById('branch');
            const subBranchContainer = document.getElementById('subBranchContainer');
            const subBranchSelect = document.getElementById('sub_branch');

            if (branchSelect.value === 'PUB') {
                subBranchContainer.style.display = 'block';
            } else {
                subBranchContainer.style.display = 'none';
                subBranchSelect.value = ''; 
            }
        }
    </script>
</head>
<body>
    <div class="sidenav">
        <div class="logo-container">
            <img src="images/Logo.jpg" alt="Nina Trading Logo" class="logo">
        </div>
        <a href="index.php">Dashboard</a>
        <a href="employees.php">Probationary Employees</a>
        <a href="upload.php">Upload Employee Data</a>
        <a href="notification.php">Employee Evaluations</a>
        <a href="evaluated_employees.php">Evaluated Employees</a>
        <a href="employee_tracking.php">Employee Actions Tracking</a>
        <a href="logout.php" class="logout-button">Log Out</a>
    </div>
    <div class="main">
        <h1>Edit Employee</h1>

        <form method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">

            <label for="surname">Surname:</label>
            <input type="text" name="surname" id="surname" value="<?= htmlspecialchars($employee['surname']) ?>" required>

            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($employee['first_name']) ?>" required>

            <label for="date_hired">Date Hired:</label>
            <input type="date" name="date_hired" id="date_hired" value="<?= htmlspecialchars($employee['date_hired']) ?>" required>

            <label for="status">Employment Status:</label>
            <select name="status" id="status" required>
                <option value="probationary" <?= strtolower($employee['status']) === 'probationary' ? 'selected' : '' ?>>Probationary</option>
                <option value="regular" <?= strtolower($employee['status']) === 'regular' ? 'selected' : '' ?>>Regular</option>
            </select>

            <label for="branch">Branch:</label>
            <select name="branch" id="branch" onchange="toggleSubBranch()" required>
                <option value="STELLA" <?= $employee['branch'] === 'STELLA' ? 'selected' : '' ?>>STELLA</option>
                <option value="DOIS" <?= $employee['branch'] === 'DOIS' ? 'selected' : '' ?>>DOIS</option>
                <option value="PUB" <?= $employee['branch'] === 'PUB' ? 'selected' : '' ?>>PUB</option>
            </select>

            <div id="subBranchContainer" style="display: <?= $employee['branch'] === 'PUB' ? 'block' : 'none' ?>;">
                <label for="sub_branch">Sub-Branch:</label>
                <select name="sub_branch" id="sub_branch">
                    <option value="Main Office" <?= $employee['sub_branch'] === 'Main Office' ? 'selected' : '' ?>>Main Office</option>
                    <option value="Nina Food Products Trading" <?= $employee['sub_branch'] === 'Nina Food Products Trading' ? 'selected' : '' ?>>Nina Food Products Trading</option>
                    <option value="Shock Sisig" <?= $employee['sub_branch'] === 'Shock Sisig' ? 'selected' : '' ?>>Shock Sisig</option>
                    <option value="Pub Express Resto-Employers" <?= $employee['sub_branch'] === 'Pub Express Resto-Employers' ? 'selected' : '' ?>>Pub Express Resto-Employers</option>
                </select>
            </div>

            <label for="position">Position:</label>
            <input type="text" name="position" id="position" value="<?= htmlspecialchars($employee['position']) ?>" required>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
