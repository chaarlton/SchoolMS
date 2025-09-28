<?php
include 'check_access.php';
requireRole('admin');

$conn = mysqli_connect("sql112.infinityfree.com", "if0_40025418", "milkosry4", "if0_40025418_escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Get student ID from query
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($student_id <= 0) die("Invalid student ID.");

// Get student info
$sql_student = "SELECT * FROM student_login WHERE ID='$student_id'";
$result_student = mysqli_query($conn, $sql_student);
if (!$result_student) die("Query failed: " . mysqli_error($conn));
$student = mysqli_fetch_assoc($result_student);

// Year & course mapping
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
$yearKey = $yearMap[$student['yr_lvl']] ?? '';

// Get filters from GET
$filter_year = $_GET['year'] ?? $yearKey;
$filter_semester = $_GET['semester'] ?? 1;
$filter_semester = (int)$filter_semester;

// Fetch subjects for the selected year and semester
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
<title>Admin - Student Grades</title>
<link rel="stylesheet" href="admin.css?v1">
  <link rel="shortcut icon" href="Picture3.png" type="image/x-icon">
<style>
table { border-collapse: collapse; width:100%; margin-top:20px; }
th, td { border:1px solid #ddd; padding:8px; } th { background:#2c3e50; color:#fff; }
tr:nth-child(even) { background:#f9f9f9; } tr:hover { background:#f1f1f1; }
input[type=text] { width:60px; padding:4px; text-align:center; }
form.filter { margin-bottom:15px; }
h1,h2,h3,h4,h5{
    text-transform: uppercase;
}
</style>
</head>
<body>
<a href="admin.php" class="home-btn">← Home to Dashboard</a>
<h2>Grades for <?php echo $student['F_name'].' '.$student['L_name']; ?></h2>

<!-- Filter Form -->
<form method="GET" class="filter">
    <input type="hidden" name="id" value="<?php echo $student_id; ?>">
    Year: 
    <select name="year" required>
        <?php foreach ($yearMap as $full => $short): ?>
            <option value="<?php echo $short; ?>" <?php if($filter_year==$short) echo 'selected'; ?>>
                <?php echo $short; ?>
            </option>
        <?php endforeach; ?>
    </select>
    Semester:
    <select name="semester" required>
        <option value="1" <?php if($filter_semester==1) echo 'selected'; ?>>1st Semester</option>
        <option value="2" <?php if($filter_semester==2) echo 'selected'; ?>>2nd Semester</option>
    </select>
    <button type="submit">Filter</button>
</form>

<!-- Grades Form -->
<form method="POST" action="save_grades.php">
    <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
    <input type="hidden" name="semester" value="<?php echo $filter_semester; ?>">
    <input type="hidden" name="year" value="<?php echo htmlspecialchars($filter_year); ?>">
    <table>
        <tr>
            <th>Code</th>
            <th>Description</th>
            <th>Units</th>
            <th>Grade</th>
        </tr>
        <?php if(mysqli_num_rows($result_subjects)>0): ?>
            <?php while($row = mysqli_fetch_assoc($result_subjects)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['subj_code']); ?></td>
                <td><?php echo htmlspecialchars($row['subj_desc']); ?></td>
                <td><?php echo $row['unit_count']; ?></td>
                <td>
                    <input type="text" name="grades[<?php echo $row['subject_id']; ?>]" 
                           value="<?php echo $row['grade'] ?? ''; ?>" size="5">
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No subjects found for this year/semester.</td></tr>
        <?php endif; ?>
    </table>
    <button type="submit">💾 Save Grades</button>
</form>
</body>
</html>
