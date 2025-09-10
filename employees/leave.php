<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if (strlen($_SESSION['emplogin']) == 0) {
    header('location:../index.php');
    exit();
}

if (isset($_POST['apply'])) {
    $empid = $_SESSION['eid'];
    $leavetype = $_POST['leavetype'];
    $fromdate = $_POST['fromdate'];
    $todate = $_POST['todate'];
    $description = $_POST['description'];
    $status = 0; // Pending status
    $isread = 0;

    // Handle file upload
    if (isset($_FILES['proof']['name']) && $_FILES['proof']['error'] == 0) {
        $fileName = $_FILES['proof']['name'];
        $fileTmpPath = $_FILES['proof']['tmp_name'];
        $fileSize = $_FILES['proof']['size'];
        $fileType = $_FILES['proof']['type'];
        
        // Define the directory for file upload
        $uploadDir = '../uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate a unique file name to avoid name conflicts
        $fileNameNew = uniqid() . '-' . basename($fileName);
        $fileDestPath = $uploadDir . $fileNameNew;
        
        // Move the uploaded file to the server
        if (!move_uploaded_file($fileTmpPath, $fileDestPath)) {
            $error = "Error: File upload failed.";
        }
    } else {
        $fileDestPath = ''; // No file uploaded
    }
$currentDate = new DateTime(); // Current date
$startDate = new DateTime($fromdate);
$endDate = new DateTime($todate);

// Calculate the minimum allowable start date (3 days from today)
$minStartDate = (clone $currentDate)->modify('+3 days');

// Calculate leave duration (inclusive)
$leaveDays = $startDate->diff($endDate)->days + 1;

// Validation: Start date and end date must not be the same
if ($startDate == $endDate) {
    $error = "Error: Start date and end date cannot be the same.";
} elseif ($startDate < $minStartDate) {
    $error = "Error: Leave applications must be submitted at least 3 days in advance.";
} elseif ($startDate > $endDate) {
    $error = "Error: The start date cannot be later than the end date.";
} elseif ($leaveDays < 5) {
    $error = "Error: The minimum leave duration is 5 days.";
} else {
   
        // Check for existing pending leave requests
        $checkSql = "SELECT COUNT(*) AS pendingCount 
                     FROM tblleaves 
                     WHERE empid = :empid 
                       AND Status = 0";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':empid', $empid, PDO::PARAM_STR);
        $checkQuery->execute();
        $pendingResult = $checkQuery->fetch(PDO::FETCH_ASSOC);

        if ($pendingResult['pendingCount'] > 0) {
            $error = "Error: You already have a pending leave request.";
        } else {
            // Check for overlapping approved leaves
            $overlapSql = "SELECT COUNT(*) AS overlapCount 
                           FROM tblleaves 
                           WHERE empid = :empid 
                             AND Status = 1 
                             AND ((FromDate <= :todate AND ToDate >= :fromdate))";
            $overlapQuery = $dbh->prepare($overlapSql);
            $overlapQuery->bindParam(':empid', $empid, PDO::PARAM_STR);
            $overlapQuery->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
            $overlapQuery->bindParam(':todate', $todate, PDO::PARAM_STR);
            $overlapQuery->execute();
            $overlapResult = $overlapQuery->fetch(PDO::FETCH_ASSOC);

            if ($overlapResult['overlapCount'] > 0) {
                $error = "Error: Your leave dates overlap with an approved leave.";
            } else {
                // Proceed to insert leave application
                $sql = "INSERT INTO tblleaves (LeaveType, ToDate, FromDate, Description, Status, IsRead, empid, ProofFile) 
                        VALUES (:leavetype, :todate, :fromdate, :description, :status, :isread, :empid, :prooffile)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':leavetype', $leavetype, PDO::PARAM_STR);
                $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
                $query->bindParam(':todate', $todate, PDO::PARAM_STR);
                $query->bindParam(':description', $description, PDO::PARAM_STR);
                $query->bindParam(':status', $status, PDO::PARAM_INT);
                $query->bindParam(':isread', $isread, PDO::PARAM_INT);
                $query->bindParam(':empid', $empid, PDO::PARAM_STR);
                $query->bindParam(':prooffile', $fileDestPath, PDO::PARAM_STR);

                if ($query->execute()) {
                    $msg = "Your leave application has been submitted successfully.";
                } else {
                    $error = "Sorry, could not process your request. Please try again later.";
                }
            }
        }
    }
}

?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Employee Leave Request and Status Tracking System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="../assets/images/icon/favicon.ico">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../assets/css/metisMenu.css">
    <link rel="stylesheet" href="../assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/css/slicknav.min.css">
    <!-- amchart css -->
    <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
    <!-- others css -->
    <link rel="stylesheet" href="../assets/css/typography.css">
    <link rel="stylesheet" href="../assets/css/default-css.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <!-- modernizr css -->
    <script src="../assets/js/vendor/modernizr-2.8.3.min.js"></script>
    <script type="text/javascript">
    document.getElementById("apply").addEventListener("click", function (event) {
        const fromdate = document.getElementsByName("fromdate")[0].value;
        const todate = document.getElementsByName("todate")[0].value;

        const startDate = new Date(fromdate);
        const endDate = new Date(todate);
        const dayDiff = (endDate - startDate) / (1000 * 3600 * 24);

        if (dayDiff < 3) {
            alert("Error: Leave applications must be submitted at least 3 days in advance.");
            event.preventDefault(); // Prevent form submission
        } else if (startDate > endDate) {
            alert("Error: The start date cannot be later than the end date.");
            event.preventDefault();
        }
    });
    </script>
