<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
$conn = mysqli_connect("localhost","root","","escr_dbase");
if(!$conn){
    echo json_encode(["error" => "DB Connection Failed!"]);
    exit;
}

#For count per A.Y
$res_year = mysqli_query($conn,"
SELECT academic_year, COUNT(*) AS total FROM student_registrations
WHERE status = 'ENROLLED'
GROUP BY academic_year
ORDER BY academic_year
");

$yearly = [];
if ($res_year) {
    while($row = mysqli_fetch_assoc($res_year)){
        $yearly[] = $row;
    }
}
#WEEKLY per ISO week of A.Y
$currentYear = date("Y");
$res_week = mysqli_query($conn,"
SELECT WEEK(date_enrolled, 1) AS week_num, COUNT(*) AS total FROM student_registrations
WHERE status = 'ENROLLED' AND YEAR(date_enrolled) = '$currentYear'
GROUP BY WEEK(date_enrolled, 1)
ORDER BY week_num
");
$weekly = [];
if ($res_week) {
    while($row = mysqli_fetch_assoc($res_week)){
        $weekly[] = $row;
    }
}
#DAILY
$res_day = mysqli_query($conn,"
SELECT DATE(date_enrolled) AS day, COUNT(*) AS total
FROM student_registrations
WHERE status = 'ENROLLED'
AND date_enrolled >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(date_enrolled)
ORDER BY day
");
$daily = [];
if ($res_day) {
    while($row = mysqli_fetch_assoc($res_day)){
        $daily[] = $row;
    }
}
echo json_encode([
    "yearly" => $yearly,
    "weekly" => $weekly,
    "daily" => $daily
])
?>