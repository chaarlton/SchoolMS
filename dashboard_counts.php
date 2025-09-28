<?php
$conn = mysqli_connect("sql112.infinityfree.com", "if0_40025418", "milkosry4", "if0_40025418_escr_dbase");
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

// F2F enrolled
$res_f2f = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM student_registrations
    WHERE academic_year = '$current_year'
      AND semester = '$current_semester'
      AND status = 'ENROLLED'
      AND mol = 'F2F'
");
$row_f2f = mysqli_fetch_assoc($res_f2f);
$total_f2f = $row_f2f['total'];

// Online enrolled
$res_online = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM student_registrations
    WHERE academic_year = '$current_year'
      AND semester = '$current_semester'
      AND status = 'ENROLLED'
      AND mol = 'ONLINE'
");
$row_online = mysqli_fetch_assoc($res_online);
$total_online = $row_online['total'];

// Course counts
function getCountByCourse($conn, $course, $current_year, $current_semester){
  $course = mysqli_real_escape_string($conn, $course);
  $sql = "SELECT COUNT(*) AS total FROM student_registrations WHERE course='$course' AND academic_year='$current_year' AND semester='$current_semester' AND status='ENROLLED'";
  $res = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($res);
  return $row['total'] ?? 0;
}
$bsit_students   = getCountByCourse($conn, "INFORMATION TECHNOLOGY (BSIT)", $current_year, $current_semester);
$bscs_students   = getCountByCourse($conn, "COMPUTER SCIENCE (BSCS)", $current_year, $current_semester);
$it_cs_students  = $bsit_students + $bscs_students;
$bsba_fm         = getCountByCourse($conn, "BSBA - MAJOR IN FM", $current_year, $current_semester);
$bsba_mm         = getCountByCourse($conn, "BSBA - MAJOR IN MM", $current_year, $current_semester);
$bsba_hrdm       = getCountByCourse($conn, "BSBA - MAJOR IN HRDM", $current_year, $current_semester);
$btvted_fsm      = getCountByCourse($conn, "BTVTED - FSM", $current_year, $current_semester);
$btvted_elec     = getCountByCourse($conn, "BTVTED - ELEC", $current_year, $current_semester);
$bsais_students  = getCountByCourse($conn, "BSAIS - ACCOUNTING INFORMATION SYSTEM", $current_year, $current_semester);
$bsoa_students   = getCountByCourse($conn, "BSOA - OFFICE ADMINISTRATION", $current_year, $current_semester);
$shs_humss       = getCountByCourse($conn, "SHS - HUMSS", $current_year, $current_semester);
$shs_abm         = getCountByCourse($conn, "SHS - ABM", $current_year, $current_semester);
$shs_ict         = getCountByCourse($conn, "SHS - ICT", $current_year, $current_semester);
$bsba_total      = $bsba_fm + $bsba_hrdm + $bsba_mm;
$btvted_students = $btvted_elec + $btvted_fsm;

// SHS breakdown
function getSHSCount($conn, $strand, $grade, $current_year, $current_semester){
    $strand = mysqli_real_escape_string($conn, $strand);
    $grade  = mysqli_real_escape_string($conn, $grade);
    $sql = "SELECT COUNT(*) AS total 
            FROM student_registrations 
            WHERE course='$strand' AND year_level='$grade' AND academic_year='$current_year' AND semester='$current_semester' AND status='ENROLLED'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['total'] ?? 0;
}
$shs_humss_g11 = getSHSCount($conn, "SHS - HUMSS", "GRADE 11", $current_year, $current_semester);
$shs_humss_g12 = getSHSCount($conn, "SHS - HUMSS", "GRADE 12", $current_year, $current_semester);
$shs_abm_g11   = getSHSCount($conn, "SHS - ABM", "GRADE 11", $current_year, $current_semester);
$shs_abm_g12   = getSHSCount($conn, "SHS - ABM", "GRADE 12", $current_year, $current_semester);
$shs_ict_g11   = getSHSCount($conn, "SHS - ICT", "GRADE 11", $current_year, $current_semester);
$shs_ict_g12   = getSHSCount($conn, "SHS - ICT", "GRADE 12", $current_year, $current_semester);

$shs_grade11_total = $shs_humss_g11 + $shs_abm_g11 + $shs_ict_g11;
$shs_grade12_total = $shs_humss_g12 + $shs_abm_g12 + $shs_ict_g12;

echo json_encode([
    "registered" => $total_registered,
    "enrolled"   => $total_enrolled,
    "f2f"        => $total_f2f,
    "online"     => $total_online,
    "it_cs"      => $it_cs_students,
    "bsba"       => $bsba_total,
    "bsais"      => $bsais_students,
    "btvted"     => $btvted_students,
    "shs_g11"    => $shs_grade11_total,
    "shs_g12"    => $shs_grade12_total
]);
?>
