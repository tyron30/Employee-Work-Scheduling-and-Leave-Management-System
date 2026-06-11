<?php
// Include your database connection
include('../includes/dbconn.php');

// Set the content type to JSON
header('Content-Type: application/json; charset=utf-8');

// Optional: Hide warnings or notices from breaking JSON output (especially for production)
ini_set('display_errors', 0);
error_reporting(0);

// Check if 'shift_date' was posted
if (isset($_POST['shift_date'])) {
    $shift_date = $_POST['shift_date'];

    try {
        // Query to get only employees who do NOT have a schedule on the selected date
        $sql = "SELECT EmpId, FirstName, LastName 
                FROM tblemployees 
                WHERE Status = 1 
                AND EmpId NOT IN (
                    SELECT empid FROM tblschedule WHERE shift_date = :shift_date
                )";

        $query = $dbh->prepare($sql);
        $query->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        // Build a clean array of employee data
        $employees = [];
        foreach ($results as $row) {
            $employees[] = [
                'EmpId' => $row->EmpId,
                'FullName' => $row->FirstName . ' ' . $row->LastName
            ];
        }

        // Send the JSON response
        echo json_encode($employees);

    } catch (Exception $e) {
        // Send error as JSON
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }

} else {
    // If 'shift_date' is missing from POST
    http_response_code(400);
    echo json_encode(['error' => 'Missing shift_date parameter.']);
}
?>
