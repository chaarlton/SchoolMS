<?php
include 'check_access.php';
requireRole('student');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$conn = mysqli_connect("sql112.infinityfree.com", "if0_40025418", "milkosry4", "if0_40025418_escr_dbase");
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

// Get registration date from student_login
$sql_reg_date = "SELECT Date FROM student_login WHERE ID = '$student_id' LIMIT 1";
$result_reg_date = mysqli_query($conn, $sql_reg_date);
$reg_date = mysqli_fetch_assoc($result_reg_date)['Date'] ?? '';

// Calculate age from birthday
$age = 'N/A';
if (!empty($student['bday'])) {
    $birthDate = new DateTime($student['bday']);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
}

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
$is_enrolled = mysqli_num_rows($enrolled_check) > 0;

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

if (!$is_enrolled) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Access Denied</title>
</head>
<body>
<h1>⚠ You are not officially enrolled for Academic Year <?php echo $filter_acadyear; ?>, Semester <?php echo $filter_semester; ?>.</h1>
<p>You will be redirected to the login page in 10 seconds.</p>
<script>
setTimeout(function() {
    window.location.href = 'logout.php';
}, 10000);
</script>
</body>
</html>
<?php
    exit();
}

// Handle POST requests
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $f_name = mysqli_real_escape_string($conn, $_POST['f_name']);
        $m_name = mysqli_real_escape_string($conn, $_POST['m_name']);
        $l_name = mysqli_real_escape_string($conn, $_POST['l_name']);
        $crs = mysqli_real_escape_string($conn, $_POST['crs']);
        $yr_lvl = mysqli_real_escape_string($conn, $_POST['yr_lvl']);

        $update_sql = "UPDATE student_login SET F_name='$f_name', M_name='$m_name', L_name='$l_name', crs='$crs', yr_lvl='$yr_lvl' WHERE ID='$student_id'";
        if (mysqli_query($conn, $update_sql)) {
            $message = 'Profile updated successfully!';
            $message_type = 'success';
            // Refresh student data
            $result_student = mysqli_query($conn, $sql_student);
            $student = mysqli_fetch_assoc($result_student);
        } else {
            $message = 'Error updating profile: ' . mysqli_error($conn);
            $message_type = 'error';
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Get current password from users table
        $user_sql = "SELECT password FROM users WHERE student_id='$student_id' LIMIT 1";
        $user_result = mysqli_query($conn, $user_sql);
        if ($user_result && mysqli_num_rows($user_result) == 1) {
            $user_row = mysqli_fetch_assoc($user_result);
            $stored_password = $user_row['password'];

            if ($current_password == $stored_password) {
                if ($new_password == $confirm_password) {
                    $update_pass_sql = "UPDATE users SET password='$new_password' WHERE student_id='$student_id'";
                    if (mysqli_query($conn, $update_pass_sql)) {
                        $message = 'Password changed successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error changing password: ' . mysqli_error($conn);
                        $message_type = 'error';
                    }
                } else {
                    $message = 'New passwords do not match!';
                    $message_type = 'error';
                }
            } else {
                $message = 'Current password is incorrect!';
                $message_type = 'error';
            }
        } else {
            $message = 'User not found!';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Portal | ESCR</title>
<link rel="stylesheet" href="student.css?v13">
<link rel="shortcut icon" href="Picture3.png" type="image/x-icon">
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
                <a href="logout.php"><button class="lgout" >LOGOUT</button></a>
            </div>

            <div class="right">
                <div class="menu">
                    <button class="btn1" onclick="showSubjects()">SUBJECTS</button>
                    <button class="btn1" onclick="showDetails()">DETAILS</button>
                    <button class="btn1">FILES</button>
                </div>
                <div class="Details">
                    <h2>My Details</h2>
                    <?php if (!empty($message)): ?>
                        <div class="message <?= $message_type; ?>">
                            <?= htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <div id="view-mode">
                        <div class="details-grid">
                            <div class="detail-item"><strong>ID:</strong> <?= htmlspecialchars($student['ID']); ?></div>
                            <div class="detail-item"><strong>Last Name:</strong> <?= htmlspecialchars($student['L_name']); ?></div>
                            <div class="detail-item"><strong>First Name:</strong> <?= htmlspecialchars($student['F_name']); ?></div>
                            <div class="detail-item"><strong>Middle Name:</strong> <?= htmlspecialchars($student['M_name']); ?></div>
                            <div class="detail-item"><strong>Age:</strong> <?= htmlspecialchars($age); ?></div>
                            <div class="detail-item"><strong>Birthday:</strong> <?= htmlspecialchars($student['bday'] ?? 'N/A'); ?></div>
                            <div class="detail-item"><strong>Place of Birth:</strong> <?= htmlspecialchars($student['pbirth'] ?? 'N/A'); ?></div>
                            <div class="detail-item"><strong>Gender:</strong> <?= htmlspecialchars($student['gender'] ?? 'N/A'); ?></div>
                            <div class="detail-item"><strong>Marital Status:</strong> <?= htmlspecialchars($student['marital'] ?? 'N/A'); ?></div>
                            <div class="detail-item"><strong>Year Level:</strong> <?= htmlspecialchars($student['yr_lvl']); ?></div>
                            <div class="detail-item"><strong>Course:</strong> <?= htmlspecialchars($student['crs']); ?></div>
                            <div class="detail-item"><strong>Street:</strong> <?= htmlspecialchars($student['street'] ?? 'N/A'); ?></div>
                            <div class="detail-item"><strong>Barangay:</strong> <?= htmlspecialchars($student['brgy'] ?? 'N/A'); ?></div>
                            <div class="detail-item"><strong>City/Municipality:</strong> <?= htmlspecialchars($student['city_mun'] ?? 'N/A'); ?></div>
                            <div class="detail-item"><strong>Registration Date:</strong> <?= htmlspecialchars($reg_date); ?></div>
                            <div class="detail-item"><strong>Student Number:</strong> <?= htmlspecialchars($student['Student_Number']); ?></div>
                            <div class="detail-item"><strong>Status:</strong> <?= htmlspecialchars($student['status'] ?? 'N/A'); ?></div>
                        </div>
                        <button class="edit-btn" onclick="toggleEdit()">Edit Details</button>
                    </div>

                    <div id="edit-mode" style="display: none;">
                        <form method="POST" class="update-form">
                            <h3>Update Profile</h3>
                            <label for="f_name">First Name:</label>
                            <input type="text" id="f_name" name="f_name" value="<?= htmlspecialchars($student['F_name']); ?>" required>

                            <label for="m_name">Middle Name:</label>
                            <input type="text" id="m_name" name="m_name" value="<?= htmlspecialchars($student['M_name']); ?>" required>

                            <label for="l_name">Last Name:</label>
                            <input type="text" id="l_name" name="l_name" value="<?= htmlspecialchars($student['L_name']); ?>" required>

                            <label for="crs">Course:</label>
                            <select id="crs" name="crs" required>
                                <option value="INFORMATION TECHNOLOGY (BSIT)" <?= $student['crs'] == 'INFORMATION TECHNOLOGY (BSIT)' ? 'selected' : ''; ?>>INFORMATION TECHNOLOGY (BSIT)</option>
                                <option value="COMPUTER SCIENCE (BSCS)" <?= $student['crs'] == 'COMPUTER SCIENCE (BSCS)' ? 'selected' : ''; ?>>COMPUTER SCIENCE (BSCS)</option>
                                <option value="ACCOUNTANCY (BSA)" <?= $student['crs'] == 'ACCOUNTANCY (BSA)' ? 'selected' : ''; ?>>ACCOUNTANCY (BSA)</option>
                                <option value="BUSINESS ADMINISTRATION (BSBA)" <?= $student['crs'] == 'BUSINESS ADMINISTRATION (BSBA)' ? 'selected' : ''; ?>>BUSINESS ADMINISTRATION (BSBA)</option>
                            </select>

                            <label for="yr_lvl">Year Level:</label>
                            <select id="yr_lvl" name="yr_lvl" required>
                                <option value="1ST YEAR COLLEGE" <?= $student['yr_lvl'] == '1ST YEAR COLLEGE' ? 'selected' : ''; ?>>1ST YEAR COLLEGE</option>
                                <option value="2ND YEAR COLLEGE" <?= $student['yr_lvl'] == '2ND YEAR COLLEGE' ? 'selected' : ''; ?>>2ND YEAR COLLEGE</option>
                                <option value="3RD YEAR COLLEGE" <?= $student['yr_lvl'] == '3RD YEAR COLLEGE' ? 'selected' : ''; ?>>3RD YEAR COLLEGE</option>
                                <option value="4TH YEAR COLLEGE" <?= $student['yr_lvl'] == '4TH YEAR COLLEGE' ? 'selected' : ''; ?>>4TH YEAR COLLEGE</option>
                            </select>

                            <button type="submit" name="update_profile">Update Profile</button>
                            <button type="button" onclick="toggleEdit()">Cancel</button>
                        </form>

                        <form method="POST" class="update-form">
                            <h3>Change Password</h3>
                            <label for="current_password">Current Password:</label>
                            <input type="password" id="current_password" name="current_password" required>

                            <label for="new_password">New Password:</label>
                            <input type="password" id="new_password" name="new_password" required>

                            <label for="confirm_password">Confirm New Password:</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>

                            <button type="submit" name="change_password">Change Password</button>
                        </form>
                    </div>
                </div>
                <div class="Subjects">
                    <h2>My Subjects</h2>

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
                        <?php
                        $total_weighted = 0;
                        $total_units = 0;
                        if(mysqli_num_rows($result_subjects) > 0):
                            mysqli_data_seek($result_subjects, 0); // Reset pointer
                            while($row = mysqli_fetch_assoc($result_subjects)):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['subj_code']); ?></td>
                                <td><?= htmlspecialchars($row['subj_desc']); ?></td>
                                <td><?= $row['unit_count']; ?></td>
                                <td><?= $row['grade'] ?? 'N/A'; ?></td>
                            </tr>
                        <?php
                                if (!empty($row['grade']) && is_numeric($row['grade'])) {
                                    $total_weighted += $row['grade'] * $row['unit_count'];
                                    $total_units += $row['unit_count'];
                                }
                            endwhile;
                        else:
                        ?>
                            <tr><td colspan="4">No subjects found for this selection.</td></tr>
                        <?php endif; ?>
                    </table>
                    <?php if ($total_units > 0): ?>
                        <div class="gwa">
                            <strong>General Weighted Average (GWA):</strong> <?= number_format($total_weighted / $total_units, 2); ?>
                        </div>
                    <?php else: ?>
                        <div class="gwa">
                            <strong>General Weighted Average (GWA):</strong> N/A
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function showSubjects() {
    document.querySelector('.Subjects').style.display = 'flex';
    document.querySelector('.Details').style.display = 'none';
}

function showDetails() {
    document.querySelector('.Subjects').style.display = 'none';
    document.querySelector('.Details').style.display = 'flex';
}

function toggleEdit() {
    const viewMode = document.getElementById('view-mode');
    const editMode = document.getElementById('edit-mode');
    if (viewMode.style.display === 'none') {
        viewMode.style.display = 'block';
        editMode.style.display = 'none';
    } else {
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
    }
}

// Initially show Subjects
showSubjects();
</script>
</body>
</html>
