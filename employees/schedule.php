<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

// Check if employee is logged in
if (strlen($_SESSION['emplogin']) == 0) {
    header('location:../index.php');
    exit();
}

// Get logged in employee ID from session (assumes it's stored as 'eid')
$empid = $_SESSION['eid'];



// Retrieve the schedule for the logged in employee
$sql = "SELECT id, shift_date, start_time, end_time, assigned_department 
        FROM tblschedule 
        WHERE EmpId = :empid 
        ORDER BY shift_date ASC";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_STR);
$query->execute();
$schedules = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Employee Schedule - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <div id="preloader">
        <div class="loader"></div>
    </div>

    <div class="page-container">
        <div class="sidebar-menu">
            <div class="sidebar-header">
                <div class="logo">
                    <a href="schedule.php"><img src="../assets/images/icon/logo.png" alt="logo"></a>
                </div>
            </div>
            <div class="main-menu">
                <div class="menu-inner">
                    <nav>
                        <ul class="metismenu" id="menu">
                            <li class="active">
                                <a href="schedule.php" aria-expanded="true"><i class="ti-calendar"></i><span>Work Schedule</span></a>
                            </li>
                            <li>
                                <a href="leave.php" aria-expanded="true"><i class="ti-agenda"></i><span>Apply Leave</span></a>
                            </li>
                            <li>
                                <a href="leave-history.php" aria-expanded="true"><i class="ti-notepad"></i><span>Leave History</span></a>
                            </li>
                        </ul>
                    </nav>
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
                        </ul>
                    </div>
                </div>
            </div>

            <div class="page-title-area">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <div class="breadcrumbs-area clearfix">
                            <h4 class="page-title pull-left">My Schedule</h4>
                            <ul class="breadcrumbs pull-left">
                                <li><a href="dashboard.php">Home</a></li>
                                <li><span>Schedule</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-6 clearfix">
                        <?php include '../includes/employee-profile-section.php'?>
                    </div>
                </div>
            </div>

            <div class="main-content-inner">
                <div class="row">
                    <div class="col-12 mt-5">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">My Work Schedule</h4>
                                <?php if(count($schedules) > 0) { ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped text-center">
                                            <thead class="text-capitalize">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Shift Date</th>
                                                    <th>Start Time</th>
                                                    <th>End Time</th>
                                                    <th>Assigned Station</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $cnt = 1; // Initialize the counter
                                                foreach ($schedules as $schedule) { 
                                                    // Format date and time
                                                    $shiftDate = (new DateTime($schedule->shift_date))->format('F j, Y');
                                                    $startTime = date("g:i A", strtotime($schedule->start_time)); // Format: 10:00 AM
                                                    $endTime = date("g:i A", strtotime($schedule->end_time)); // Format: 10:00 PM
                                                ?>
                                                    <tr>
                                                        <td><?php echo $cnt++; ?></td> <!-- Increment the counter for each row -->
                                                        <td><?php echo htmlentities($shiftDate); ?></td>
                                                        <td><?php echo htmlentities($startTime); ?></td>
                                                        <td><?php echo htmlentities($endTime); ?></td>
                                                        <td><?php echo htmlentities($schedule->assigned_department); ?></td>
                                                        <td><?php echo htmlentities($schedule->description); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <p class="text-center">No schedule available at the moment.</p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <div class="offset-area">
        <div class="offset-close"><i class="ti-close"></i></div>
    </div>

    <script src="../assets/js/vendor/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/owl.carousel.min.js"></script>
    <script src="../assets/js/metisMenu.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/jquery.slicknav.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>
