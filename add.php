<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $updatedData = json_decode($_POST['updatedData'], true);

    if (empty($updatedData)) {
        echo json_encode(['success' => false, 'error' => 'No data to update']);
        exit;
    }

    $conn = new mysqli('localhost', 'root', '', 'employee_evaluation');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    
    foreach ($updatedData as $data) {
        $ojt_id = $data['ojt_id'];
        $hours_to_subtract = $data['hours'];
        $minutes_to_subtract = $data['minutes'];
        $hours_to_add = $data['add_hours'];
        $minutes_to_add = $data['add_minutes'];

        $update_sql = "UPDATE ojt_employees SET 
                        hours_to_achieve = hours_to_achieve - ? + ?,
                        remaining_minutes = remaining_minutes - ? + ?
                        WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('iiiiii', $hours_to_subtract, $hours_to_add, $minutes_to_subtract, $minutes_to_add, $ojt_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    echo json_encode(['success' => true]);  
}
?>
