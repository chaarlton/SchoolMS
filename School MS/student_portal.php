<?php
include 'check_access.php';
requireRole('student');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$conn = mysqli_connect("localhost", "root", "", "escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
$student_id = $_SESSION['student_id'];

// ---------------------------
// Current Academic Year & Semester
// ---------------------------
$year_now = date("Y");
$current_year = $year_now . "-" . ($year_now + 1);
$month = date("n");
$current_semester = ($month >= 6 && $month <= 10) ? 1 : 2;

// ---------------------------
// Get student info
// ---------------------------
$sql_student = "SELECT * FROM student_login WHERE ID='$student_id' LIMIT 1";
$result_student = mysqli_query($conn, $sql_student);
$student = mysqli_fetch_assoc($result_student);

// ---------------------------
// Get student's enrollment for current AY & semester
// ---------------------------
$sql_enrollment = "
    SELECT * FROM student_registrations 
    WHERE student_id = '$student_id'
      AND academic_year = '$current_year'
      AND semester = '$current_semester'
      AND status = 'ENROLLED'
    LIMIT 1
";
$result_enrollment = mysqli_query($conn, $sql_enrollment);
$enrollment = mysqli_fetch_assoc($result_enrollment);

// ---------------------------
// Determine year level for display
// ---------------------------
$display_year = $enrollment['year_level'] ?? $student['yr_lvl'];

// ---------------------------
// Maps for course codes and year filters
// ---------------------------
$courseMap = [
    "INFORMATION TECHNOLOGY (BSIT)" => "IT",
    "COMPUTER SCIENCE (BSCS)" => "CS",
    "ACCOUNTANCY (BSA)" => "BSA",
];
$yearMap = [
    "1ST YEAR COLLEGE" => "1ST YEAR",
    "2ND YEAR COLLEGE" => "2ND YEAR",
    "3RD YEAR COLLEGE" => "3RD YEAR",
    "4TH YEAR COLLEGE" => "4TH YEAR",
];
$courseKey = $courseMap[$student['crs']] ?? '';

// ---------------------------
// Get all registrations for dropdown
// ---------------------------
$sql_regs = "SELECT * FROM student_registrations WHERE student_id='$student_id' ORDER BY academic_year DESC, semester DESC";
$result_regs = mysqli_query($conn, $sql_regs);
$registrations = [];
while ($reg = mysqli_fetch_assoc($result_regs)) {
    $registrations[] = $reg;
}

// ---------------------------
// Filters from GET (defaults to current enrollment)
// ---------------------------
$filter_acadyear = $_GET['academic_year'] ?? $current_year;
$filter_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : $current_semester;
$filter_year = $_GET['year'] ?? $display_year;

// ---------------------------
// Ensure student is enrolled for selected term
// ---------------------------
$enrolled_check = mysqli_query($conn, "
    SELECT * FROM student_registrations 
    WHERE student_id = '$student_id' 
      AND academic_year = '$filter_acadyear'
      AND semester = '$filter_semester'
      AND status = 'ENROLLED'
    LIMIT 1
");
if (mysqli_num_rows($enrolled_check) == 0) {
    die("⚠ You are not officially enrolled for Academic Year $filter_acadyear, Semester $filter_semester.");
}

// ---------------------------
// Fetch subjects & grades
// ---------------------------
$sql_subjects = "
    SELECT s.ID AS subject_id, s.subj_code, s.subj_desc, s.unit_count, g.grade
    FROM escr_subjects s
    LEFT JOIN student_grades g
        ON g.subject_id = s.ID
        AND g.student_id = '$student_id'
        AND g.semester = '$filter_semester'
    WHERE s.level = '$filter_year'
      AND s.subj_sort = '$courseKey'
      AND s.semester = '$filter_semester'
    ORDER BY s.subj_code
";
$result_subjects = mysqli_query($conn, $sql_subjects);
if (!$result_subjects) die("Subjects query failed: " . mysqli_error($conn));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Portal | ESCR</title>
<link rel="stylesheet" href="student.css?v13">
</head>
<body>
<div class="main">
    <div class="container">
        <div class="cover">
            <img src="EAST.JPG">
        </div>
        <div class="contents">
            <div class="left">
                <img class="profile" src="sample.png">
                <h3><?= htmlspecialchars($student['F_name'].' '.$student['M_name'].' '.$student['L_name']); ?></h3>
                <h5><?= htmlspecialchars($student['crs']); ?></h5>
                <h5><i>REGULAR STUDENT</i></h5>
                <h5><i><?= htmlspecialchars($display_year); ?> - <?= $filter_semester==1?"1st Semester":"2nd Semester"; ?></i></h5>
                <h3><i><?= htmlspecialchars($student['Student_Number']); ?></i></h3>
            </div>

            <div class="right">
                <div class="menu">
                    <button class="btn1">SUBJECTS</button>
                    <button class="btn1">DETAILS</button>
                    <button class="btn1">FILES</button>
                </div>

                <div class="Subjects">
                    <h2>📘 My Subjects</h2>

                    <form method="GET" class="filter">
                        Academic Year:
                        <select name="academic_year" required>
                            <?php foreach ($registrations as $reg): ?>
                                <option value="<?= $reg['academic_year']; ?>" <?= $reg['academic_year']==$filter_acadyear ? 'selected' : ''; ?>>
                                    <?= $reg['academic_year']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        Year:
                        <select name="year" required>
                            <?php foreach ($yearMap as $full => $short): ?>
                                <option value="<?= $short; ?>" <?= $filter_year==$short ? 'selected' : ''; ?>>
                                    <?= $short; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        Semester:
                        <select name="semester" required>
                            <option value="1" <?= $filter_semester==1 ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2" <?= $filter_semester==2 ? 'selected' : ''; ?>>2nd Semester</option>
                        </select>

                        <button type="submit">Filter</button>
                    </form>

                    <table>
                        <tr>
                            <th>SUBJECT CODE</th>
                            <th>SUBJECT DESCRIPTION</th>
                            <th>NO. OF UNITS</th>
                            <th>GRADE</th>
                        </tr>
                        <?php if(mysqli_num_rows($result_subjects) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result_subjects)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['subj_code']); ?></td>
                                <td><?= htmlspecialchars($row['subj_desc']); ?></td>
                                <td><?= $row['unit_count']; ?></td>
                                <td><?= $row['grade'] ?? 'N/A'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No subjects found for this selection.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
