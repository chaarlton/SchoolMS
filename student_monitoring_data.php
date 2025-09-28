<?php
header('Content-Type: application/json');

// DB Connection
$conn = mysqli_connect("sql112.infinityfree.com", "if0_40025418", "milkosry4", "if0_40025418_escr_dbase");
if (!$conn) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

// Current term
$year_now = date("Y");
$current_year = $year_now . "-" . ($year_now + 1);
$current_semester = (date("n") >= 6 && date("n") <= 10) ? 1 : 2;

// Filters
$search = $_GET['search'] ?? '';
$date = $_GET['date'] ?? '';

$sql = "SELECT s.*, 
        (SELECT status FROM student_registrations r 
         WHERE r.student_id = s.ID 
           AND r.academic_year='$current_year' 
           AND r.semester='$current_semester' 
         LIMIT 1) AS enrolled_status
        FROM student_login s WHERE 1";

if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (s.L_name LIKE '%$searchEsc%' 
              OR s.F_name LIKE '%$searchEsc%' 
              OR s.M_name LIKE '%$searchEsc%' 
              OR s.Student_Number LIKE '%$searchEsc%')";
}
if ($date !== '') {
    $dateEsc = mysqli_real_escape_string($conn, $date);
    $sql .= " AND s.Date = '$dateEsc'";
}
$sql .= " ORDER BY s.L_name, s.F_name";

$result = mysqli_query($conn, $sql);
$students = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $fullname = $row['L_name'] . ", " . $row['F_name'] . " " . $row['M_name'];
        $status = in_array(normalizeYearLevel($row['yr_lvl']), ["1ST YEAR","2ND YEAR","3RD YEAR","4TH YEAR"]) 
                  ? "Regular Student" 
                  : $row['status'];
        $enrolled_status = $row['enrolled_status'] ? "Enrolled" : "Not Enrolled";

        $students[] = [
            'ID' => $row['ID'],
            'fullname' => $fullname,
            'yr_lvl' => $row['yr_lvl'],
            'crs' => $row['crs'],
            'Student_Number' => $row['Student_Number'],
            'student_number' => $row['student_number'],
            'status' => $status,
            'enrolled_status' => $enrolled_status
        ];
    }
}

function normalizeYearLevel($value) {
    $map = [
        "1ST YEAR COLLEGE" => "1ST YEAR",
        "2ND YEAR COLLEGE" => "2ND YEAR",
        "3RD YEAR COLLEGE" => "3RD YEAR",
        "4TH YEAR COLLEGE" => "4TH YEAR",
        "1ST YEAR" => "1ST YEAR",
        "2ND YEAR" => "2ND YEAR",
        "3RD YEAR" => "3RD YEAR",
        "4TH YEAR" => "4TH YEAR",
    ];
    return $map[$value] ?? "1ST YEAR";
}

echo json_encode($students);
mysqli_close($conn);
?>
