<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
if(strlen($_SESSION['alogin'])==0){   
    header('location:index.php');
} else {
    if(isset($_POST['add'])){
        
        // Get the posted form data
        $deptname = $_POST['departmentname'];
        $deptshortname = $_POST['departmentshortname'];


        // Check if a department with the same name or code already exists
        $sql_check = "SELECT * FROM tbldepartments WHERE DepartmentName = :deptname";
        $query_check = $dbh->prepare($sql_check);
        $query_check->bindParam(':deptname', $deptname, PDO::PARAM_STR);
    
        $query_check->execute();
        $result_check = $query_check->fetch(PDO::FETCH_ASSOC);

        if ($result_check) {
            // If a department with the same name or code exists, show error
            $error = "A station with this name or code already exists!";
        } else {
            // If no duplication, proceed with inserting the new department
            $sql = "INSERT INTO tbldepartments(DepartmentName, DepartmentShortName) 
                    VALUES(:deptname,:deptshortname)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':deptname', $deptname, PDO::PARAM_STR);
            $query->bindParam(':deptshortname', $deptshortname, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $dbh->lastInsertId();

            if($lastInsertId){
                $msg = "Station Created Successfully";
                // Redirect to department.php with success message
                header("Location: department.php?msg=" . urlencode($msg));
                exit();  // Make sure to stop further script execution
            } else {
                $error = "Something went wrong. Please try again";
            }
        }
    }
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Admin Panel - Employee Leave</title>
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
                    <a href="dashboard.php"><img src="../assets/images/icon/logo.png" alt="logo"></a>
                </div>
            </div>
            <div class="main-menu">
                <div class="menu-inner">
                    <?php
                        $page='department';
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
                            <h4 class="page-title pull-left">Station Section</h4>
                            <ul class="breadcrumbs pull-left">
                                <li><a href="department.php">Station</a></li>
                                <li><span>Add</span></li>
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
                            <?php if($error){?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong> </strong><?php echo htmlentities($error); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php } 
                            else if($msg){?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <strong> </strong><?php echo htmlentities($msg); ?> 
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php }?>
                            
                            <form method="POST">
                                <div class="card-body">
                                    <p class="text-muted font-14 mb-4">Please fill up the form in order to add new station</p>

                                    <div class="form-group">
                                        <label for="example-text-input" class="col-form-label">Station Name</label>
                                        <input class="form-control" name="departmentname" type="text" required id="example-text-input" >
                                    </div>

                                    <div class="form-group">
                                        <label for="example-text-input" class="col-form-label">Shortform</label>
                                        <input class="form-control" name="departmentshortname" type="text" autocomplete="off" required id="example-text-input" >
                                    </div>

                                
                                    <button class="btn btn-primary" name="add" id="add" type="submit">ADD</button>
                                </div>
                            </form>
                        </div> 
                    </div>
                </div>
            </div>

            <?php include '../includes/footer.php' ?>
        </div>
    </div>

    <script src="../assets/js/vendor/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/owl.carousel.min.js"></script>
    <script src="../assets/js/metisMenu.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/jquery.slicknav.min.js"></script>
    <script src="assets/js/line-chart.js"></script>
    <script src="assets/js/pie-chart.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>

<?php } ?>
