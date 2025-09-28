<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
$conn = mysqli_connect("sql112.infinityfree.com", "if0_40025418", "milkosry4", "if0_40025418_escr_dbase");
if(!$conn){
    echo json_encode(["error" => "DB Connection Failed!"]);
    exit;
}

// Current term
$year_now = date("Y");
$current_year = $year_now . "-" . ($year_now + 1);
$month = date("n");
$current_semester = ($month >= 6 && $month <= 10) ? 1 : 2;

// Counts
$res_registered = mysqli_query($conn, "SELECT COUNT(*) AS total_registered FROM student_login");
$row_registered = mysqli_fetch_assoc($res_registered);
$total_registered = $row_registered['total_registered'];

$res_enrolled = mysqli_query($conn, "SELECT COUNT(*) AS total_enrolled FROM student_registrations WHERE academic_year = '$current_year' AND semester='$current_semester' AND status = 'ENROLLED'");
$row_enrolled = mysqli_fetch_assoc($res_enrolled);
$total_enrolled = $row_enrolled['total_enrolled'];

function getCountByCourse($conn, $course){
    $course = mysqli_real_escape_string($conn, $course);
    $sql = "SELECT COUNT(*) AS total FROM student_registrations WHERE course='$course'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['total'] ?? 0;
}
$bsit_students = getCountByCourse($conn, "INFORMATION TECHNOLOGY (BSIT)");
$bscs_students = getCountByCourse($conn, "COMPUTER SCIENCE (BSCS)");
$it_cs_students = $bsit_students + $bscs_students;
$bsba_fm = getCountByCourse($conn, "BSBA - MAJOR IN FM");
$bsba_mm = getCountByCourse($conn, "BSBA - MAJOR IN MM");
$bsba_hrdm = getCountByCourse($conn, "BSBA - MAJOR IN HRDM");
$btvted_fsm = getCountByCourse($conn, "BTVTED - FSM");
$btvted_elec = getCountByCourse($conn, "BTVTED - ELEC");
$bsais_students = getCountByCourse($conn, "BSAIS - ACCOUNTING INFORMATION SYSTEM");
$bsoa_students = getCountByCourse($conn, "BSOA - OFFICE ADMINISTRATION");
$shs_humss = getCountByCourse($conn, "SHS - HUMSS");
$shs_abm = getCountByCourse($conn, "SHS - ABM");
$shs_ict = getCountByCourse($conn, "SHS - ICT");
$bsba_total = $bsba_fm + $bsba_mm + $bsba_hrdm;
$btvted_students = $btvted_elec + $btvted_fsm;

// SHS breakdown
function getSHSCount($conn, $strand, $grade){
    $strand = mysqli_real_escape_string($conn, $strand);
    $grade = mysqli_real_escape_string($conn, $grade);
    $sql = "SELECT COUNT(*) AS total FROM student_login WHERE crs='$strand' AND yr_lvl='$grade'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['total'] ?? 0;
}
$shs_humss_g11 = getSHSCount($conn, "SHS - HUMSS", "GRADE 11");
$shs_humss_g12 = getSHSCount($conn, "SHS - HUMSS", "GRADE 12");
$shs_abm_g11 = getSHSCount($conn, "SHS - ABM", "GRADE 11");
$shs_abm_g12 = getSHSCount($conn, "SHS - ABM", "GRADE 12");
$shs_ict_g11 = getSHSCount($conn, "SHS - ICT", "GRADE 11");
$shs_ict_g12 = getSHSCount($conn, "SHS - ICT", "GRADE 12");

$shs_grade11_total = $shs_humss_g11 + $shs_abm_g11 + $shs_ict_g11;
$shs_grade12_total = $shs_humss_g12 + $shs_abm_g12 + $shs_ict_g12;

// Mode of learning
function getModeOfLearning($conn, $mol){
    $mol = mysqli_real_escape_string($conn, $mol);
    $sql = "SELECT COUNT(*) AS total FROM student_registrations WHERE mol = '$mol'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['total'] ?? 0;
}
$f2f = getModeOfLearning($conn, "F2F");
$online = getModeOfLearning($conn, "ONLINE");

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
SELECT WEEK(date_registered, 1) AS week_num, COUNT(*) AS total FROM student_registrations
WHERE status = 'ENROLLED' AND YEAR(date_registered) = '$currentYear'
GROUP BY WEEK(date_registered, 1)
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
SELECT DATE(date_registered) AS day, COUNT(*) AS total
FROM student_registrations
WHERE status = 'ENROLLED'
AND date_registered >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(date_registered)
ORDER BY day
");
$daily = [];
if ($res_day) {
    while($row = mysqli_fetch_assoc($res_day)){
        $daily[] = $row;
    }
}
echo json_encode([
    "registered" => $total_registered,
    "enrolled" => $total_enrolled,
    "f2f" => $f2f,
    "online" => $online,
    "it_cs" => $it_cs_students,
    "bsba" => $bsba_total,
    "bsais" => $bsais_students,
    "btvted" => $btvted_students,
    "shs_g11" => $shs_grade11_total,
    "shs_g12" => $shs_grade12_total,
    "yearly" => $yearly,
    "weekly" => $weekly,
    "daily" => $daily
])
?>
