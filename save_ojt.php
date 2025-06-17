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


        $select_sql = "SELECT hours_to_achieve, remaining_minutes FROM ojt_employees WHERE id = ?";
        $stmt = $conn->prepare($select_sql);
        $stmt->bind_param('i', $ojt_id);
        $stmt->execute();
        $stmt->bind_result($current_hours, $current_minutes);
        $stmt->fetch();
        $stmt->close();


        $new_hours = $current_hours - $hours_to_subtract + $hours_to_add;
        $new_minutes = $current_minutes - $minutes_to_subtract + $minutes_to_add;


        if ($new_minutes < 0) {
            $new_hours -= 1;
            $new_minutes += 60;
        } elseif ($new_minutes >= 60) {
            $new_hours += floor($new_minutes / 60);
            $new_minutes = $new_minutes % 60;
        }


        if ($new_hours < 0) {
            $new_hours = 0;
            $new_minutes = 0;  
        }

   
        $update_sql = "UPDATE ojt_employees SET 
                        hours_to_achieve = ?, remaining_minutes = ?
                        WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('iii', $new_hours, $new_minutes, $ojt_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    echo json_encode(['success' => true]); 
}
?>
