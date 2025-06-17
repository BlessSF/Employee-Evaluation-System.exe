<?php
require_once('vendor/autoload.php');
use TCPDF;


function getDbConnection() {
    $conn = new mysqli('localhost', 'root', '', 'employee_evaluation');
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    return $conn;
}



function getEmployeeDetails($conn, $employee_id) {
    $sql = "SELECT * FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}



function getLatestEvaluation($conn, $employee_id) {
    $sql_eval = "SELECT appraisal_rating, summary_performance, recommendation 
                 FROM evaluations 
                 WHERE employee_id = ? 
                 ORDER BY evaluation_date DESC 
                 LIMIT 1";
    $stmt = $conn->prepare($sql_eval);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}


$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($employee_id === 0) {
    die('Invalid Employee ID');
}

try {

    $conn = getDbConnection();


    $employee = getEmployeeDetails($conn, $employee_id);
    if (!$employee) {
        throw new Exception('Employee not found.');
    }

   
    $evaluation = getLatestEvaluation($conn, $employee_id);
    $performance_rating = $evaluation ? $evaluation['appraisal_rating'] : "N/A";
    $summary_text = $evaluation ? $evaluation['summary_performance'] : "No evaluation record found.";
    $selected_recommendation = $evaluation ? trim($evaluation['recommendation']) : "";

  
    $recommendations = [
        "For Regularization" => "[ ] For Regularization",
        "For Promotion" => "[ ] For Promotion",
        "Continue status to improve performance" => "[ ] Continue status to improve performance",
        "For Termination" => "[ ] For Termination"
    ];

   
    foreach ($recommendations as $key => &$value) {
        if (strcasecmp($selected_recommendation, $key) === 0) { 
            $value = "[✔] $key";
        }
    }

 
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Multipliers Corp.');
    $pdf->SetTitle('Employee Evaluation Report');
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 10);

   
    $pdf->SetHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));

 
    $pdf->setHeaderFont(Array('helvetica', 'B', 14));
    $pdf->setHeaderMargin(0); 

    $pdf->AddPage();

   
    $logoPath = 'images/Logo.jpg';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 10, 40, 0, 'JPG');
    } else {
        $pdf->Cell(0, 5, 'Logo Not Found', 0, 1, 'C');
    }

  
    $pdf->SetY(15);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 5, 'MULTIPLIERS CORP.', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Mission Road Extension, Brgy. San Nicolas, LaPaz, Iloilo City', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Tel No: (033) 801-2981', 0, 1, 'C');
    $pdf->Ln(10);

    // Performance Appraisal Form Title
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'PERFORMANCE APPRAISAL FORM', 0, 1, 'C');
    $pdf->Ln(10);

    
    $pdf->SetFont('helvetica', '', 11);

    
    $pdf->SetX(15);
    $pdf->Cell(50, 6, "Employee Name:", 0, 0);
    $pdf->SetX(50);  
    $pdf->Cell(55, 6, "{$employee['first_name']} {$employee['surname']}", 0, 1);

    $pdf->SetX(15);
    $pdf->Cell(50, 6, "Job Title:", 0, 0);
    $pdf->SetX(40);  
    $pdf->Cell(90, 6, "{$employee['position']}", 0, 1);  

    $pdf->SetX(15);
    $pdf->Cell(50, 6, "Department:", 0, 0);
    $pdf->SetX(40);  
    $pdf->Cell(60, 6, "{$employee['branch']}", 0, 1);

    $pdf->SetX(15);
    $pdf->Cell(50, 6, "Review Period Coverage:", 0, 0);
    $pdf->SetX(40);  
    $pdf->Cell(60, 6, "{$employee['review_period_coverage']}", 0, 1);  

    $pdf->SetY($pdf->GetY() - 24);
    
    $pdf->SetX(98);
    $pdf->Cell(50, 6, "Last Performance Review:", 0, 0);
    $pdf->Cell(80, 6, "{$employee['last_performance_review']}", 0, 1); 

    $pdf->SetX(98);
    $pdf->Cell(50, 6, "Appraiser Name of Last Review:", 0, 0);
    $pdf->Cell(80, 6, "{$employee['appraiser_name_last_review']}", 0, 1);  

    $pdf->SetX(98);
    $pdf->Cell(50, 6, "Appraiser Name of Present Review:", 0, 0);
    $pdf->Cell(80, 6, "{$employee['appraiser_name_present_review']}", 0, 1);  

    $pdf->SetX(98);
    $pdf->Cell(50, 6, "Appraiser Job Title:", 0, 0);
    $pdf->Cell(80, 6, "{$employee['appraiser_job_title']}", 0, 1);  

    $pdf->Ln(10);  

    
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 6, 'Appraisal Rating and Description', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    
   
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(30, 6, 'Numerical Rating', 1, 0, 'C'); 
    $pdf->Cell(50, 6, 'Descriptive Rating', 1, 0, 'L'); 
    $pdf->Cell(110, 6, 'Definition of the Rating', 1, 1, 'L'); 
    
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(30, 6, '5', 1, 0, 'C');
    $pdf->Cell(50, 6, 'EXCELLENT', 1, 0, 'L');
    $pdf->Cell(110, 6, 'Definitely the best performance', 1, 1, 'L');
    
    $pdf->Cell(30, 6, '4', 1, 0, 'C');
    $pdf->Cell(50, 6, 'ABOVE SATISFACTORY', 1, 0, 'L');
    $pdf->Cell(110, 6, 'Consistently fulfills the job requirements and exceeds expectations', 1, 1, 'L');
    
    $pdf->Cell(30, 6, '3', 1, 0, 'C');
    $pdf->Cell(50, 6, 'SATISFACTORY', 1, 0, 'L');
    $pdf->Cell(110, 6, 'Consistently fulfills the job requirements and follows the standards', 1, 1, 'L');
    
    $pdf->Cell(30, 6, '2', 1, 0, 'C');
    $pdf->Cell(50, 6, 'BELOW SATISFACTORY', 1, 0, 'L');
    $pdf->Cell(110, 6, 'Consistently failure to the job requirements, needs improvement', 1, 1, 'L');
    
    $pdf->Cell(30, 6, '1', 1, 0, 'C');
    $pdf->Cell(50, 6, 'POOR', 1, 0, 'L');
    $pdf->Cell(110, 6, 'Definitely cannot perform what is expected of the job requirements', 1, 1, 'L');
    
    $pdf->Ln(10);

    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 6, "SUMMARY OF OVERALL PERFORMANCE:", 0, 0); 

    
    $pdf->SetX(93); 
    $pdf->SetFont('helvetica', 'U', 12); 
    $pdf->Cell(0, 6, "  $summary_text", 0, 1); 
    $pdf->Ln(10);
    $pdf->SetY($pdf->GetY() + 10);
  
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 6, "RECOMMENDATION", 0, 1);
    $pdf->SetFont('helvetica', '', 10);


    foreach ($recommendations as $rec) {
        $pdf->Cell(0, 6, $rec, 0, 1); 
    }
    $pdf->Ln(10);
    $pdf->SetY($pdf->GetY() + 30);
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(60, 6, "Conducted By: ____________________", 0, 1);
    $pdf->Cell(60, 6, "Department Head", 0, 1);
    $pdf->Ln(6);

    $pdf->Cell(60, 6, "Noted By: ____________________", 0, 1);
    $pdf->Cell(60, 6, "Chief Executive Officer", 0, 1);
    $pdf->Ln(10);

    $pdf->SetY($pdf->GetY() - 24);
    $pdf->SetX(98);
    $pdf->Cell(60, 6, "Reviewed By: ____________________", 0, 1);

    $pdf->SetY($pdf->GetY() - 2);
    $pdf->SetX(138);
    $pdf->Cell(60, 6, "HRD", 0, 1);
    $pdf->Ln(6);
    
  

   
    $conn->close();

   
    $filename = "{$employee['surname']}_{$employee['first_name']}_Evaluation_Report.pdf";


    $pdf->Output($filename, 'D');
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
