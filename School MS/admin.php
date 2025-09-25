<?php
include 'check_access.php';
requireRole('admin');

// Prevent browser cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$conn = mysqli_connect("localhost","root","","escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// --------------------
// Helpers
// --------------------
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
    return isset($map[$value]) ? $map[$value] : "1ST YEAR";
}

function generateRandomPassword($length = 8) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

// --------------------
// Current term
// --------------------
$year_now = date("Y");
$current_year = $year_now . "-" . ($year_now + 1);
$month = date("n");
$current_semester = ($month >= 6 && $month <= 10) ? 1 : 2;


$res_registered = mysqli_query($conn, "SELECT COUNT(*) AS total_registered FROM student_login");
$row_registered = mysqli_fetch_assoc($res_registered);
$total_registered = $row_registered['total_registered'];

$res_enrolled = mysqli_query($conn, "
SELECT COUNT(*) AS total_enrolled FROM student_registrations
WHERE academic_year = '$current_year' AND semester='$current_semester' AND
status = 'ENROLLED'");
$row_enrolled = mysqli_fetch_assoc($res_enrolled);
$total_enrolled = $row_enrolled['total_enrolled'];

function getCountByCourse($conn, $course){
  $course = mysqli_real_escape_string($conn, $course);
  $sql = "SELECT COUNT(*) AS total FROM student_registrations WHERE course='$course'";
  $res = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($res);
  return $row['total'] ?? 0;
}
$bsit_students   = getCountByCourse($conn, "INFORMATION TECHNOLOGY (BSIT)");
$bscs_students   = getCountByCourse($conn, "COMPUTER SCIENCE (BSCS)");
$bsba_fm         = getCountByCourse($conn, "BSBA - MAJOR IN FM");
$bsba_mm         = getCountByCourse($conn, "BSBA - MAJOR IN MM");
$bsba_hrdm       = getCountByCourse($conn, "BSBA - MAJOR IN HRDM");
$btvted_fsm      = getCountByCourse($conn, "BTVTED - FSM");
$btvted_elec     = getCountByCourse($conn, "BTVTED - ELEC");
$bsais_students   = getCountByCourse($conn, "BSAIS - ACCOUNTING INFORMATION SYSTEM");
$bsoa_students   = getCountByCourse($conn, "BSOA - OFFICE ADMINISTRATION");
$shs_humss       = getCountByCourse($conn, "SHS - HUMSS");
$shs_abm         = getCountByCourse($conn, "SHS - ABM");
$shs_ict         = getCountByCourse($conn, "SHS - ICT");
$bsba_total = $bsba_fm + $bsba_hrdm + $bsba_mm;
$btvted_students = $btvted_elec + $btvted_fsm;

function getSHSCount($conn, $strand, $grade){
    $strand = mysqli_real_escape_string($conn, $strand);
    $grade  = mysqli_real_escape_string($conn, $grade);
    $sql = "SELECT COUNT(*) AS total 
            FROM student_login 
            WHERE crs='$strand' AND yr_lvl='$grade'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['total'] ?? 0;
}
$shs_humss_g11 = getSHSCount($conn, "SHS - HUMSS", "GRADE 11");
$shs_humss_g12 = getSHSCount($conn, "SHS - HUMSS", "GRADE 12");

$shs_abm_g11   = getSHSCount($conn, "SHS - ABM", "GRADE 11");
$shs_abm_g12   = getSHSCount($conn, "SHS - ABM", "GRADE 12");

$shs_ict_g11   = getSHSCount($conn, "SHS - ICT", "GRADE 11");
$shs_ict_g12   = getSHSCount($conn, "SHS - ICT", "GRADE 12");

// Totals
$shs_grade11_total = $shs_humss_g11 + $shs_abm_g11 + $shs_ict_g11;
$shs_grade12_total = $shs_humss_g12 + $shs_abm_g12 + $shs_ict_g12;

//Mode of Learning
function getModeOfLearning($conn, $mol){
  $mol = mysqli_real_escape_string($conn, $mol);
  $sql = "SELECT COUNT(*) AS total FROM student_registrations WHERE mol = '$mol'";
  $res = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($res);
  return $row['total'] ?? 0;
}
$f2f = getModeOfLearning($conn, "F2F");
$OC = getModeOfLearning($conn, "ONLINE");
// --------------------
// Handle approval
// --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $student_id = intval($_POST['approve_id']);
    
    $res = mysqli_query($conn, "SELECT * FROM student_login WHERE ID='$student_id' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $student = mysqli_fetch_assoc($res);

        // Normalize year level
        $yr_lvl = normalizeYearLevel($student['yr_lvl']);
        $crs    = mysqli_real_escape_string($conn, $student['crs']);

        // Insert into student_registrations if not already enrolled
        $checkReg = mysqli_query($conn, "SELECT * FROM student_registrations 
                        WHERE student_id='$student_id' 
                          AND academic_year='$current_year' 
                          AND semester='$current_semester' LIMIT 1");

        if (mysqli_num_rows($checkReg) == 0) {
            $insertReg = "INSERT INTO student_registrations
                (student_id, academic_year, semester, year_level, status, date_registered, course)
                VALUES
                ('$student_id', '$current_year', '$current_semester', '$yr_lvl', 'ENROLLED', NOW(), '$crs')";
            
            if (!mysqli_query($conn, $insertReg)) {
                die("Error inserting into student_registrations: " . mysqli_error($conn));
            }
        }

        // Create user account if not exists
        $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE student_id='$student_id' LIMIT 1");
        if (mysqli_num_rows($checkUser) == 0) {
            $username = strtolower(substr($student['F_name'],0,1) . $student['L_name']);
            $password = generateRandomPassword(); // plain password

            $insertUser = "INSERT INTO users (username,password,role,created_at,student_id) 
                           VALUES ('$username','$password','student',NOW(),'$student_id')";
            
            if (!mysqli_query($conn, $insertUser)) {
                die(" Error inserting into users: " . mysqli_error($conn));
            }

            $account_msg = "User account created → Username: $username, Password: $password";
        } else {
            $account_msg = "Account already exists for this student.";
        }

        $msg = " Student approved & enrolled. $account_msg";
    } else {
        $msg = " Student not found.";
    }
}

// --------------------
// Filters
// --------------------
$search = $_GET['search'] ?? '';
$date   = $_GET['date'] ?? '';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ESCR | ADMIN PANEL</title>
  <link rel="stylesheet" href="admin.css?v3">
  <link rel="shortcut icon" href="Picture3.png" type="image/x-icon">
  <style>
      table { width: 100%; border-collapse: collapse; margin-top: 15px; }
      table, th, td { border: 1px solid #ddd; }
      th { background: #2c3e50; color: #fff; padding: 10px; text-align: left; }
      td { padding: 8px; font-size: 14px; }
      tr:nth-child(even) { background: #f9f9f9; }
      tr:hover { background: #f1f1f1; }
      .filter-form { margin: 15px 0; display: flex; gap: 10px; align-items: center; }
      .filter-form input, .filter-form button { padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; }
      .filter-form button { background: #2c3e50; color: #fff; cursor: pointer; }
      .filter-form button:hover { background: #1a252f; }
      .actionbtns a, .actionbtns button { margin-right: 5px; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
      .edit-btn { background:#3498db; color:#fff; }
      .grades-btn { background:#27ae60; color:#fff; }
      .approve-btn { background:#f39c12; color:#fff; border:none; cursor:pointer; }
  </style>
</head>
<body>
  <div class="main">
    <div class="container">
      <div class="left">
        <h3 align="center">ADMIN PANEL</h3>
        <div class="panel">
          <nav>
            <ul>
              <li>DASHBOARD</li>
              <li>STUDENT MONITORING</li>
              <li>ADMIN CENTER</li>
              <li>TICKET CENTER</li>
              <li>IT REQUEST</li>
            </ul>
          </nav>
        </div>
      </div>
      <div class="right">
        <div class="contents">

 <div class="dash">
  <div class="d-box1">
      <h1><?php echo $total_enrolled; ?></h1>
      <h3>ENROLLED STUDENTS</h3>
      <div class="count">
      <div class="cnt">
      <h1><?php echo $f2f; ?></h1>
      <h3>FACE TO FACE</h3>
</div>
      <div class="cnt">
      <h1><?php echo $OC; ?></h1>
      <h3>ONLINE CLASS</h3>
</div>
</div>
  </div>
  <div class="d-box1">
      <h1><?php echo $total_registered; ?></h1>
      <h3>REGISTERED STUDENTS</h3>
      <h5>A.Y <?php echo $current_year; ?></h5>
  </div>

  <div class="d-box">
      <h1><?php echo $bsit_students; ?></h1>
      <h3>IT STUDENTS</h3>
      <h5>A.Y <?php echo $current_year; ?></h5>
  </div>
  <div class="d-box">
      <h1><?php echo $bsba_total; ?></h1>
      <h3>BSBA (ALL MAJOR) STUDENTS</h3>
      <h5>A.Y <?php echo $current_year; ?></h5>
  </div>
  <div class="d-box">
      <h1><?php echo $bsais_students; ?></h1>
      <h3>BSAIS STUDENTS</h3>
      <h5>A.Y <?php echo $current_year; ?></h5>
  </div>
  <div class="d-box">
      <h1><?php echo $btvted_students; ?></h1>
      <h3>BTVTED (ALL MAJOR) STUDENTS</h3>
      <h5>A.Y <?php echo $current_year; ?></h5>
  </div>
  <div class="d-box">
      <h1><?php echo $shs_grade11_total; ?></h1>
      <h3>GRADE 11 (ALL STRANDS)</h3>
      <h5>A.Y <?php echo $current_year; ?></h5>
  </div>
  <div class="d-box">
      <h1><?php echo $shs_grade12_total; ?></h1>
      <h3>GRADE 12 (ALL STRANDS)</h3>
      <h5>A.Y <?php echo $current_year; ?></h5>
  </div>
</div>

          <div class="students">
            <h2> Student Information</h2>

            <?php if (!empty($msg)) echo "<p><b>$msg</b></p>"; ?>

            <!-- Filter Form -->
            <form method="GET" class="filter-form">
              <input type="text" name="search" placeholder="Search by name or student number" value="<?php echo htmlspecialchars($search); ?>">
              <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
              <button type="submit">Filter</button>
              <a href="admin.php" style="margin-left:10px; text-decoration:none; color:#3498db;">Reset</a>
            </form>

            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Year Level</th>
                        <th>Course</th>
                        <th>Student Number</th>
                        <th>Status</th> <!-- Regular/Irregular -->
                        <th>Enrollment Status</th> <!-- Enrolled/Not Enrolled -->
                        <th>Actions</th>
                      </tr>";
                while($row = mysqli_fetch_assoc($result)) {
                    $fullname = $row['L_name'].", ".$row['F_name']." ".$row['M_name'];

                    // Force Regular Student for 1st–4th year
                    $status = in_array(normalizeYearLevel($row['yr_lvl']), ["1ST YEAR","2ND YEAR","3RD YEAR","4TH YEAR"]) 
                              ? "Regular Student" 
                              : $row['status'];

                    echo "<tr>
                            <td>{$row['ID']}</td>
                            <td>{$fullname}</td>
                            <td>{$row['yr_lvl']}</td>
                            <td>{$row['crs']}</td>
                            <td>{$row['Student_Number']}</td>
                            <td>{$status}</td>
                            <td>".($row['enrolled_status'] ? "Enrolled" : " Not Enrolled")."</td>
                            <td class='actionbtns'>
                              <a class='edit-btn' href='edit_student.php?id={$row['ID']}'>Edit</a>
                              <a class='grades-btn' href='admin_student_grades.php?id={$row['ID']}'>Manage Grades</a>
                              <form method='POST' style='display:inline'>
                                <input type='hidden' name='approve_id' value='{$row['ID']}'>
                                <button type='submit' class='approve-btn'>Approve</button>
                              </form>
                            </td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No students found.</p>";
            }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