</head>

<body>
    <!-- preloader area start -->
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <!-- preloader area end -->
    <!-- page container area start -->
    <div class="page-container">
        <!-- sidebar menu area start -->
        <div class="sidebar-menu">
            <div class="sidebar-header">
                <div class="logo">
                    <a href="leave.php"><img src="../assets/images/icon/logo.png" alt="logo"></a>
                </div>
            </div>
            <div class="main-menu">
                <div class="menu-inner">
                    <nav>
                        <ul class="metismenu" id="menu">
                        <li class="work">
                                <a href="schedule.php" aria-expanded="true"><i class="ti-user"></i><span>Work Schedule</span></a>
                            </li>
                            <li class="active">
                                <a href="leave.php" aria-expanded="true"><i class="ti-user"></i><span>Apply Leave</span></a>
                            </li>
                            <li class="#">
                                <a href="leave-history.php" aria-expanded="true"><i class="ti-agenda"></i><span>View My Leave</span></a>
                            </li>


                
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <!-- sidebar menu area end -->
        <!-- main content area start -->
        <div class="main-content">
            <!-- header area start -->
            <div class="header-area">
                <div class="row align-items-center">
                    <!-- nav and search button -->
                    <div class="col-md-6 col-sm-8 clearfix">
                        <div class="nav-btn pull-left">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <!-- profile info & task notification -->
                    <div class="col-md-6 col-sm-4 clearfix">
                        <ul class="notification-area pull-right">
                            <li id="full-view"><i class="ti-fullscreen"></i></li>
                            <li id="full-view-exit"><i class="ti-zoom-out"></i></li>
                         
                        </ul>
                    </div>
                </div>
            </div>
            <!-- header area end -->
            <!-- page title area start -->
            <div class="page-title-area">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <div class="breadcrumbs-area clearfix">
                            <h4 class="page-title pull-left">Apply For Leave Days</h4>
                            <ul class="breadcrumbs pull-left">
                                <li><span>Leave Form</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-6 clearfix">
                        <?php include '../includes/employee-profile-section.php'?>
                    </div>
                </div>
            </div>
            <!-- page title area end -->
            <div class="main-content-inner">
                <div class="row">
                    <div class="col-lg-6 col-ml-12">
                        <div class="row">
                            <!-- Textual inputs start -->
                            <div class="col-12 mt-5">
                            <?php if($error){?><div class="alert alert-danger alert-dismissible fade show"><strong></strong><?php echo htmlentities($error); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                             </div><?php } 
                                 else if($msg){?><div class="alert alert-success alert-dismissible fade show"><strong> </strong><?php echo htmlentities($msg); ?> 
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                 </div><?php }?>
                                <div class="card">
                                <form name="addemp" method="POST" enctype="multipart/form-data">

                                    <div class="card-body">
                                        <h4 class="header-title">Employee Leave Form</h4>
                                        <p class="text-muted font-14 mb-4">Please fill up the form below.</p>

                                        <?php
                                       $currentDate = date('Y-m-d'); // Correct format for HTML date input
                                       ?>
                                       <div class="form-group">
                                           <label for="fromdate" class="col-form-label">Starting Date</label>
                                           <input class="form-control" type="date" value="<?php echo $currentDate; ?>" required id="fromdate" name="fromdate">
                                       </div>
                                       
                                       <div class="form-group">
                                           <label for="todate" class="col-form-label">End Date</label>
                                           <input class="form-control" type="date" value="<?php echo $currentDate; ?>" required id="todate" name="todate">
                                       </div>

                                        <div class="form-group">
                                            <label class="col-form-label">Your Leave Type</label>
                                            <select class="custom-select" name="leavetype" autocomplete="off">
                                                <option value="">Click here to select any ...</option>
                                                <?php $sql = "SELECT LeaveType from tblleavetype";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                $cnt=1;
                                                if($query->rowCount() > 0) {
                                                    foreach($results as $result)
                                                {   ?> 
                                                <option value="<?php echo htmlentities($result->LeaveType);?>"><?php echo htmlentities($result->LeaveType);?></option>
                                                <?php }
                                                } ?>
                                            </select>
                                        </div>

                                        <!-- New file input for leave proof -->
                                        <div class="form-group">
                                            <label for="proof" class="col-form-label">Upload Leave Proof (Optional)</label>
                                            <input type="file" class="form-control" name="proof" id="proof">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Describe Your Conditions</label>
                                            <textarea class="form-control" name="description" rows="5"></textarea>
                                        </div>

                                        <button class="btn btn-primary" name="apply" id="apply" type="submit">SUBMIT</button>
                                    </div>
                                </form>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- main content area end -->
        <!-- footer area start-->
        <?php include '../includes/footer.php' ?>
        <!-- footer area end-->
    </div>
    <!-- page container area end -->
    <!-- offset area start -->
    <div class="offset-area">
        <div class="offset-close"><i class="ti-close"></i></div>
    </div>
    <!-- offset area end -->
    <!-- jquery latest version -->
    <script src="../assets/js/vendor/jquery-2.2.4.min.js"></script>
    <!-- bootstrap 4 js -->
    <script src="../assets/js/popper.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/owl.carousel.min.js"></script>
    <script src="../assets/js/metisMenu.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/jquery.slicknav.min.js"></script>
    <!-- others plugins -->
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>

</html>
