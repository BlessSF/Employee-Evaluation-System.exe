<?php 

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}


if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

   
    $employee_id = filter_var($employee_id, FILTER_SANITIZE_NUMBER_INT);

    
    $conn = new mysqli('localhost', 'root', '', 'employee_evaluation');
    
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    
    $stmt = $conn->prepare("DELETE FROM ojt_employees WHERE id = ?");
    $stmt->bind_param('i', $employee_id);

   
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }

    if ($stmt->execute()) {
       
        if ($stmt->affected_rows > 0) {
            
            $_SESSION['floating_message'] = "Employee with ID $employee_id has been deleted.";
        } else {
            
            $_SESSION['floating_message'] = "No employee found with ID $employee_id to delete.";
        }
        
        header("Location: ojt_employees.php");
        exit;
    } else {
      
        echo "Error deleting employee: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    
    header("Location: ojt_employees.php");
    exit;
}
?>
