<?php
include('../includes/dbconn.php');

// Fetch data from the database
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
        JOIN tblemployees ON tblleaves.empid = tblemployees.id";
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Set headers for the document download
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=leave-report.doc");

// Start of the document content
echo "<html>";
echo "<head>";
echo "<style>
        body {
            font-family: Arial, sans-serif;
        }
        h2 {
            font-size: 24px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>";
echo "</head>";
echo "<body>";
echo "<h2>Employee Leave Report</h2>";
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
        $status = ($result->Status == 1) ? "Approved" : (($result->Status == 2) ? "Declined" : "Pending");
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
}
echo "</table>";
echo "</body>";
echo "</html>";
?>
