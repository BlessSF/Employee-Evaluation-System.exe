<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$host = 'localhost';
$dbname = 'employee_evaluation';
$username = 'root';
$password = '';


$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT e.id, e.surname, e.first_name, e.position, e.status, e.branch, 
    p.daily_rate, p.rate_per_hour, p.no_of_days, p.no_of_hours, p.basic_pay, 
    p.days, p.amount, p.no_of_days_tr, p.trainee_allowance, p.allowance,
    p.medical_reimbursement, p.hours_nd, p.ten_percent, p.hours_hp, p.hundred_percent, 
    p.hours_hdp, p.thirty_percent, p.gross_pay, p.sss_loan, p.sss_d, p.phic, p.pag_ibig, 
    p.insurance, p.others, p.cp, p.cash_advance, p.total_deductions, p.adjustment, p.net_pay,
    pc.period_covered
    FROM employees e
    LEFT JOIN stella_payroll p ON e.id = p.employee_id
    LEFT JOIN employee_period_cover pc ON e.id = pc.employee_id
    WHERE e.branch = 'STELLA'";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


$headers = [
    'Employee Name', 'Period Covered', 'Daily Rate', 'Rate/HR', 'No. of Days', 'No. of Hours', 'Basic Pay',
    'Days', 'Amount', 'No. of Days TR', 'TRAINEE ALLOWANCE', 'Allowance', 'Medical Reimbursement',
    'HOURS/ND', '10%', 'Hours/HP', '100%', 'Hours/HDP', '30%', 'Gross Pay',
    'SSS Loan', 'SSS', 'PHIC', 'PAG-IBIG', 'Insurance', 'Others', 'CP', 'Cash Advance',
    'Total Deductions', 'Adjustment', 'Net Pay'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $col = 'A';
    $sheet->setCellValue($col++ . $rowNum, ucfirst_safe($row['first_name']) . ' ' . ucfirst_safe($row['surname']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['period_covered']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['daily_rate']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['rate_per_hour']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['no_of_days']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['no_of_hours']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['basic_pay']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['days']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['amount']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['no_of_days_tr']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['trainee_allowance']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['allowance']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['medical_reimbursement']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['hours_nd']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['ten_percent']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['hours_hp']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['hundred_percent']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['hours_hdp']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['thirty_percent']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['gross_pay']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['sss_loan']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['sss_d']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['phic']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['pag_ibig']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['insurance']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['others']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['cp']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['cash_advance']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['total_deductions']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['adjustment']));
    $sheet->setCellValue($col++ . $rowNum, safe($row['net_pay']));
    $rowNum++;
}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Stella_Payroll_Data.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();


function safe($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function ucfirst_safe($value) {
    return ucfirst(htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'));
}
?>
