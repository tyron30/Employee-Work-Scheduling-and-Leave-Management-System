<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

$msg = '';
$error = '';

if (isset($_POST['submit'])) {
    $empid = $_POST['empid'];
    $shift_date = $_POST['shift_date'];

    // Determine start time
    if ($_POST['start_time_select'] === 'manual') {
        $start_time = $_POST['start_time'];
    } else {
        $start_time = $_POST['start_time_select'];
    }

    // Determine end time
    if ($_POST['start_time_select'] === 'manual') {
        $end_time = $_POST['end_time_manual'];
    } else {
        $end_time = $_POST['end_time'];
    }

    $assigned_department = $_POST['assigned_department'];
    $description = $_POST['description'];

    // Basic validation
    if (empty($empid) || empty($shift_date) || empty($start_time) || empty($end_time) || empty($assigned_department)) {
        $error = "All required fields must be filled.";
    } 
    // Prevent past dates
    elseif ($shift_date < date('Y-m-d')) {
        $error = "Shift date must be today or a future date.";
    } 
    // Start < End time
    elseif ($start_time >= $end_time) {
        $error = "Start time must be earlier than end time.";
    } 
    // Minimum shift duration: 4 hours
    elseif (strtotime($end_time) - strtotime($start_time) < 4 * 3600) {
        $error = "Shift duration must be at least 4 hours.";
    }

    if ($error === '') {
        // Check if employee already has a schedule on that day
        $sql_check_emp = "SELECT 1 FROM tblschedule WHERE empid = :empid AND shift_date = :shift_date";
        $query_check_emp = $dbh->prepare($sql_check_emp);
        $query_check_emp->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query_check_emp->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
        $query_check_emp->execute();

        if ($query_check_emp->rowCount() > 0) {
            $error = "This employee already has a schedule on the selected date.";
        } else {
            // Check if the assigned department is already scheduled during overlapping time
            $sql_check_station = "SELECT 1 FROM tblschedule 
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
                $error = "That station is already assigned during this time on the selected date.";
            } else {
                // Insert new schedule
                $sql_insert = "INSERT INTO tblschedule (empid, shift_date, start_time, end_time, assigned_department, description) 
                               VALUES (:empid, :shift_date, :start_time, :end_time, :assigned_department, :description)";
                $query_insert = $dbh->prepare($sql_insert);
                $query_insert->bindParam(':empid', $empid, PDO::PARAM_STR);
                $query_insert->bindParam(':shift_date', $shift_date, PDO::PARAM_STR);
                $query_insert->bindParam(':start_time', $start_time, PDO::PARAM_STR);
                $query_insert->bindParam(':end_time', $end_time, PDO::PARAM_STR);
                $query_insert->bindParam(':assigned_department', $assigned_department, PDO::PARAM_STR);
                $query_insert->bindParam(':description', $description, PDO::PARAM_STR);
                $query_insert->execute();

                $msg = "Schedule added successfully!";
            }
        }
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
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🗓 Add Work Schedule</h2>

    <?php if ($msg): ?>
        <div class="alert alert-success text-center"><?php echo htmlentities($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?php echo htmlentities($error); ?></div>
    <?php endif; ?>

    <form method="POST" id="scheduleForm">
        <div class="mb-3">
            <label class="form-label">Shift Date</label>
            <input type="date" name="shift_date" class="form-control" required id="shiftDate" 
                   min="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Select Employee</label>
            <select name="empid" class="form-control" required id="employeeSelect">
                <option value="">Select a shift date first</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Assigned Station</label>
            <select name="assigned_department" class="form-control" required id="stationSelect">
                <option value="">Select a shift date first</option>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Time</label>
                <select name="start_time_select" class="form-control" required id="startTimeSelect">
                    <option value="">Select Start Time</option>
                    <option value="05:00">Opening (5:00am)</option>
                    <option value="11:00">Mid Shift (11:00am)</option>
                    <option value="18:00">Closing (6:00pm)</option>
                    <option value="00:00">Graveyard (12:00am)</option>
                    <option value="manual">Manual</option>
                </select>
            </div>
            <div class="col-md-6 mb-3" id="endTimeContainer">
                <label class="form-label">End Time</label>
                <select name="end_time" class="form-control" required id="endTimeSelect">
                    <option value="">Select End Time</option>
                    <option value="11:00">Opening (11:00am)</option>
                    <option value="18:00">Mid Shift (6:00pm)</option>
                    <option value="00:00">Closing (12:00am)</option>
                    <option value="06:00">Graveyard (6:00am)</option>
                </select>
            </div>
        </div>

        <div class="row" id="manualTimeFields" style="display: none;">
            <div class="col-md-6 mb-3">
                <label class="form-label">Manual Start Time</label>
                <input type="time" name="start_time" class="form-control" id="manualStartTime">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Manual End Time</label>
                <input type="time" name="end_time_manual" class="form-control" id="manualEndTime">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Description (Optional)</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <a href="work-schedule.php" class="btn btn-secondary mb-3">⬅️ Back</a>
        <button type="submit" name="submit" class="btn btn-primary">✅ Add Schedule</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const shiftDate = document.getElementById('shiftDate');
    const employeeSelect = document.getElementById('employeeSelect');
    const stationSelect = document.getElementById('stationSelect');

    // Set default date to today
    shiftDate.value = new Date().toISOString().split('T')[0];

    shiftDate.addEventListener('change', () => {
        const selectedDate = shiftDate.value;

        if (!selectedDate) return;

        // Fetch available employees
        fetch('get-available-employees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'shift_date=' + encodeURIComponent(selectedDate)
        })
        .then(res => res.ok ? res.json() : Promise.reject(res))
        .then(data => {
            employeeSelect.innerHTML = '<option value="">Select Employee</option>';
            if (data.length === 0) {
                employeeSelect.innerHTML = '<option value="">No available employees</option>';
                return;
            }
            data.forEach(emp => {
                const option = document.createElement('option');
                option.value = emp.EmpId;
                option.textContent = emp.FullName;
                employeeSelect.appendChild(option);
            });
        })
        .catch(err => {
            console.error('Employee fetch error:', err);
            employeeSelect.innerHTML = '<option value="">Error loading employees</option>';
        });

        // Fetch available stations
        fetch('get-available-stations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'shift_date=' + encodeURIComponent(selectedDate)
        })
        .then(res => res.ok ? res.json() : Promise.reject(res))
        .then(data => {
            stationSelect.innerHTML = '<option value="">Select Station</option>';
            if (data.length === 0) {
                stationSelect.innerHTML = '<option value="">No available stations</option>';
                return;
            }
            data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.DepartmentName;
                option.textContent = dept.DepartmentName;
                stationSelect.appendChild(option);
            });
        })
        .catch(err => {
            console.error('Station fetch error:', err);
            stationSelect.innerHTML = '<option value="">Error loading stations</option>';
        });
    });

    // Handle start time selection
    document.getElementById('startTimeSelect').addEventListener('change', function() {
        const selected = this.value;
        const endTimeContainer = document.getElementById('endTimeContainer');
        const endTimeSelect = document.getElementById('endTimeSelect');
        const manualFields = document.getElementById('manualTimeFields');

        if (selected === 'manual') {
            manualFields.style.display = 'block';
            endTimeContainer.style.display = 'none';
            endTimeSelect.required = false;
            document.getElementById('manualStartTime').required = true;
            document.getElementById('manualEndTime').required = true;
        } else {
            manualFields.style.display = 'none';
            endTimeContainer.style.display = 'block';
            endTimeSelect.required = true;
            document.getElementById('manualStartTime').required = false;
            document.getElementById('manualEndTime').required = false;

            // Auto-set end time based on start time
            switch(selected) {
                case '05:00': // Opening
                    endTimeSelect.value = '11:00';
                    break;
                case '11:00': // Mid Shift
                    endTimeSelect.value = '18:00';
                    break;
                case '18:00': // Closing
                    endTimeSelect.value = '00:00';
                    break;
                case '00:00': // Graveyard
                    endTimeSelect.value = '06:00';
                    break;
            }
        }
    });

    // Form validation for time and minimum shift
    document.getElementById('scheduleForm').addEventListener('submit', function (e) {
        let start, end;
        const startSelect = document.getElementById('startTimeSelect').value;

        if (startSelect === 'manual') {
            start = document.getElementById('manualStartTime').value;
            end = document.getElementById('manualEndTime').value;
        } else {
            start = startSelect;
            end = document.getElementById('endTimeSelect').value;
        }

        if (!start || !end) {
            e.preventDefault();
            alert('Please select or enter start and end times.');
            return;
        }

        if (start >= end) {
            e.preventDefault();
            alert('Start time must be earlier than end time.');
        } else if ((new Date('1970-01-01T' + end + ':00') - new Date('1970-01-01T' + start + ':00')) < 4 * 3600 * 1000) {
            e.preventDefault();
            alert('Shift duration must be at least 4 hours.');
        }
    });
});
</script>
</body>
</html>
