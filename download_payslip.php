<?php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);


ob_start();

require_once('vendor/tecnickcom/tcpdf/tcpdf.php');


session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}


$host     = 'localhost';
$dbname   = 'employee_evaluation';
$username = 'root';
$password = '';
$conn     = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    exit;
}


$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 1977;


$emp_sql   = "SELECT * FROM employees WHERE id = ?";
$emp_stmt  = $conn->prepare($emp_sql);
$emp_stmt->bind_param("i", $employee_id);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();
$employee   = $emp_result->fetch_assoc();


$pay_sql   = "SELECT * FROM stella_payroll WHERE employee_id = ?";
$pay_stmt  = $conn->prepare($pay_sql);
$pay_stmt->bind_param("i", $employee_id);
$pay_stmt->execute();
$pay_result = $pay_stmt->get_result();
$payroll   = $pay_result->fetch_assoc();


$period_sql = "SELECT period_covered FROM employee_period_cover WHERE employee_id = ?";
$period_stmt = $conn->prepare($period_sql);
$period_stmt->bind_param("i", $employee_id);
$period_stmt->execute();
$period_result = $period_stmt->get_result();
$period_covered = $period_result->num_rows > 0 ? $period_result->fetch_assoc()['period_covered'] : 'Period not set'; 


function format_value($value) {
    return isset($value) ? number_format($value, 2) : "-";
}


$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Stella Fusion Buffet');
$pdf->SetTitle('Payslip');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->AddPage();

// --- Header Section with Logo ---
$pdf->Image('images/Stella.jpg', 10, 12, 30, 0, 'JPG'); 

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'STELLA FUSION BUFFET', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Owned and Managed by: MULTIPLIERS CORP.', 0, 1, 'C');
$pdf->Cell(0, 6, 'Greenfield Building, Benigno Aquino Ave.,', 0, 1, 'C');
$pdf->Cell(0, 6, 'Diversion Road, Mandurriao, Iloilo City', 0, 1, 'C');
$pdf->Cell(0, 6, 'Tel No: (033) 328-9995', 0, 1, 'C');
$pdf->Ln(10);

// --- Employee Name & Period Covered Aligned to the Right ---
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'PAYSLIP', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 10); 
$pdf->Cell(1, 6, '', 0, 0); 
$pdf->Cell(70, 6, 'NAME: ' . htmlspecialchars($employee['surname'] . ', ' . $employee['first_name']), 0, 1);
$pdf->SetFont('helvetica', '', 10); 

$pdf->Cell(1, 6, '', 0, 0); 
$pdf->Cell(70, 6, 'PERIOD COVERED: ' . htmlspecialchars($period_covered), 0, 1);
$pdf->Ln(6);

// --- Earnings Table ---
$pdf->SetFont('helvetica', 'B', 10);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(95, 6, 'No. of Days:', 1);
$pdf->Cell(95, 6, format_value($payroll['no_of_days']), 1, 1);
$pdf->Cell(95, 6, 'No. of Hours:', 1);
$pdf->Cell(95, 6, format_value($payroll['no_of_hours']), 1, 1);
$pdf->Cell(95, 6, 'Amount (Leave WI Pay):', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['leave_with_pay']) ? $payroll['leave_with_pay'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Basic Salary:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['basic_pay']) ? $payroll['basic_pay'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Total/OVT:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['overtime']) ? $payroll['overtime'] : 0), 1, 1);
$pdf->Cell(95, 6, '10% (Night Diff):', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['night_diff']) ? $payroll['night_diff'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Holiday Pay 100%:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['hundred_percent']) ? $payroll['hundred_percent'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Holiday Pay 30%:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['thirty_percent']) ? $payroll['thirty_percent'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Allowance:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['allowance']) ? $payroll['allowance'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Gross Pay:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['gross_pay']) ? $payroll['gross_pay'] : 0), 1, 1);
$pdf->Ln(6);

// --- Deductions Table ---
$pdf->SetFont('helvetica', 'B', 10);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(95, 6, 'SSS Contribution:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['sss_d']) ? $payroll['sss_d'] : 0), 1, 1);
$pdf->Cell(95, 6, 'PAG-IBIG Contribution:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['hdmf_contribution']) ? $payroll['hdmf_contribution'] : 0), 1, 1);
$pdf->Cell(95, 6, 'PHIC Contribution:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['phic']) ? $payroll['phic'] : 0), 1, 1);
$pdf->Cell(95, 6, 'SSS Loan:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['sss_loan']) ? $payroll['sss_loan'] : 0), 1, 1);
$pdf->Cell(95, 6, 'HDMF Loan:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['hdmf_loan']) ? $payroll['hdmf_loan'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Insurance:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['insurance']) ? $payroll['insurance'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Advances:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['cash_advance']) ? $payroll['cash_advance'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Others:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['others']) ? $payroll['others'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Total Deductions:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['total_deductions']) ? $payroll['total_deductions'] : 0), 1, 1);
$pdf->Ln(6);

// --- Net Pay Section ---
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(95, 6, 'Adjustment:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['adjustment']) ? $payroll['adjustment'] : 0), 1, 1);
$pdf->Cell(95, 6, 'Net Pay:', 1);
$pdf->Cell(95, 6, format_value(isset($payroll['net_pay']) ? $payroll['net_pay'] : 0), 1, 1);
$pdf->Ln(6);

// --- Signature Section ---
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(105, 35, 'Prepared by:', 0, 0);
$pdf->Cell(105, 35, 'Received by:', 0, 1);
$pdf->Ln(-10);
$pdf->Cell(105, 10,    '__________________________', 0, 0);
$pdf->Cell(105, 10, '__________________________', 0, 1);
$pdf->Ln(-10);


$file_name = 'payslip_' . preg_replace('/[^a-zA-Z0-9_ -]/', '', $employee['first_name']) . '_' . preg_replace('/[^a-zA-Z0-9_ -]/', '', $employee['surname']) . '.pdf';


ob_clean();


$pdf->Output($file_name, 'D');
exit;
?>
