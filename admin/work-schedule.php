<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if(strlen($_SESSION['alogin']) == 0) {   
    header('location:index.php');
    exit();
}

// Delete Schedule
if(isset($_GET['delid'])) {
    $id = $_GET['delid'];
    $sql = "DELETE FROM tblschedule WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_STR);
    $query->execute();
    header('location:work-schedule.php');
}

// Fetch all employees for selection dropdown
$sqlEmployees = "SELECT EmpId, FirstName, LastName FROM tblemployees ORDER BY FirstName ASC";
$queryEmployees = $dbh->prepare($sqlEmployees);
$queryEmployees->execute();
$employees = $queryEmployees->fetchAll(PDO::FETCH_OBJ);

// Get selected employee ID from GET request
$selectedEmpId = isset($_GET['empid']) ? $_GET['empid'] : "";

// Fetch schedules (filtered if employee is selected)
$scheduleQuery = "SELECT s.id, s.shift_date, s.start_time, s.end_time, s.description, 
                          e.FirstName, e.LastName, s.assigned_department 
                  FROM tblschedule s 
                  INNER JOIN tblemployees e ON s.EmpId = e.EmpId";

if (!empty($selectedEmpId)) {
    $scheduleQuery .= " WHERE s.EmpId = :empid";
}

$scheduleQuery .= " ORDER BY s.shift_date ASC";

$querySchedule = $dbh->prepare($scheduleQuery);

if (!empty($selectedEmpId)) {
    $querySchedule->bindParam(':empid', $selectedEmpId, PDO::PARAM_STR);
}

$querySchedule->execute();
$schedules = $querySchedule->fetchAll(PDO::FETCH_OBJ);

// Function to categorize shifts
function categorizeShift($startTime) {
    $start = new DateTime($startTime);
    $hour = (int)$start->format('H');
    
    // Adjusted Shift Times
    if ($hour >= 5 && $hour < 11) {
        return 'Opening Shift';
    } elseif ($hour >= 11 && $hour < 18) {
        return 'Mid Shift';
    } elseif ($hour >= 18 && $hour < 24) {
        return 'Closing Shift';
    } else {
        return 'Graveyard Shift';
    }
}

// Group schedules by shift date
$groupedSchedules = [];

foreach ($schedules as $schedule) {
   $shiftDate = (new DateTime($schedule->shift_date))->format('F j, Y');

    $shiftCategory = categorizeShift($schedule->start_time);
    
    if (!isset($groupedSchedules[$shiftDate])) {
        $groupedSchedules[$shiftDate] = [
            'Opening Shift' => [],
            'Mid Shift' => [],
            'Closing Shift' => [],
            'Graveyard Shift' => []
        ];
    }
    
    $groupedSchedules[$shiftDate][$shiftCategory][] = $schedule;
}
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Panel - Work Schedule</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/icon/favicon.ico">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../assets/css/metisMenu.css">
    <link rel="stylesheet" href="../assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/css/slicknav.min.css">
    <link rel="stylesheet" href="../assets/css/typography.css">
    <link rel="stylesheet" href="../assets/css/default-css.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <script src="../assets/js/vendor/modernizr-2.8.3.min.js"></script>
</head>

<body>

