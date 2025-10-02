
<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if (strlen($_SESSION['emplogin']) == 0) {   
    header('location:../index.php');
} else {
    $eid = $_SESSION['emplogin'];
?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Employee Work Schedule and Leave Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="../assets/images/icon/favicon.ico">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../assets/css/metisMenu.css">
    <link rel="stylesheet" href="../assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/css/slicknav.min.css">
    <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
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
                            <li>
                                <a href="leave.php" aria-expanded="true"><i class="ti-user"></i><span>Apply Leave</span></a>
                            </li>
                            <li>
                                <a href="leave-history.php" aria-expanded="true"><i class="ti-agenda"></i><span>View My Leave History</span></a>
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
                            <h4 class="page-title pull-left">My Profile</h4>  
                        </div>
                    </div>
                    <div class="col-sm-6 clearfix">
                        <?php include '../includes/employee-profile-section.php'?>
                    </div>
                </div>
            </div>
            <div class="main-content-inner">
                <div class="row">
                    <div class="col-lg-6 col-ml-12">
                        <div class="row">
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
                                            <h4 class="header-title">Update My Profile</h4>
                                            <p class="text-muted font-14 mb-4">Please make changes on the form below in order to update your profile</p>

                                            <?php 
                                                $eid=$_SESSION['emplogin'];
                                                $sql = "SELECT * from  tblemployees where EmailId=:eid";
                                                $query = $dbh -> prepare($sql);
                                                $query -> bindParam(':eid',$eid, PDO::PARAM_STR);
                                                $query->execute();
                                                $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                $cnt=1;
                                                if($query->rowCount() > 0){
                                                    foreach($results as $result)
                                                { 
                                            ?> 
                                            <?php if (!empty($result->ProfilePic)): ?>
                                                <img src="uploads/<?php echo basename($result->ProfilePic); ?>" alt="Profile Picture" style="max-width: 100px; height: auto;">
                                            <?php else: ?>
                                                <p>No profile picture uploaded.</p>
                                            <?php endif; ?>

                                            <div class="form-group">
                                                <label for="profilePic" class="col-form-label">Upload Profile Picture</label>
                                                <input type="file" name="profilePic" id="profilePic" class="form-control-file" accept="image/*">
                                            </div>

                                            <div class="form-group">
                                                <label for="example-text-input" class="col-form-label">First Name</label>
                                                <input class="form-control" name="firstName" value="<?php echo htmlentities($result->FirstName);?>"  type="text" required id="example-text-input">
                                            </div>

                                            <div class="form-group">
                                                <label for="example-text-input" class="col-form-label">Last Name</label>
                                                <input class="form-control" name="lastName" value="<?php echo htmlentities($result->LastName);?>" type="text" autocomplete="off" required id="example-text-input">
                                            </div>

                                            <div class="form-group">
                                                <label for="example-email-input" class="col-form-label">Email</label>
                                                <input class="form-control" name="email" type="email"  value="<?php echo htmlentities($result->EmailId);?>" readonly autocomplete="off" required id="example-email-input">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Gender</label>
                                                <select class="custom-select" name="gender" autocomplete="off">
                                                    <option value="<?php echo htmlentities($result->Gender);?>"><?php echo htmlentities($result->Gender);?></option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="example-date-input" class="col-form-label">Date of Birth</label>
                                                <input class="form-control" type="date" name="dob" id="birthdate" value="<?php echo htmlentities($result->Dob);?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="example-text-input" class="col-form-label">Employee ID</label>
                                                <input class="form-control" name="empcode" type="text" autocomplete="off" readonly required value="<?php echo htmlentities($result->EmpId);?>" id="example-text-input">
                                            </div>

                                            <div class="form-group">
                                                <label for="example-text-input" class="col-form-label">Address</label>
                                                <input class="form-control" name="address" type="text"  value="<?php echo htmlentities($result->Address);?>" autocomplete="off" required>
                                            </div>

                                            <?php }
                                            }?>

                                            <button class="btn btn-primary" name="update" id="update" type="submit">MAKE CHANGES</button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include '../includes/footer.php' ?>
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
<?php  
    if(isset($_POST['update'])){
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $gender = $_POST['gender'];
        $dob = $_POST['dob'];
        $empcode = $_POST['empcode'];
        $address = $_POST['address'];

        if ($_FILES['profilePic']['name']) {
            $file = $_FILES['profilePic'];
            $fileName = $_FILES['profilePic']['name'];
            $fileTmpName = $_FILES['profilePic']['tmp_name'];
            $fileSize = $_FILES['profilePic']['size'];
            $fileError = $_FILES['profilePic']['error'];
            $fileType = $_FILES['profilePic']['type'];

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));
            $allowed = array('jpg', 'jpeg', 'png');

            if (in_array($fileActualExt, $allowed)) {
                if ($fileError === 0) {
                    if ($fileSize < 1000000) {
                        $fileNameNew = uniqid('', true).".".$fileActualExt;
                        $fileDestination = 'uploads/'.$fileNameNew;
                        move_uploaded_file($fileTmpName, $fileDestination);

                        $sql = "UPDATE tblemployees SET FirstName=:firstName, LastName=:lastName, EmailId=:email, Gender=:gender, Dob=:dob, EmpId=:empcode, Address=:address, ProfilePic=:profilePic WHERE EmailId=:eid";
                        $query = $dbh -> prepare($sql);
                        $query -> bindParam(':firstName', $firstName, PDO::PARAM_STR);
                        $query -> bindParam(':lastName', $lastName, PDO::PARAM_STR);
                        $query -> bindParam(':email', $email, PDO::PARAM_STR);
                        $query -> bindParam(':gender', $gender, PDO::PARAM_STR);
                        $query -> bindParam(':dob', $dob, PDO::PARAM_STR);
                        $query -> bindParam(':empcode', $empcode, PDO::PARAM_STR);
                        $query -> bindParam(':address', $address, PDO::PARAM_STR);
                        $query -> bindParam(':profilePic', $fileNameNew, PDO::PARAM_STR);
                        $query -> bindParam(':eid', $eid, PDO::PARAM_STR);
                        $query -> execute();

                        // Refresh page to show updated image
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit;
                    } else {
                        $error = "Your file is too big!";
                    }
                } else {
                    $error = "There was an error uploading your file!";
                }
            } else {
                $error = "You cannot upload files of this type!";
            }
        } else {
            // If no profile picture is uploaded, update other fields only
            $sql = "UPDATE tblemployees SET FirstName=:firstName, LastName=:lastName, EmailId=:email, Gender=:gender, Dob=:dob, EmpId=:empcode, Address=:address WHERE EmailId=:eid";
            $query = $dbh -> prepare($sql);
            $query -> bindParam(':firstName', $firstName, PDO::PARAM_STR);
            $query -> bindParam(':lastName', $lastName, PDO::PARAM_STR);
            $query -> bindParam(':email', $email, PDO::PARAM_STR);
            $query -> bindParam(':gender', $gender, PDO::PARAM_STR);
            $query -> bindParam(':dob', $dob, PDO::PARAM_STR);
            $query -> bindParam(':empcode', $empcode, PDO::PARAM_STR);
            $query -> bindParam(':address', $address, PDO::PARAM_STR);
            $query -> bindParam(':eid', $eid, PDO::PARAM_STR);
            $query -> execute();

            // Refresh page to show updated data
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
} // <-- CLOSES the 'else {' from the top
?>
