<?php
session_start();
include('../includes/dbconn.php');

// Ensure the user is logged in
if (strlen($_SESSION['emplogin']) == 0) {
    header('location:../index.php');
    exit();
}

if (isset($_GET['id'])) {
    $scheduleId = intval($_GET['id']);
    $empid = $_SESSION['eid'];

    // Secure deletion only if the schedule belongs to the employee
    $sql = "DELETE FROM tblschedule WHERE id = :id AND EmpId = :empid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $scheduleId, PDO::PARAM_INT);
    $query->bindParam(':empid', $empid, PDO::PARAM_STR);

    if ($query->execute()) {
        header("Location: schedule.php?msg=deleted");
    } else {
        echo "Failed to delete.";
    }
} else {
    echo "Invalid request.";
}
?>