<div class="page-container">
    <div class="sidebar-menu">
        <div class="sidebar-header">
            <div class="logo">
                <a href="dashboard.php"><img src="../assets/images/icon/logo.png" alt="logo"></a>
            </div>
        </div>
        <div class="main-menu">
            <div class="menu-inner">
                <?php
                    $page = 'work-schedule';
                    include '../includes/admin-sidebar.php';
                ?>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="header-area">
            <div class="row align-items-center">
                <div class="col-md-6 col-sm-8 clearfix">
                    <div class="nav-btn pull-left">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
                <div class="col-md-6 col-sm-4 clearfix">
                    <ul class="notification-area pull-right">
                        <li id="full-view"><i class="ti-fullscreen"></i></li>
                        <li id="full-view-exit"><i class="ti-zoom-out"></i></li>
                        <?php include '../includes/admin-notification.php'?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-title-area">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <div class="breadcrumbs-area clearfix">
                        <h4 class="page-title pull-left">Work Schedule Section</h4>
                        <ul class="breadcrumbs pull-left">
                            <li><a href="dashboard.php">Home</a></li>
                            <li><span>Work Schedule Management</span></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-6 clearfix">
                    <div class="user-profile pull-right">
                        <img class="avatar user-thumb" src="../assets/images/admin.png" alt="avatar">
                        <h4 class="user-name dropdown-toggle" data-toggle="dropdown">ADMIN <i class="fa fa-angle-down"></i></h4>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="logout.php">Log Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content-inner">
            <div class="row">
                <div class="col-12 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="data-tables datatable-dark">
                                <center>
                                    <a href="add-schedule.php" class="btn btn-sm btn-info">Add New Schedule</a>
                                </center>

                                <!-- Employee Selection Dropdown -->
                                <form method="GET" action="work-schedule.php" class="mt-3">
                                    <label>Select Employee:</label>
                                    <select name="empid" class="form-control" onchange="this.form.submit()">
                                        <option value="">All Employees</option>
                                        <?php foreach ($employees as $employee) { ?>
                                            <option value="<?php echo $employee->EmpId; ?>" 
                                                <?php echo ($selectedEmpId == $employee->EmpId) ? "selected" : ""; ?>>
                                                <?php echo htmlentities($employee->FirstName . " " . $employee->LastName); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </form>

                                <!-- Displaying schedules by date and shift category -->
                                <?php foreach ($groupedSchedules as $shiftDate => $shiftCategories) { ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-4">
    <h3>Schedules for <?php echo $shiftDate; ?></h3>
    <a href="print-schedule.php?date=<?php echo urlencode($shiftDate); ?>" 
       class="btn btn-sm btn-success" target="_blank">
        <i class="fa fa-print"></i> Print Schedule
    </a>
</div>

                                    
                                    <!-- Display Opening Shift with table headers -->
                                    <?php if (count($shiftCategories['Opening Shift']) > 0) { ?>
                                        <h4>Opening Shift</h4>
                                        <table class='table table-hover table-striped text-center mt-3'>
                                            <thead>
                                                <tr><th>#</th><th>Name</th><th>Assigned Station</th><th>Shift Time</th><th>Description</th><th>Action</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $cnt = 1;
                                                foreach ($shiftCategories['Opening Shift'] as $schedule) {
                                                    $startTime = new DateTime($schedule->start_time);
                                                    $endTime = new DateTime($schedule->end_time);
                                                    $formattedStartTime = $startTime->format('h:i A');
                                                    $formattedEndTime = $endTime->format('h:i A');
                                                ?>  
                                                <tr>
                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                    <td><?php echo htmlentities($schedule->FirstName . ' ' . $schedule->LastName); ?></td>
                                                    <td><?php echo htmlentities($schedule->assigned_department); ?></td>
                                                    <td><?php echo htmlentities($formattedStartTime . ' - ' . $formattedEndTime); ?></td>
                                                    <td><?php echo htmlentities($schedule->description); ?></td>
                                                    <td>
                                                        <a href="edit-schedule.php?id=<?php echo htmlentities($schedule->id); ?>" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <a href="work-schedule.php?delid=<?php echo htmlentities($schedule->id); ?>" 
                                                           onclick="return confirm('Are you sure you want to delete this schedule?');"
                                                           class="btn btn-sm btn-danger">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php $cnt++; } ?>
                                            </tbody>
                                        </table>
                                    <?php } ?>

                                   <!-- Display Mid, Closing, and Graveyard shifts WITH table headers -->
<?php foreach (['Mid Shift', 'Closing Shift', 'Graveyard Shift'] as $shiftName) { ?>
    <?php if (count($shiftCategories[$shiftName]) > 0) { ?>
        <h4><?php echo $shiftName; ?></h4>
        <table class='table table-hover table-striped text-center mt-3'>
            <thead>
                <tr><th>#</th><th>Name</th><th>Assigned Station</th><th>Shift Time</th><th>Description</th><th>Action</th></tr>
            </thead>
            <tbody>

                                                    <?php 
                                                    $cnt = 1;
                                                    foreach ($shiftCategories[$shiftName] as $schedule) {
                                                        $startTime = new DateTime($schedule->start_time);
                                                        $endTime = new DateTime($schedule->end_time);
                                                        $formattedStartTime = $startTime->format('h:i A');
                                                        $formattedEndTime = $endTime->format('h:i A');
                                                    ?>  
                                                    <tr>
                                                        <td><?php echo htmlentities($cnt); ?></td>
                                                        <td><?php echo htmlentities($schedule->FirstName . ' ' . $schedule->LastName); ?></td>
                                                        <td><?php echo htmlentities($schedule->assigned_department); ?></td>
                                                        <td><?php echo htmlentities($formattedStartTime . ' - ' . $formattedEndTime); ?></td>
                                                        <td><?php echo htmlentities($schedule->description); ?></td>
                                                        <td>
                                                            <a href="edit-schedule.php?id=<?php echo htmlentities($schedule->id); ?>" class="btn btn-sm btn-primary">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                            <a href="work-schedule.php?delid=<?php echo htmlentities($schedule->id); ?>" 
                                                               onclick="return confirm('Are you sure you want to delete this schedule?');"
                                                               class="btn btn-sm btn-danger">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php $cnt++; } ?>
                                                </tbody>
                                            </table>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
