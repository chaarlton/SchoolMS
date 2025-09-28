<?php
include 'check_access.php';
requireRole('admin');

$conn = mysqli_connect("localhost","root","","escr_dbase");
if (!$conn) die("DB connect error: " . mysqli_connect_error());

// Calculate current academic year and semester
$year_now = date("Y");
$current_year = $year_now . "-" . ($year_now + 1);
$month = date("n");
$current_semester = ($month >= 6 && $month <= 10) ? 1 : 2;

$error = "";
$success = "";
$step = 1; // 1: select student/year/sem, 2: select subjects
$selected_student = null;
$selected_year = $current_year;
$selected_semester = $current_semester;
$subjects = [];

// Handle form submits
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == 1) {
        // Step 1: Get student, year, sem
        $student_id = (int)$_POST['student_id'];
        $selected_year = trim($_POST['academic_year']);
        $selected_semester = (int)$_POST['semester'];

        if ($student_id <= 0) {
            $error = "Please select a student.";
        } else {
            // Fetch student info
            $sel = $conn->prepare("SELECT ID, F_name, L_name, crs, yr_lvl FROM student_login WHERE ID = ?");
            $sel->bind_param("i", $student_id);
            $sel->execute();
            $res = $sel->get_result();
            $selected_student = $res->fetch_assoc();
            if (!$selected_student) {
                $error = "Student not found.";
            } else {
                $step = 2;
                // Fetch all subjects with enrollment check
                $subjects_query = "SELECT s.*, g.ID as enrolled_id FROM escr_subjects s LEFT JOIN student_grades g ON g.subject_id = s.ID AND g.student_id = ? ORDER BY s.level, s.subj_sort, s.semester, s.subj_code";
                $subjects_stmt = $conn->prepare($subjects_query);
                $subjects_stmt->bind_param("i", $student_id);
                $subjects_stmt->execute();
                $subjects_res = $subjects_stmt->get_result();
                $subjects = [];
                while ($row = $subjects_res->fetch_assoc()) {
                    $subjects[] = $row;
                }
            }
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == 2) {
        // Step 2: Process selected subjects
        $student_id = (int)$_POST['student_id'];
        $selected_year = trim($_POST['academic_year']);
        $selected_semester = (int)$_POST['semester'];
        $selected_subjects = $_POST['subjects'] ?? [];

        if ($student_id <= 0) {
            $error = "Invalid student.";
        } elseif (empty($selected_subjects)) {
            $error = "Please select at least one subject.";
        } else {
            // Fetch student info
            $sel = $conn->prepare("SELECT crs, yr_lvl FROM student_login WHERE ID = ?");
            $sel->bind_param("i", $student_id);
            $sel->execute();
            $res = $sel->get_result();
            $student_info = $res->fetch_assoc();
            if (!$student_info) {
                $error = "Student not found.";
            } else {
                // Insert/update student_registrations
                $reg_sql = "INSERT INTO student_registrations (student_id, academic_year, semester, year_level, course, status, enrollment_type)
                            VALUES (?, ?, ?, ?, ?, 'ENROLLED', 'irregular')
                            ON DUPLICATE KEY UPDATE status='ENROLLED', year_level=VALUES(year_level), course=VALUES(course), enrollment_type='irregular'";
                $reg_stmt = $conn->prepare($reg_sql);
                $reg_stmt->bind_param("sisss", $student_id, $selected_year, $selected_semester, $student_info['yr_lvl'], $student_info['crs']);
                if (!$reg_stmt->execute()) {
                    $error = "Failed to update registration: " . $reg_stmt->error;
                } else {
                    // Insert subjects into student_grades
                    $grade_sql = "INSERT IGNORE INTO student_grades (student_id, academic_year, subject_id, semester, grade)
                                  VALUES (?, ?, ?, ?, '')";
                    $grade_stmt = $conn->prepare($grade_sql);
                    $inserted = 0;
                    foreach ($selected_subjects as $subj_id) {
                        $subj_id = (int)$subj_id;
                        $grade_stmt->bind_param("isii", $student_id, $selected_year, $subj_id, $selected_semester);
                        if ($grade_stmt->execute()) {
                            $inserted++;
                        }
                    }
                    $success = "Enrolled in $inserted subject(s) successfully.";
                }
            }
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Setup Irregular Student</title>
<link rel="stylesheet" href="admin.css?v1">
<link rel="shortcut icon" href="Picture3.png" type="image/x-icon">
<style>
.form { max-width: 1200px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #333; }
label { display: block; margin-top: 10px; font-weight: bold; color: #555; }
input[type="text"], select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
button:hover { background-color: #0056b3; }
a { color: #007bff; text-decoration: none; margin-left: 10px; }
.notice { background: #ffeded; border: 1px solid #ff6b6b; padding: 10px; border-radius: 6px; color: #a00; margin-bottom: 10px; }
.ok { background: #e6f6e6; border: 1px solid #4caf50; padding: 10px; border-radius: 6px; color: #065; margin-bottom: 10px; }
table { border-collapse: collapse; width:100%; margin-top:20px; }
th, td { border:1px solid #ddd; padding:8px; } th { background:#2c3e50; color:#fff; }
tr:nth-child(even) { background:#f9f9f9; } tr:hover { background:#f1f1f1; }
.checkbox { width: 20px; }
</style>
</head>
<body>
<a href="admin.php" class="home-btn">← Home to Dashboard</a>
<div class="form">
    <h2>Setup Irregular Student</h2>

    <?php if ($error): ?>
        <div class="notice"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="ok"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($step == 1): ?>
    <form method="post">
        <input type="hidden" name="step" value="1">

        <label>Student</label>
        <select name="student_id" required>
            <option value="">-- Select Student --</option>
            <?php
            $students = mysqli_query($conn, "SELECT ID, F_name, L_name, Student_Number FROM student_login ORDER BY L_name");
            while ($row = mysqli_fetch_assoc($students)) {
                echo "<option value='{$row['ID']}'>{$row['Student_Number']} - {$row['L_name']}, {$row['F_name']}</option>";
            }
            ?>
        </select>

        <label>Academic Year</label>
        <input type="text" name="academic_year" value="<?php echo htmlspecialchars($current_year); ?>" required>

        <label>Semester</label>
        <select name="semester" required>
            <option value="1" <?php echo ($current_semester == 1) ? 'selected' : ''; ?>>1st Semester</option>
            <option value="2" <?php echo ($current_semester == 2) ? 'selected' : ''; ?>>2nd Semester</option>
        </select>

        <div style="text-align: center; margin-top: 20px;">
            <button type="submit">Next: Select Subjects</button>
            <a href="admin.php">Cancel</a>
        </div>
    </form>
    <?php elseif ($step == 2): ?>
    <p>Setting up subjects for: <strong><?php echo htmlspecialchars($selected_student['F_name'] . ' ' . $selected_student['L_name']); ?></strong> (<?php echo htmlspecialchars($selected_year); ?>, Semester <?php echo $selected_semester; ?>)</p>
    <form method="post">
        <input type="hidden" name="step" value="2">
        <input type="hidden" name="student_id" value="<?php echo $selected_student['ID']; ?>">
        <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($selected_year); ?>">
        <input type="hidden" name="semester" value="<?php echo $selected_semester; ?>">

        <table>
            <tr>
                <th>Select</th>
                <th>Code</th>
                <th>Description</th>
                <th>Units</th>
                <th>Level</th>
                <th>Course</th>
                <th>Semester</th>
            </tr>
            <?php foreach ($subjects as $subj): ?>
            <tr>
                <td><input type="checkbox" name="subjects[]" value="<?php echo $subj['ID']; ?>" class="checkbox" <?php echo (!empty($subj['enrolled_id']) ? 'checked' : ''); ?>></td>
                <td><?php echo htmlspecialchars($subj['subj_code']); ?></td>
                <td><?php echo htmlspecialchars($subj['subj_desc']); ?></td>
                <td><?php echo $subj['unit_count']; ?></td>
                <td><?php echo $subj['level']; ?></td>
                <td><?php echo $subj['subj_sort']; ?></td>
                <td><?php echo $subj['semester'] == 1 ? '1st' : '2nd'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div style="text-align: center; margin-top: 20px;">
            <button type="submit">Enroll in Selected Subjects</button>
            <a href="?">Back</a>
        </div>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
