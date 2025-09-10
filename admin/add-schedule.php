<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
} else {
    if (isset($_POST['submit'])) {
        $empid = $_POST['empid'];
        $shift_date = $_POST['shift_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $assigned_department = $_POST['assigned_department'];
        $description = $_POST['description'];

        // Server-side validation
        $error = '';
        if (empty($empid) || empty($shift_date) || empty($start_time) || empty($end_time) || empty($assigned_department)) {
            $error = "All fields are required.";
        } elseif ($shift_date < date('Y-m-d')) {
            $error = "Shift date must be a future date.";
        } elseif ($start_time >= $end_time) {
            $error = "Start time must be earlier than end time.";
        }

        if ($error == '') {
            // Check if the employee already has a schedule for the selected shift date
            $sql_check_emp = "SELECT * FROM tblschedule WHERE empid = :empid AND shift_date = :shift_date";
            $query_check_emp = $dbh->prepare($sql_check_emp);
            $query_check_emp->bindParam(':empid', $empid, PDO::PARAM_INT);
            $query_check_emp->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
            $query_check_emp->execute();

            if ($query_check_emp->rowCount() > 0) {
                $error = "Error: This employee already has a schedule for the selected date.";
            } else {
                // Check if the assigned station already has a schedule at the same time on the same date
                $sql_check_station = "SELECT * FROM tblschedule 
                WHERE assigned_department = :assigned_department 
                AND shift_date = :shift_date 
                AND (:start_time < end_time AND :end_time > start_time)";
            $query_check_station = $dbh->prepare($sql_check_station);
            $query_check_station->bindParam(':assigned_department', $assigned_department, PDO::PARAM_STR);
            $query_check_station->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
            $query_check_station->bindParam(':start_time', $start_time, PDO::PARAM_STR);
            $query_check_station->bindParam(':end_time', $end_time, PDO::PARAM_STR);
            $query_check_station->execute();
            

                if ($query_check_station->rowCount() > 0) {
                    $error = "Error: The assigned station already has a schedule at this time on the selected date.";
                } else {
                    // Insert the schedule if no conflict
                    $sql = "INSERT INTO tblschedule (empid, shift_date, start_time, end_time, assigned_department, description) 
                            VALUES (:empid, :shift_date, :start_time, :end_time, :assigned_department, :description)";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':empid', $empid, PDO::PARAM_INT);
                    $query->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
                    $query->bindParam(':start_time', $start_time, PDO::PARAM_STR);
                    $query->bindParam(':end_time', $end_time, PDO::PARAM_STR);
                    $query->bindParam(':assigned_department', $assigned_department, PDO::PARAM_STR);
                    $query->bindParam(':description', $description, PDO::PARAM_STR);
                    $query->execute();
                    $msg = "Schedule Added Successfully!";
                }
            }
        } else {
            $error = "Error: " . $error;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add Schedule</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f9;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            background: #fff;
            padding: 30px;
            margin-top: 50px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .btn-primary {
            width: 100%;
            font-size: 16px;
            padding: 10px;
            background: #007bff;
            border: none;
            border-radius: 5px;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .alert {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🗓 Add Work Schedule</h2>

    <?php if ($msg) { ?>
    <div class="alert alert-success">
        <?php echo htmlentities($msg); ?>
    </div>
    <?php } ?>
    <?php if ($error) { ?>
    <div class="alert alert-danger">
        <?php echo htmlentities($error); ?>
    </div>
    <?php } ?>

    <form method="POST" id="scheduleForm">
        <div class="mb-3">
            <label class="form-label">Select Employee</label>
            <select name="empid" class="form-control" required>
                <option value="">Select Employee</option>
                <?php
                $sql = "SELECT EmpId, FirstName, LastName FROM tblemployees WHERE Status=1";
                $query = $dbh->prepare($sql);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                if ($query->rowCount() > 0) {
                    foreach ($results as $result) { ?>
                        <option value="<?php echo htmlentities($result->EmpId); ?>">
                            <?php echo htmlentities($result->FirstName . ' ' . $result->LastName); ?>
                        </option>
                <?php }} ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Assigned Station</label>
            <select name="assigned_department" class="form-control" required>
                <option value="">Select Station</option>
                <?php
                $sql = "SELECT * FROM tbldepartments";
                $query = $dbh->prepare($sql);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                if ($query->rowCount() > 0) {
                    foreach ($results as $result) { ?>
                        <option value="<?php echo htmlentities($result->DepartmentName); ?>">
                            <?php echo htmlentities($result->DepartmentName); ?>
                        </option>
                <?php }} ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Shift Date</label>
            <input type="date" name="shift_date" class="form-control" required id="shiftDate">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Description (Optional)</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <a href="work-schedule.php" class="btn btn-secondary mb-3">⬅️ Back to Schedule List</a>
        <button type="submit" name="submit" class="btn btn-primary">✅ Add Schedule</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let today = new Date().toISOString().split('T')[0];
        document.getElementById("shiftDate").value = today;
    });

    // Client-side validation for time
    document.getElementById('scheduleForm').onsubmit = function(e) {
        const start_time = document.querySelector('[name="start_time"]').value;
        const end_time = document.querySelector('[name="end_time"]').value;

        if (start_time >= end_time) {
            e.preventDefault();
            alert("Start time must be earlier than end time.");
        }
    };
</script>

</body>
</html>

<?php } ?>
