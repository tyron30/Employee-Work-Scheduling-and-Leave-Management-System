<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:../index.php');
    exit();
}

if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo "No date provided.";
    exit();
}

$inputDate = DateTime::createFromFormat('F j, Y', $_GET['date']);
if (!$inputDate) {
    echo "Invalid date format.";
    exit();
}
$shiftDateFormatted = $inputDate->format('Y-m-d');

$sql = "SELECT s.id, s.shift_date, s.start_time, s.end_time, s.description, 
               e.FirstName, e.LastName, s.assigned_department 
        FROM tblschedule s 
        INNER JOIN tblemployees e ON s.EmpId = e.EmpId 
        WHERE s.shift_date = :shift_date 
        ORDER BY s.start_time ASC";
$query = $dbh->prepare($sql);
$query->bindParam(':shift_date', $shiftDateFormatted, PDO::PARAM_STR);
$query->execute();
$schedules = $query->fetchAll(PDO::FETCH_OBJ);

function categorizeShift($startTime) {
    $start = new DateTime($startTime);
    $hour = (int)$start->format('H');
    if ($hour >= 5 && $hour < 11) return 'Opening Shift';
    elseif ($hour >= 11 && $hour < 18) return 'Mid Shift';
    elseif ($hour >= 18 && $hour < 24) return 'Closing Shift';
    else return 'Graveyard Shift';
}

$categorized = [
    'Opening Shift' => [],
    'Mid Shift' => [],
    'Closing Shift' => [],
    'Graveyard Shift' => []
];

foreach ($schedules as $sched) {
    $cat = categorizeShift($sched->start_time);
    $categorized[$cat][] = $sched;
}

$displayDate = $inputDate->format('F j, Y');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print Schedule - <?php echo $displayDate; ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 40px;
            background-color: #fffceb;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #ffcc00;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header img {
            width: 100px;
        }
        .header h2 {
            margin: 10px 0 5px;
            font-size: 28px;
            color: #d71a28;
        }
        .header h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #ffe600;
            color: #000;
        }
        h4 {
            margin-top: 40px;
            color: #d71a28;
            border-bottom: 2px solid #d71a28;
            padding-bottom: 5px;
        }
        .noprint {
            text-align: right;
            margin-bottom: 20px;
        }
        .noprint button {
            background-color: #d71a28;
            color: white;
            border: none;
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }
        .noprint button:hover {
            background-color: #b01622;
        }
        @media print {
            .noprint { display: none; }
        }
    </style>
</head>
<body>
    <div class="noprint">
        <button onclick="window.print()">🖨️ Print This Page</button>
    </div>

    <div class="header">
        <!-- Use local path or absolute URL to McDonald's logo -->
        <img src="../assets/images/mcdo-logo.png" alt="McDonald's Logo">
        <h2>McDonald's Work Schedule</h2>
        <h3>Date: <?php echo $displayDate; ?></h3>
    </div>

    <?php foreach ($categorized as $shiftType => $entries): ?>
        <?php if (count($entries) > 0): ?>
            <h4><?php echo $shiftType; ?></h4>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee Name</th>
                        <th>Assigned Station</th>
                        <th>Shift Time</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $cnt = 1;
                    foreach ($entries as $row): 
                        $start = (new DateTime($row->start_time))->format('h:i A');
                        $end = (new DateTime($row->end_time))->format('h:i A');
                    ?>
                        <tr>
                            <td><?php echo $cnt++; ?></td>
                            <td><?php echo htmlentities($row->FirstName . ' ' . $row->LastName); ?></td>
                            <td><?php echo htmlentities($row->assigned_department); ?></td>
                            <td><?php echo $start . " - " . $end; ?></td>
                            <td><?php echo htmlentities($row->description); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>

    <p style="margin-top: 40px;">Generated by: Admin</p>
</body>
</html>
