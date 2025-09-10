<?php
    session_start();
    error_reporting(0);
    include('../includes/dbconn.php');
    if(strlen($_SESSION['alogin'])==0){   
        header('location:index.php');
    } else {
        $eid = intval($_GET['empid']);
        if(isset($_POST['update'])){
            
            $fname = $_POST['firstName'];
            $lname = $_POST['lastName'];   
            $gender = $_POST['gender']; 
            $dob = $_POST['dob']; 
            $address = $_POST['address']; 
            
            // Calculate age from DOB
            $dob_date = new DateTime($dob);
            $today = new DateTime();
            $age = $today->diff($dob_date)->y;

            if ($age < 16) {
                $error = "Employee must be at least 16 years old.";
            } else {
                $sql = "UPDATE tblemployees SET FirstName=:fname, LastName=:lname, Gender=:gender, Dob=:dob, Address=:address WHERE id=:eid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':fname', $fname, PDO::PARAM_STR);
                $query->bindParam(':lname', $lname, PDO::PARAM_STR);
                $query->bindParam(':gender', $gender, PDO::PARAM_STR);
                $query->bindParam(':dob', $dob, PDO::PARAM_STR);
                $query->bindParam(':address', $address, PDO::PARAM_STR);
                $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                $query->execute();
                
                $msg = "Employee record updated successfully.";
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
    <div id="preloader"><div class="loader"></div></div>
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
                        $page='employee';
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
                            <span></span><span></span><span></span>
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
                            <h4 class="page-title pull-left">Update Employee Section</h4>
                            <ul class="breadcrumbs pull-left"> 
                                <li><a href="employees.php">Employee</a></li>
                                <li><span>Update</span></li>
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
                    <div class="col-lg-6 col-ml-12">
                        <div class="row">
                            <div class="col-12 mt-5">
                                <?php if($error){?>
                                    <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlentities($error); ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                <?php } else if($msg){ ?>
                                    <div class="alert alert-success alert-dismissible fade show"><?php echo htmlentities($msg); ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                <?php }?>

                                <div class="card">
                                    <form name="addemp" method="POST">
                                        <div class="card-body">
                                            <p class="text-muted font-14 mb-4">Please make changes on the form below in order to update your profile</p>

                                            <?php 
                                                $eid=intval($_GET['empid']);
                                                $sql = "SELECT * from  tblemployees where id=:eid";
                                                $query = $dbh -> prepare($sql);
                                                $query -> bindParam(':eid',$eid, PDO::PARAM_STR);
                                                $query->execute();
                                                $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                if($query->rowCount() > 0)
                                                {
                                                    foreach($results as $result)
                                                    {               
                                            ?> 
<div class="form-group">
    <label class="col-form-label">Current Profile Picture</label><br>
    <?php 
        // Ensure the file exists in the specified directory
        $profilePicPath = "../employees/uploads/" . basename($result->ProfilePic); 
        if (!empty($result->ProfilePic) && file_exists($profilePicPath)) {
            // Show image if it exists
            echo '<img src="' . $profilePicPath . '" alt="Profile Picture" style="max-width: 100px; height: auto;">';
        } else {
            // Display a default image or message if no profile picture is found
            echo '<p>No profile picture uploaded.</p>';
        }
    ?>
</div>


                                            <div class="form-group">
                                                <label class="col-form-label">First Name</label>
                                                <input class="form-control" name="firstName" value="<?php echo htmlentities($result->FirstName);?>" type="text" required>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Last Name</label>
                                                <input class="form-control" name="lastName" value="<?php echo htmlentities($result->LastName);?>" type="text" required>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Email</label>
                                                <input class="form-control" name="email" type="email" value="<?php echo htmlentities($result->EmailId);?>" readonly required>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Gender</label>
                                                <select class="custom-select" name="gender">
                                                    <option value="<?php echo htmlentities($result->Gender);?>"><?php echo htmlentities($result->Gender);?></option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Date of Birth</label>
                                                <input class="form-control" type="date" name="dob" value="<?php echo htmlentities($result->Dob);?>">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Employee ID</label>
                                                <input class="form-control" name="empcode" type="text" readonly required value="<?php echo htmlentities($result->EmpId);?>">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Address</label>
                                                <input class="form-control" name="address" type="text" value="<?php echo htmlentities($result->Address);?>" required>
                                            </div>

                                            <?php }} ?>

                                            <button class="btn btn-primary" name="update" type="submit">MAKE CHANGES</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdn.zingchart.com/zingchart.min.js"></script>
    <script>
        zingchart.MODULESDIR = "https://cdn.zingchart.com/modules/";
        ZC.LICENSE = ["569d52cefae586f634c54f86dc99e6a9", "ee6b7db5b51705a13dc2339db3edaf6d"];
    </script>
    <script src="../assets/js/line-chart.js"></script>
    <script src="../assets/js/pie-chart.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>

<?php } ?>
