<?php
$conn = mysqli_connect("localhost","root","","escr_dbase");
include 'check_access.php';
requireRole('admin');
if (!$conn) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// Registered = all rows in student_login
$res_registered = mysqli_query($conn, "SELECT COUNT(*) AS total FROM student_login");
$row_registered = mysqli_fetch_assoc($res_registered);
$total_registered = $row_registered['total'];

// Enrolled = ENROLLED in student_registrations
$year_now = date("Y");
$current_year = $year_now . "-" . ($year_now + 1);
$month = date("n");
$current_semester = ($month >= 6 && $month <= 10) ? 1 : 2;

$res_enrolled = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM student_registrations
    WHERE academic_year = '$current_year'
      AND semester = '$current_semester'
      AND status = 'ENROLLED'
");
$row_enrolled = mysqli_fetch_assoc($res_enrolled);
$total_enrolled = $row_enrolled['total'];

echo json_encode([
    "registered" => $total_registered,
    "enrolled"   => $total_enrolled
]);
?>
