<?php

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
            p.medical_reimbursement, p.hours_nd, p.ten_percent, p.hours_hp, p.hundred_percent, p.hours_hdp, p.thirty_percent, p.gross_pay, p.sss_loan, p.sss_d, p.phic, p.pag_ibig, p.insurance,
            p.others, p.cp, p.cash_advance, p.total_deductions, p.adjustment, p.net_pay
        FROM employees e
        LEFT JOIN dois_payroll p ON e.id = p.employee_id
        WHERE e.branch = 'DOIS'"; 

$result = $conn->query($sql);


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


$sheet->setCellValue('A1', 'Employee Name');
$sheet->setCellValue('B1', 'Daily Rate');
$sheet->setCellValue('C1', 'Rate/HR');
$sheet->setCellValue('D1', 'No. of Days');
$sheet->setCellValue('E1', 'No. of Hours');
$sheet->setCellValue('F1', 'Basic Pay');
$sheet->setCellValue('G1', 'Days');
$sheet->setCellValue('H1', 'Amount');
$sheet->setCellValue('I1', 'Rate/OVT');
$sheet->setCellValue('J1', 'Hour/OVT');
$sheet->setCellValue('K1', 'Total/OVT');
$sheet->setCellValue('L1', 'No. of Days TR');
$sheet->setCellValue('M1', 'TRAINEE ALLOWANCE');
$sheet->setCellValue('N1', 'Allowance');
$sheet->setCellValue('O1', 'Medical Reimbursement');
$sheet->setCellValue('P1', 'HOURS/ND');
$sheet->setCellValue('Q1', '10%');
$sheet->setCellValue('R1', 'Hours/HP');
$sheet->setCellValue('S1', '100%');
$sheet->setCellValue('T1', 'Hours/HDP');
$sheet->setCellValue('U1', '30%');
$sheet->setCellValue('V1', 'Gross Pay');
$sheet->setCellValue('W1', 'SSS Loan');
$sheet->setCellValue('X1', 'SSS');
$sheet->setCellValue('Y1', 'PHIC');
$sheet->setCellValue('Z1', 'PAG-IBIG');
$sheet->setCellValue('AA1', 'Insurance');
$sheet->setCellValue('AB1', 'Others');
$sheet->setCellValue('AC1', 'CP');
$sheet->setCellValue('AD1', 'Cash Advance');
$sheet->setCellValue('AE1', 'Total Deductions');
$sheet->setCellValue('AF1', 'Adjustment');
$sheet->setCellValue('AG1', 'Net Pay');


$rowIndex = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowIndex, $row['first_name'] . ' ' . $row['surname']);
    $sheet->setCellValue('B' . $rowIndex, $row['daily_rate']);
    $sheet->setCellValue('C' . $rowIndex, $row['rate_per_hour']);
    $sheet->setCellValue('D' . $rowIndex, $row['no_of_days']);
    $sheet->setCellValue('E' . $rowIndex, $row['no_of_hours']);
    $sheet->setCellValue('F' . $rowIndex, $row['basic_pay']);
    $sheet->setCellValue('G' . $rowIndex, $row['days']);
    $sheet->setCellValue('H' . $rowIndex, $row['amount']);
    $sheet->setCellValue('I' . $rowIndex, $row['rate_per_hour'] * 1.25); 
    $sheet->setCellValue('J' . $rowIndex, 0); 
    $sheet->setCellValue('K' . $rowIndex, 0); 
    $sheet->setCellValue('L' . $rowIndex, $row['no_of_days_tr']);
    $sheet->setCellValue('M' . $rowIndex, $row['trainee_allowance']);
    $sheet->setCellValue('N' . $rowIndex, $row['allowance']);
    $sheet->setCellValue('O' . $rowIndex, $row['medical_reimbursement']);
    $sheet->setCellValue('P' . $rowIndex, $row['hours_nd']);
    $sheet->setCellValue('Q' . $rowIndex, $row['ten_percent']);
    $sheet->setCellValue('R' . $rowIndex, $row['hours_hp']);
    $sheet->setCellValue('S' . $rowIndex, $row['hundred_percent']);
    $sheet->setCellValue('T' . $rowIndex, $row['hours_hdp']);
    $sheet->setCellValue('U' . $rowIndex, $row['thirty_percent']);
    $sheet->setCellValue('V' . $rowIndex, $row['gross_pay']);
    $sheet->setCellValue('W' . $rowIndex, $row['sss_loan']);
    $sheet->setCellValue('X' . $rowIndex, $row['sss_d']);
    $sheet->setCellValue('Y' . $rowIndex, $row['phic']);
    $sheet->setCellValue('Z' . $rowIndex, $row['pag_ibig']);
    $sheet->setCellValue('AA' . $rowIndex, $row['insurance']);
    $sheet->setCellValue('AB' . $rowIndex, $row['others']);
    $sheet->setCellValue('AC' . $rowIndex, $row['cp']);
    $sheet->setCellValue('AD' . $rowIndex, $row['cash_advance']);
    $sheet->setCellValue('AE' . $rowIndex, $row['total_deductions']);
    $sheet->setCellValue('AF' . $rowIndex, $row['adjustment']);
    $sheet->setCellValue('AG' . $rowIndex, $row['net_pay']);
    
    $rowIndex++;
}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="dois_payroll_data.xlsx"');
header('Cache-Control: max-age=0');


$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
