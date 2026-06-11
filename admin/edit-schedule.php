<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if(strlen($_SESSION['alogin']) == 0){
    header('location:index.php');
    exit();
}

// Ensure schedule id is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header('location:work-schedule.php');
    exit();
}

$id = intval($_GET['id']);

// Fetch current schedule details
$sql = "SELECT * FROM tblschedule WHERE id = :id";
$query = $dbh->prepare($sql);
$query->bindParam(':id', $id, PDO::PARAM_INT);
$query->execute();
$schedule = $query->fetch(PDO::FETCH_OBJ);

if(!$schedule){
    header('location:work-schedule.php');
    exit();
}

// Handle form submission
if(isset($_POST['submit'])){
    $empid = $_POST['empid'];
    $shift_date = $_POST['shift_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $assigned_department = $_POST['assigned_department'];
    $description = $_POST['description'];

    // Validation: required fields
    if(empty($empid) || empty($shift_date) || empty($start_time) || empty($end_time) || empty($assigned_department)){
        $error = "All required fields must be filled.";
    }
    // Validation: no past dates
    elseif($shift_date < date('Y-m-d')){
        $error = "Shift date must be today or a future date.";
    }
    // Validation: start time < end time
    elseif($start_time >= $end_time){
        $error = "Start time must be earlier than end time.";
    }
    // Validation: minimum 4-hour shift
    elseif(strtotime($end_time) - strtotime($start_time) < 4*3600){
        $error = "Shift duration must be at least 4 hours.";
    }

    // Validation: duplicate schedule (same employee + same date)
    if(!isset($error)){
        $sql_dup = "SELECT 1 FROM tblschedule WHERE empid = :empid AND shift_date = :shift_date AND id != :id";
        $stmt_dup = $dbh->prepare($sql_dup);
        $stmt_dup->bindParam(':empid', $empid, PDO::PARAM_INT);
        $stmt_dup->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
        $stmt_dup->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_dup->execute();

        if($stmt_dup->rowCount() > 0){
            $error = "This employee already has a schedule on this date.";
        }
    }

    // Validation: overlapping shifts
    if(!isset($error)){
        $sql_overlap = "SELECT 1 FROM tblschedule 
                        WHERE empid = :empid 
                          AND shift_date = :shift_date 
                          AND id != :id 
                          AND (:start_time < end_time AND :end_time > start_time)";
        $stmt_overlap = $dbh->prepare($sql_overlap);
        $stmt_overlap->bindParam(':empid', $empid, PDO::PARAM_INT);
        $stmt_overlap->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
        $stmt_overlap->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_overlap->bindParam(':start_time', $start_time, PDO::PARAM_STR);
        $stmt_overlap->bindParam(':end_time', $end_time, PDO::PARAM_STR);
        $stmt_overlap->execute();

        if($stmt_overlap->rowCount() > 0){
            $error = "Shift overlaps with an existing schedule for this employee.";
        }
    }

    // Update schedule if no errors
    if(!isset($error)){
        $sql_update = "UPDATE tblschedule 
                       SET empid = :empid, shift_date = :shift_date, start_time = :start_time, 
                           end_time = :end_time, assigned_department = :assigned_department, description = :description
                       WHERE id = :id";
        $stmt_update = $dbh->prepare($sql_update);
        $stmt_update->bindParam(':empid', $empid, PDO::PARAM_INT);
        $stmt_update->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
        $stmt_update->bindParam(':start_time', $start_time, PDO::PARAM_STR);
        $stmt_update->bindParam(':end_time', $end_time, PDO::PARAM_STR);
        $stmt_update->bindParam(':assigned_department', $assigned_department, PDO::PARAM_STR);
        $stmt_update->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt_update->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_update->execute();

        $success = "Schedule updated successfully!";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Schedule</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f4f9; font-family: Arial, sans-serif; }
        .container { max-width: 600px; background: #fff; padding: 30px; margin-top: 50px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        .btn-primary { width: 100%; font-size: 16px; padding: 10px; background: #007bff; border: none; border-radius: 5px; }
        .btn-primary:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Work Schedule</h2>

    <?php if(isset($error)){ ?>
        <div class="alert alert-danger text-center"><?php echo htmlentities($error); ?></div>
    <?php } ?>

    <?php if(isset($success)){ ?>
        <div class="alert alert-success text-center"><?php echo htmlentities($success); ?></div>
    <?php } ?>

    <form method="post" id="editScheduleForm">
        <div class="mb-3">
            <label class="form-label">Select Employee</label>
            <select name="empid" class="form-control" required>
                <option value="">Select Employee</option>
                <?php
                $sql = "SELECT EmpId, FirstName, LastName FROM tblemployees WHERE Status=1";
                $query = $dbh->prepare($sql);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);

                foreach($results as $result){
                    $selected = ($result->EmpId == $schedule->empid) ? 'selected' : '';
                    echo "<option value='".htmlentities($result->EmpId)."' $selected>"
                         .htmlentities($result->FirstName.' '.$result->LastName)."</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Shift Date</label>
            <input type="date" 
                   name="shift_date" 
                   class="form-control" 
                   required
                   min="<?php echo date('Y-m-d'); ?>" 
                   value="<?php echo htmlentities($schedule->shift_date); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Assigned Department</label>
            <input type="text" name="assigned_department" class="form-control" required
                   value="<?php echo htmlentities($schedule->assigned_department); ?>">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" required
                       value="<?php echo htmlentities($schedule->start_time); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" class="form-control" required
                       value="<?php echo htmlentities($schedule->end_time); ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Description (Optional)</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlentities($schedule->description); ?></textarea>
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Update Schedule</button>
        <a href="work-schedule.php" class="btn btn-secondary mt-2">Back</a>
    </form>
</div>

<script>
document.getElementById('editScheduleForm').addEventListener("submit", function(e){
    let start = document.querySelector("[name='start_time']").value;
    let end = document.querySelector("[name='end_time']").value;

    if(start >= end){
        e.preventDefault();
        alert("Start time must be earlier than end time.");
    } 
    else if ((new Date('1970-01-01T'+end+':00') - new Date('1970-01-01T'+start+':00')) < 4*3600*1000) {
        e.preventDefault();
        alert("Shift duration must be at least 4 hours.");
    }
});
</script>

</body>
</html>
