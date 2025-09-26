<?php
include 'check_access.php';
requireRole('admin');

$conn = mysqli_connect("localhost", "root", "", "escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// ---------------------------
// Current Academic Year & Semester
// ---------------------------
$year_now = date("Y");
$current_year = $year_now . "-" . ($year_now + 1);
$month = date("n");
$current_semester = ($month >= 6 && $month <= 10) ? 1 : 2;

// ---------------------------
// Year levels & courses
// ---------------------------
$year_levels = ["1ST YEAR", "2ND YEAR", "3RD YEAR", "4TH YEAR"];
$course_options = [
    "INFORMATION TECHNOLOGY (BSIT)",
    "COMPUTER SCIENCE (BSCS)",
    "ACCOUNTANCY (BSA)",
    "BSBA - MAJOR IN FM",
    "BSBA - MAJOR IN MM",
    "BSBA - MAJOR IN HRDM",
    "BTVTED - FSM",
    "BTVTED - ELEC",
    "BSOA - OFFICE ADMINISTRATION",
    "SHS - HUMSS",
    "SHS - ABM",
    "SHS - ICT"
];

// ---------------------------
// Helper: Map yr_lvl to standard year level
// ---------------------------
function map_year_level($yr_lvl) {
    $normalized = strtolower(trim($yr_lvl));
    $mapping = [
        "1" => "1ST YEAR",
        "2" => "2ND YEAR",
        "3" => "3RD YEAR",
        "4" => "4TH YEAR",
        "1st year" => "1ST YEAR",
        "2nd year" => "2ND YEAR",
        "3rd year" => "3RD YEAR",
        "4th year" => "4TH YEAR",
        "first year" => "1ST YEAR",
        "second year" => "2ND YEAR",
        "third year" => "3RD YEAR",
        "fourth year" => "4TH YEAR",
        "1st year college" => "1ST YEAR",
        "2nd year college" => "2ND YEAR",
        "3rd year college" => "3RD YEAR",
        "4th year college" => "4TH YEAR",
        // Add more if needed
    ];
    return $mapping[$normalized] ?? "1ST YEAR"; // Default to 1ST YEAR if not found
}

// ---------------------------
// Helper: Generate random password
// ---------------------------
function generatePassword($length = 8) {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
}

$msg = "";

// ---------------------------
// Handle actions
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = intval($_POST['student_id']);
    $action     = $_POST['action'] ?? '';

    $res = mysqli_query($conn, "SELECT * FROM student_login WHERE ID='$student_id' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $student = mysqli_fetch_assoc($res);

        // Always take yr_lvl from student_login if not overridden
        $year_level = $_POST['year_level'] ?? $student['yr_lvl'];
        $course     = $_POST['course'] ?? $student['crs'];

        // Check if already registered for this term
        $check = mysqli_query($conn, "SELECT * FROM student_registrations 
            WHERE student_id='$student_id'
              AND academic_year='$current_year'
              AND semester='$current_semester'
            LIMIT 1
        ");

        if ($action == "enroll") {
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn, "INSERT INTO student_registrations
                    (student_id, academic_year, semester, year_level, course, status, date_registered)
                    VALUES
                    ('$student_id', '$current_year', '$current_semester', '$year_level', '$course', 'ENROLLED', NOW())");

                // Create user account if not exists
                $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE student_id='$student_id' LIMIT 1");
                if (mysqli_num_rows($checkUser) == 0) {
                    $username = strtolower(substr($student['F_name'], 0, 1) . $student['L_name']);
                    $password = generatePassword();
                    mysqli_query($conn, "INSERT INTO users (username,password,role,created_at,student_id)
                        VALUES ('$username','$password','student',NOW(),'$student_id')");
                }

                $msg = "✅ Student enrolled successfully!";
            } else {
                $msg = "⚠ Student is already processed for this term.";
            }
        } elseif ($action == "decline") {
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn, "INSERT INTO student_registrations
                    (student_id, academic_year, semester, year_level, course, status, date_registered)
                    VALUES
                    ('$student_id', '$current_year', '$current_semester', '$year_level', '$course', 'CANCELLED', NOW())");
                $msg = "❌ Student enrollment declined.";
            } else {
                $msg = "⚠ Student already processed this term.";
            }
        } elseif ($action == "update_status" && isset($_POST['new_status'])) {
            $new_status = $_POST['new_status'];
            mysqli_query($conn, "UPDATE student_registrations 
                SET status='$new_status' 
                WHERE student_id='$student_id' 
                  AND academic_year='$current_year' 
                  AND semester='$current_semester'");
            $msg = "🔄 Status updated to $new_status.";
        }
    }
}

// ---------------------------
// Fetch pending students
// ---------------------------
$sql_pending = "
    SELECT * FROM student_login s
    WHERE NOT EXISTS (
        SELECT 1 FROM student_registrations r
        WHERE r.student_id = s.ID
          AND r.academic_year = '$current_year'
          AND r.semester = '$current_semester'
    )
    ORDER BY s.L_name, s.F_name
";
$result_pending = mysqli_query($conn, $sql_pending);

// ---------------------------
// Fetch enrolled students
// ---------------------------
$sql_enrolled = "
    SELECT s.*, r.year_level, r.course, r.status
    FROM student_registrations r
    JOIN student_login s ON s.ID = r.student_id
    WHERE r.academic_year = '$current_year'
      AND r.semester = '$current_semester'
      AND r.status = 'ENROLLED'
    ORDER BY s.L_name, s.F_name
";
$result_enrolled = mysqli_query($conn, $sql_enrolled);

// ---------------------------
// Fetch dropped/cancelled students
// ---------------------------
$sql_dropped = "
    SELECT s.*, r.year_level, r.course, r.status
    FROM student_registrations r
    JOIN student_login s ON s.ID = r.student_id
    WHERE r.academic_year = '$current_year'
      AND r.semester = '$current_semester'
      AND r.status IN ('DROPPED','CANCELLED')
    ORDER BY s.L_name, s.F_name
";
$result_dropped = mysqli_query($conn, $sql_dropped);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Enrollment Panel</title>
<link rel="stylesheet" href="admin.css?v10">
</head>
<body>
<h1>📚 Enrollment Management (AY <?= $current_year ?> - <?= $current_semester==1?"1st Sem":"2nd Sem" ?>)</h1>

<?php if (!empty($msg)) echo "<p><b>$msg</b></p>"; ?>

<!-- PENDING -->
<h2>🟡 Pending Students</h2>
<table border="1" cellpadding="5">
<tr>
    <th>Name</th>
    <th>Course</th>
    <th>Year Level</th>
    <th>Action</th>
</tr>
<?php if(mysqli_num_rows($result_pending) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($result_pending)): ?>
    <tr>
        <form method="POST">
            <td><?= htmlspecialchars($row['L_name'].", ".$row['F_name']." ".$row['M_name']); ?></td>
            <td>
                <input type="hidden" name="student_id" value="<?= $row['ID']; ?>">
                <select name="course" required>
                    <?php foreach($course_options as $c): ?>
                        <option value="<?= $c ?>" <?= $c==$row['crs']?'selected':'' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <?php $mapped_yr_lvl = map_year_level($row['yr_lvl']); ?>
                <select name="year_level" required>
                    <?php foreach($year_levels as $yl): ?>
                        <option value="<?= $yl ?>" <?= $yl==$mapped_yr_lvl?'selected':'' ?>><?= $yl ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <button type="submit" name="action" value="enroll">✅ Enroll</button>
                <button type="submit" name="action" value="decline">❌ Decline</button>
            </td>
        </form>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No pending students</td></tr>
<?php endif; ?>
</table>

<!-- ENROLLED -->
<h2>🟢 Enrolled Students</h2>
<table border="1" cellpadding="5">
<tr>
    <th>Name</th>
    <th>Course</th>
    <th>Year Level</th>
    <th>Status</th>
    <th>Update</th>
</tr>
<?php if(mysqli_num_rows($result_enrolled) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($result_enrolled)): ?>
    <tr>
        <form method="POST">
            <td><?= htmlspecialchars($row['L_name'].", ".$row['F_name']." ".$row['M_name']); ?></td>
            <td><?= htmlspecialchars($row['course']); ?></td>
            <td><?= htmlspecialchars($row['year_level']); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td>
                <input type="hidden" name="student_id" value="<?= $row['ID']; ?>">
                <select name="new_status">
                    <option value="ENROLLED" selected>ENROLLED</option>
                    <option value="DROPPED">DROPPED</option>
                    <option value="CANCELLED">CANCELLED</option>
                </select>
                <button type="submit" name="action" value="update_status">Update</button>
            </td>
        </form>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5">No enrolled students</td></tr>
<?php endif; ?>
</table>

<!-- DROPPED/CANCELLED -->
<h2>🔴 Dropped / Cancelled Students</h2>
<table border="1" cellpadding="5">
<tr>    
    <th>Name</th>
    <th>Course</th>
    <th>Year Level</th>
    <th>Status</th>
    <th>Update</th>
</tr>   
<?php if(mysqli_num_rows($result_dropped) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($result_dropped)): ?>
    <tr>
        <form method="POST">
            <td><?= htmlspecialchars($row['L_name'].", ".$row['F_name']." ".$row['M_name']); ?></td>
            <td><?= htmlspecialchars($row['course']); ?></td>
            <td><?= htmlspecialchars($row['year_level']); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td>
                <input type="hidden" name="student_id" value="<?= $row['ID']; ?>">
                <select name="new_status">
                    <option value="ENROLLED">ENROLLED</option>
                    <option value="DROPPED" <?= $row['status']=="DROPPED"?"selected":"" ?>>DROPPED</option>
                    <option value="CANCELLED" <?= $row['status']=="CANCELLED"?"selected":"" ?>>CANCELLED</option>
                </select>
                <button type="submit" name="action" value="update_status">Update</button>
            </td>
        </form>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5">No dropped/cancelled students</td></tr>
<?php endif; ?>
</table>

</body>
</html>
