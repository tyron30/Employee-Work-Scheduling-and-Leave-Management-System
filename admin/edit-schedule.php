<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if(strlen($_SESSION['alogin']) == 0){
    header('location:index.php');
} else {
    // Ensure schedule id is provided
    if(!isset($_GET['id']) || empty($_GET['id'])){
        header('location:work-schedule.php');
        exit();
    }
    
    $id = intval($_GET['id']);
    
    // If form submitted, update schedule
    if(isset($_POST['submit'])){
        $empid = $_POST['empid'];
        $shift_date = $_POST['shift_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $description = $_POST['description'];
        
        $sql = "UPDATE tblschedule SET empid = :empid, shift_date = :shift_date, start_time = :start_time, end_time = :end_time, description = :description WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_INT);
        $query->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
        $query->bindParam(':start_time', $start_time, PDO::PARAM_STR);
        $query->bindParam(':end_time', $end_time, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $msg = "Schedule updated successfully.";
        header('location:work-schedule.php');
        exit();
    }
    
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
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Schedule</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f9;
            font-family: Arial, sans-serif;
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
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Work Schedule</h2>
    <?php if(isset($msg)){ ?>
      <div class="alert alert-success">
          <?php echo htmlentities($msg); ?>
      </div>
    <?php } ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Select Employee</label>
            <select name="empid" class="form-control" required>
                <option value="">Select Employee</option>
                <?php
                $sql = "SELECT EmpId, FirstName, LastName FROM tblemployees WHERE Status=1";
                $query = $dbh->prepare($sql);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                if($query->rowCount() > 0){
                    foreach($results as $result){
                        // Pre-select the employee based on the current schedule
                        $selected = ($result->EmpId == $schedule->empid) ? 'selected' : '';
                        echo "<option value='" . htmlentities($result->EmpId) . "' $selected>";
                        echo htmlentities($result->FirstName . ' ' . $result->LastName);
                        echo "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Shift Date</label>
            <input type="date" name="shift_date" class="form-control" required value="<?php echo htmlentities($schedule->shift_date); ?>">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" required value="<?php echo htmlentities($schedule->start_time); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" class="form-control" required value="<?php echo htmlentities($schedule->end_time); ?>">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>
