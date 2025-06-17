<?php

$host = 'localhost';
$dbname = 'employee_evaluation';
$username = 'root';
$password = '';


$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "DELETE FROM stella_payroll";


if ($conn->query($sql) === TRUE) {
    echo "All payroll data has been deleted";
} else {
    echo "Error deleting data: " . $conn->error;
}


$conn->close();
?>
