<?php
include('../includes/dbconn.php');

// Fetch only approved leave data from the database
$sql = "SELECT 
            tblemployees.FirstName, 
            tblemployees.LastName, 
            tblemployees.EmpId, 
            tblleaves.LeaveType, 
            tblleaves.FromDate, 
            tblleaves.ToDate, 
            tblleaves.PostingDate, 
            tblleaves.Status 
        FROM tblleaves 
        JOIN tblemployees ON tblleaves.empid = tblemployees.id
        WHERE tblleaves.Status = 1"; // Filter for approved status
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Set headers for the document download
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=approved-leave-report.doc");

echo "<html>";
echo "<head>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; font-size: 12pt; }";
echo "table { border-collapse: collapse; width: 100%; }";
echo "th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }";
echo "h2 { font-size: 16pt; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<h2>Approved Employee Leave Report</h2>";
echo "<table>";
echo "<tr>
        <th>Employee ID</th>
        <th>Full Name</th>
        <th>Leave Type</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Applied On</th>
        <th>Status</th>
      </tr>";

if ($query->rowCount() > 0) {
    foreach ($results as $result) {
        // Determine the status label
        $status = ($result->Status == 1) ? "Approved" : "Unknown";
        echo "<tr>
                <td>{$result->EmpId}</td>
                <td>{$result->FirstName} {$result->LastName}</td>
                <td>{$result->LeaveType}</td>
                <td>{$result->FromDate}</td>
                <td>{$result->ToDate}</td>
                <td>{$result->PostingDate}</td>
                <td>{$status}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7'>No approved leave requests found.</td></tr>";
}
echo "</table>";
echo "</body>";
echo "</html>";
?>
