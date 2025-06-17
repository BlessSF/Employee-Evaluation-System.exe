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


$sql = "SELECT ojt_employees.id, ojt_employees.surname, ojt_employees.first_name, ojt_employees.branch, 
               ojt_employees.date_hired, ojt_employees.hours_to_achieve, ojt_employees.remaining_minutes
        FROM ojt_employees
        ORDER BY ojt_employees.surname";

$result = $conn->query($sql);
$ojt_employees = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OJT Hours Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <style>
        .sidenav a.active {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .branch-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .branch-table th, .branch-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .remaining-hours, .remaining-minutes {
            font-weight: bold;
            text-align: center;
        }

       
        .save-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 8px;
        }

        .save-button:hover {
            background-color: #45a049;
        }

        
        .toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 2px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 75%;
            top: 20%;
            transform: translate(-50%, -50%);
            font-size: 17px;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .toast.show {
            visibility: visible;
            opacity: 1;
        }

       
        .pub {
            color: red;
        }

        .pub .remaining-hours, .pub .remaining-minutes {
            color: red;
        }

        .dois {
            color: #ff5722;
        }

        .dois .remaining-hours, .dois .remaining-minutes {
            color: orange;
        }

        .stella {
            color: green;
        }

        .stella .remaining-hours, .stella .remaining-minutes {
            color: green;
        }

        .input-field {
            width: 100px;
            text-align: center;
            padding: 5px;
            font-size: 14px;
        }

        .input-group {
            position: relative;
            display: inline-block;
        }

        .input-group input {
            width: 95px;
            text-align: center;
            font-size: 14px;
            padding: 5px;
        }

        .input-group .fa-minus, .input-group .fa-plus {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #888;
        }

        .input-group .fa-minus {
            left: 5px;
        }

        .input-group .fa-plus {
            right: 5px;
        }
    </style>
</head>
<body>

    <div class="sidenav">
        <div class="logo-container">
            <img src="images/Logo.jpg" alt="Nina Trading Logo" class="logo">
        </div>
        <a href="index.php" >Dashboard</a>
        <a href="employees.php">Probationary Employees</a>
        <a href="regular_employees.php">Regular Employees</a>
        <a href="ojt_employees.php">OJT Employees</a>
        <a href="ojt_hours.php"class="active">OJT HOURS</a>
       
        <a href="notification.php">Employee Evaluations</a>
        <a href="evaluated_employees.php">Evaluated Employees</a>
        <a href="employee_tracking.php">Employee Actions Tracking</a>
        <a href="payroll_data.php">View Payroll Data</a>
        <a href="logout.php" class="logout-button">Log Out</a>
    </div>

    <div class="main">
        <header>
            <h1>OJT Hours Tracker</h1>
        </header>

        <form id="saveForm">
            <table class="branch-table">
                <thead>
                    <tr>
                        <th>Surname</th>
                        <th>First Name</th>
                        <th>Branch</th>
                        <th>Date Hired</th>
                        <th>Remaining Hours</th>
                        <th>Remaining Minutes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ojt_employees as $ojt): ?>
                        <?php 
                            
                            $branchClass = strtolower($ojt['branch']); 
                        ?>
                        <tr class="<?= $branchClass ?>" id="employee-<?= $ojt['id'] ?>">
                            <td><?= htmlspecialchars($ojt['surname']) ?></td>
                            <td><?= htmlspecialchars($ojt['first_name']) ?></td>
                            <td><?= htmlspecialchars($ojt['branch']) ?></td>
                            <td><?= htmlspecialchars($ojt['date_hired']) ?></td>
                            <td class="remaining-hours"><?= htmlspecialchars($ojt['hours_to_achieve']) ?></td>
                            <td class="remaining-minutes"><?= htmlspecialchars($ojt['remaining_minutes']) ?></td>
                            <td>
                              
                                <div class="input-group">
                                    <input type="number" class="input-field hours-input" data-id="<?= $ojt['id'] ?>" placeholder=" - Hours">
                                </div>

                                <div class="input-group">
                                    <input type="number" class="input-field minutes-input" data-id="<?= $ojt['id'] ?>" placeholder="- Minutes">
                                </div>

                             
                                <div class="input-group">
                                    <input type="number" class="input-field add-hours-input" data-id="<?= $ojt['id'] ?>" placeholder="+ Hours">
                                </div>

                                <div class="input-group">
                                    <input type="number" class="input-field add-minutes-input" data-id="<?= $ojt['id'] ?>" placeholder="+ Minutes">
                                </div>

                              
                                <button type="button" class="save-button save-btn" data-id="<?= $ojt['id'] ?>">Save Changes</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>

   
    <div id="toast" class="toast">Saving Changes...</div>

    <script>
        $(document).ready(function() {
       
            $(".save-btn").click(function() {
                var employeeId = $(this).data("id");
                var hoursToSubtract = $(this).closest("tr").find(".hours-input").val() || 0;
                var minutesToSubtract = $(this).closest("tr").find(".minutes-input").val() || 0;
                
            
                var hoursToAdd = $(this).closest("tr").find(".add-hours-input").val() || 0;
                var minutesToAdd = $(this).closest("tr").find(".add-minutes-input").val() || 0;

        
                $("#toast").text("Saving Changes...").addClass("show");

                $.ajax({
                    url: "save_ojt.php",  
                    type: "POST",
                    data: {
                        updatedData: JSON.stringify([{
                            ojt_id: employeeId,
                            hours: hoursToSubtract,
                            minutes: minutesToSubtract,
                            add_hours: hoursToAdd,  
                            add_minutes: minutesToAdd  
                        }])
                    },
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                       
                            $("#toast").text("Changes saved successfully!").addClass("show");

                          
                            setTimeout(function() {
                                location.reload();
                            }, 2000);  
                        } else {
                            $("#toast").text("Error saving changes. Please try again.").addClass("show");
                        }
                    },
                    error: function() {
                        $("#toast").text("An error occurred. Please try again.").addClass("show");
                    },
                    complete: function() {
                        
                        setTimeout(function() {
                            $("#toast").removeClass("show");
                        }, 3000);
                    }
                });
            });
        });
    </script>

</body>
</html>
