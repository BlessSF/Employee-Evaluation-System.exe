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


if (isset($_POST['delete_employee'])) {
    $employee_id = $_POST['employee_id'];

    
    $old_sql = "SELECT * FROM employees WHERE id = '$employee_id'";
    $old_result = $conn->query($old_sql);
    $old_row = $old_result->fetch_assoc();

    
    if ($old_row) {
        $old_value = "surname: {$old_row['surname']}, first_name: {$old_row['first_name']}, date_hired: {$old_row['date_hired']}, branch: {$old_row['branch']}, position: {$old_row['position']}";
        $new_value = ''; 

        
        $sql = "DELETE FROM employees WHERE id = '$employee_id'";
        if ($conn->query($sql) === TRUE) {
           
            $action = 'Delete';
            $changed_by = $_SESSION['admin_name'];  

            $log_sql = "INSERT INTO employee_audit_log (employee_id, action, old_value, new_value, changed_by)
                        VALUES ('$employee_id', '$action', '$old_value', '$new_value', '$changed_by')";
            $conn->query($log_sql);
            
            header('Location: employee_tracking.php');
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        
        echo "Employee not found to delete.";
    }
}


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $employee_id = intval($_GET['id']);

    
    $stmt = $conn->prepare("SELECT surname, first_name FROM employees WHERE id = ?");
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();

    
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param('i', $employee_id);

    if ($stmt->execute()) {
        
        $_SESSION['floating_message'] = "{$employee['surname']}, {$employee['first_name']} has been successfully deleted.";
    } else {
        $_SESSION['floating_message'] = "Failed to delete employee.";
    }

    $stmt->close();
} else {
   
    $_SESSION['floating_message'] = "Invalid employee ID.";
}


header('Location: employees.php');
$conn->close();
?>
