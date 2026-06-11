<?php
// Always start with clean headers and error handling
header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // Prevent warnings from breaking JSON
ini_set('display_errors', 0);

include('../includes/dbconn.php'); // Ensure this doesn't output anything (even a space)

// Check if the POST parameter exists
if (!isset($_POST['shift_date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing shift_date parameter.']);
    exit();
}

$shift_date = $_POST['shift_date'];

try {
    // Query: Departments not yet assigned on the selected shift_date
    $sql = "SELECT DepartmentName 
            FROM tbldepartments 
            WHERE DepartmentName NOT IN (
                SELECT assigned_department 
                FROM tblschedule 
                WHERE shift_date = :shift_date
            )";

    $query = $dbh->prepare($sql);
    $query->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
    $query->execute();

    $departments = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($departments);
    exit();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit();
}
?>
