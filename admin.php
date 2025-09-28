<?php
include 'check_access.php';
requireRole('admin');
include 'course_mapping.php';

// Prevent browser cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// --------------------
// DB Connection
// --------------------
$conn = mysqli_connect("sql112.infinityfree.com", "if0_40025418", "milkosry4", "if0_40025418_escr_dbase");
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
    return $map[$value] ?? "1ST YEAR";
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

// --------------------
// Dashboard counts
// --------------------
$res_registered = mysqli_query($conn, "
    SELECT COUNT(*) AS total_registered 
    FROM student_login
");
$row_registered = mysqli_fetch_assoc($res_registered);
$total_registered = $row_registered['total_registered'];


$res_enrolled = mysqli_query($conn, "
SELECT COUNT(*) AS total_enrolled 
FROM student_registrations
WHERE academic_year = '$current_year' 
  AND semester='$current_semester' 
  AND status = 'ENROLLED'");
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
$it_cs_students  = $bsit_students + $bscs_students;
$bsba_fm         = getCountByCourse($conn, "BSBA - MAJOR IN FM");
$bsba_mm         = getCountByCourse($conn, "BSBA - MAJOR IN MM");
$bsba_hrdm       = getCountByCourse($conn, "BSBA - MAJOR IN HRDM");
$btvted_fsm      = getCountByCourse($conn, "BTVTED - FSM");
$btvted_elec     = getCountByCourse($conn, "BTVTED - ELEC");
$bsais_students  = getCountByCourse($conn, "BSAIS - ACCOUNTING INFORMATION SYSTEM");
$bsoa_students   = getCountByCourse($conn, "BSOA - OFFICE ADMINISTRATION");
$shs_humss       = getCountByCourse($conn, "SHS - HUMSS");
$shs_abm         = getCountByCourse($conn, "SHS - ABM");
$shs_ict         = getCountByCourse($conn, "SHS - ICT");
$bsba_total      = $bsba_fm + $bsba_hrdm + $bsba_mm;
$btvted_students = $btvted_elec + $btvted_fsm;

// SHS breakdown
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
$OC  = getModeOfLearning($conn, "ONLINE");

// --------------------
// Handle approval (POST → Redirect → Get)
// --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $student_id = intval($_POST['approve_id']);
    $msg = "";

    $res = mysqli_query($conn, "SELECT * FROM student_login WHERE ID='$student_id' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $student = mysqli_fetch_assoc($res);

        $yr_lvl = normalizeYearLevel($student['yr_lvl']);
        $crs    = mysqli_real_escape_string($conn, $student['crs']);
        $mol    = $student['mol'];

        // Insert into student_registrations if not already enrolled
        $checkReg = mysqli_query($conn, "SELECT * FROM student_registrations
                        WHERE student_id='$student_id'
                          AND academic_year='$current_year'
                          AND semester='$current_semester' LIMIT 1");

        if (mysqli_num_rows($checkReg) == 0) {
            mysqli_query($conn, "INSERT INTO student_registrations
                (student_id, academic_year, semester, year_level, course, mol, status, date_registered)
                VALUES
                ('$student_id', '$current_year', '$current_semester', '$yr_lvl', '$crs', '$mol', 'ENROLLED', NOW())");
        }

        // Create user account if not exists
        $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE student_id='$student_id' LIMIT 1");
        if (mysqli_num_rows($checkUser) == 0) {
            $username = strtolower(substr($student['F_name'],0,1) . $student['L_name']);
            $password = generateRandomPassword();

            mysqli_query($conn, "INSERT INTO users (username,password,role,created_at,student_id)
                           VALUES ('$username','$password','student',NOW(),'$student_id')");

            $msg = "Student approved & enrolled. Account created → Username: $username, Password: $password";
        } else {
            $msg = "Student approved & enrolled. Account already exists.";
        }

        // Generate student number for 1st year students in 1st semester
        if ($yr_lvl == '1ST YEAR' && $current_semester == 1) {
            $year_part = substr($current_year, 2, 2); // e.g. '25' for '2025-2026'
            $course_abbr = $courseMap[$crs] ?? 'UNK';

            // Find existing student numbers for this year and course
            $pattern = 'A' . $year_part . '-' . $course_abbr . '-%';
            $existing_query = mysqli_query($conn, "SELECT student_number FROM student_login WHERE student_number LIKE '$pattern' AND student_number IS NOT NULL");
            $used_numbers = [];
            while ($row = mysqli_fetch_assoc($existing_query)) {
                $num_str = substr($row['student_number'], -4);
                $used_numbers[] = intval($num_str);
            }
            sort($used_numbers);

            // Find the smallest available number
            $number = 1;
            foreach ($used_numbers as $used) {
                if ($used == $number) {
                    $number++;
                } else {
                    break;
                }
            }

            $student_number = 'A' . $year_part . '-' . $course_abbr . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            mysqli_query($conn, "UPDATE student_login SET student_number = '$student_number' WHERE ID = '$student_id'");

            $msg .= " Student number assigned: $student_number";
        }
    } else {
        $msg = "Student not found.";
    }

    // Check if AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['msg' => $msg]);
        exit;
    }

    // 🔑 Redirect to clear POST (avoids resubmission)
    header("Location: admin.php?view=monitor&msg=" . urlencode($msg));
    exit;
}

// --------------------
// Handle change status (unenroll)
// --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status_id'])) {
    $student_id = intval($_POST['change_status_id']);
    $msg = "";

    // Remove student number
    mysqli_query($conn, "UPDATE student_login SET student_number = NULL WHERE ID = '$student_id'");

    // Delete registration record
    mysqli_query($conn, "DELETE FROM student_registrations WHERE student_id='$student_id' AND academic_year='$current_year' AND semester='$current_semester'");

    // Delete user account
    mysqli_query($conn, "DELETE FROM users WHERE student_id='$student_id'");

    $msg = "Student unenrolled and account removed.";

    // Check if AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['msg' => $msg]);
        exit;
    }

    // Redirect
    header("Location: admin.php?view=monitor&msg=" . urlencode($msg));
    exit;
}

// --------------------
// Filters + View state
// --------------------
$search = $_GET['search'] ?? '';
$date   = $_GET['date'] ?? '';
$view   = $_GET['view'] ?? 'dash';
$msg    = $_GET['msg'] ?? '';

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

// Student data loaded via AJAX
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ESCR | ADMIN PANEL</title>
  <link rel="stylesheet" href="admin.css?v8">
  <link rel="shortcut icon" href="Picture3.png" type="image/x-icon">
</head>

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
      #moni { display: none; } /* Hide monitoring by default */
  </style>
<body>
  <div class="main">
    <div class="container">
      <div class="left">
        <h3 align="center">ADMIN PANEL</h3>
        <div class="panel">
          <nav>
            <ul>
              <li onclick="showBox('dash')">DASHBOARD</li>
              <li onclick="showBox('monitor')">STUDENT MONITORING</li>
              <li onclick="showBox('admin_center')">ADMIN CENTER</li>
              <li>TICKET CENTER</li>
              <li>IT REQUEST</li>
              <a href="logout.php"><li style=" position:relative;">LOGOUT</li></a>
            </ul>
          </nav>
        </div>
      </div>
      <div class="right">
        <div class="contents">

 <!-- Dashboard -->
 <div class="dash" id="DashB">
<div class="d-box1">
    <h1 id="enrolledCount"><?php echo $total_enrolled; ?></h1>
    <h3>ENROLLED STUDENTS</h3>
<div class="count">
  <div class="cnt">
    <h1 id="f2fCount"><?php echo $f2f; ?></h1>
    <h3>FACE TO FACE</h3>
  </div>
  <div class="cnt">
    <h1 id="onlineCount"><?php echo $OC; ?></h1>
    <h3>ONLINE CLASS</h3>
  </div>
</div>
</div>

<div class="d-box1">
    <h1 id="registeredCount"><?php echo $total_registered; ?></h1>
    <h3>REGISTERED STUDENTS</h3>
    <h5>A.Y <?php echo $current_year; ?></h5>
</div>


  <div class="d-box"><h1 id="itCsCount"><?php echo $it_cs_students; ?></h1><h3>IT STUDENTS</h3><h5>A.Y <?php echo $current_year; ?></h5></div>
  <div class="d-box"><h1 id="bsbaCount"><?php echo $bsba_total; ?></h1><h3>BSBA (ALL MAJOR) STUDENTS</h3><h5>A.Y <?php echo $current_year; ?></h5></div>
  <div class="d-box"><h1 id="bsaisCount"><?php echo $bsais_students; ?></h1><h3>BSAIS STUDENTS</h3><h5>A.Y <?php echo $current_year; ?></h5></div>
  <div class="d-box"><h1 id="btvtedCount"><?php echo $btvted_students; ?></h1><h3>BTVTED (ALL MAJOR) STUDENTS</h3><h5>A.Y <?php echo $current_year; ?></h5></div>
  <div class="d-box"><h1 id="grade11Count"><?php echo $shs_grade11_total; ?></h1><h3>GRADE 11 (ALL STRANDS)</h3><h5>A.Y <?php echo $current_year; ?></h5></div>
  <div class="d-box"><h1 id="grade12Count"><?php echo $shs_grade12_total; ?></h1><h3>GRADE 12 (ALL STRANDS)</h3><h5>A.Y <?php echo $current_year; ?></h5></div>
<div class="Charts">
  <canvas id="combinedChart"></canvas>
</div>
</div>

 <!-- Student Monitoring -->
 <div class="students" id="moni" style="display:none;">
    <h2> Student Information</h2>
<?php if (!empty($_GET['msg'])): ?>
  <div id="alertBox" 
       style="padding:10px; background:#dff0d8; color:#3c763d; border:1px solid #3c763d; margin-bottom:10px; border-radius:5px; transition: opacity 1s ease;">
    <b><?php echo htmlspecialchars($_GET['msg']); ?></b>
  </div>
  <script>
    if (window.history.replaceState) {
      window.history.replaceState(null, null, "admin.php");
    }
    setTimeout(function() {
      var box = document.getElementById("alertBox");
      if (box) {
        box.style.opacity = "0";
        setTimeout(function() { box.style.display = "none"; }, 1000);
      }
    }, 10000);
  </script>
<?php endif; ?>

    <!-- Filter Form -->
    <form id="filterForm" class="filter-form">
      <input type="hidden" name="view" value="monitor">
      <input type="text" id="search" name="search" placeholder="Search by name or student number" value="<?php echo htmlspecialchars($search); ?>">
      <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
      <button type="submit">Filter</button>
      <a href="admin.php?view=monitor" style="margin-left:10px; text-decoration:none; color:#3498db;">Reset</a>
    </form>

    <div id="studentTableContainer">
      <table id="studentTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Year Level</th>
            <th>Course</th>
            <th>Student Number</th>
            <th>Status</th>
            <th>Enrollment Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Rows will be populated via AJAX -->
        </tbody>
      </table>
    </div>
 </div>

 <!-- Admin Center -->
 <div class="admin_center" id="admin_center" style="display:none;">
    <h2>Admin Center</h2>
    <div class="admin-sub">
        <h3>Student Approvals</h3>
        <p>Manage student enrollment approvals.</p>
        <a href="admin_student_approvals.php"><button>Go to Student Approvals</button></a>
    </div>
    <div class="admin-sub">
        <h3>Student Accounts</h3>
        <p>Manage student user accounts.</p>
        <a href="admin_accounts.php"><button>Go to Student Accounts</button></a>
    </div>
    <div class="admin-sub">
        <h3>SUBJECT MANAGEMENT</h3>
        <p>Manage the subjects for the current curriculum</p>
        <a href="manage_subjects.php"><button>Go to Subjects Management</button></a>
    </div>
    <div class="admin-sub">
        <h3>Reports</h3>
        <p>Generate and view system reports.</p>
        <a href="reports.php"><button>Go to Reports</button></a>
    </div>
 </div>

        </div>
      </div>
    </div>
  </div>

<script>
function updateDashboard() {
  fetch("dashboard_chart_data.php")
    .then(res => res.json())
    .then(data => {
      if (data.registered !== undefined) {
        document.getElementById("registeredCount").innerText = data.registered;
      }
      if (data.enrolled !== undefined) {
        document.getElementById("enrolledCount").innerText = data.enrolled;
      }
      if (data.f2f !== undefined) {
        document.getElementById("f2fCount").innerText = data.f2f;
      }
      if (data.online !== undefined) {
        document.getElementById("onlineCount").innerText = data.online;
      }
      if (data.it_cs !== undefined) {
        document.getElementById("itCsCount").innerText = data.it_cs;
      }
      if (data.bsba !== undefined) {
        document.getElementById("bsbaCount").innerText = data.bsba;
      }
      if (data.bsais !== undefined) {
        document.getElementById("bsaisCount").innerText = data.bsais;
      }
      if (data.btvted !== undefined) {
        document.getElementById("btvtedCount").innerText = data.btvted;
      }
      if (data.shs_g11 !== undefined) {
        document.getElementById("grade11Count").innerText = data.shs_g11;
      }
      if (data.shs_g12 !== undefined) {
        document.getElementById("grade12Count").innerText = data.shs_g12;
      }
    })
    .catch(err => console.error("Error fetching counts:", err));
}

// Update every 5 seconds
setInterval(updateDashboard, 5000);

// Run once on load
updateDashboard();
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function loadCharts() {
  fetch("dashboard_chart_data.php")
    .then(res => res.json())
    .then(data => {
      // Prepare labels and values for each dataset
      const yearLabels = data.yearly.map(r => r.academic_year);
      const yearValues = data.yearly.map(r => r.total);
      const weekLabels = data.weekly.map(r => "Week " + r.week_num);
      const weekValues = data.weekly.map(r => r.total);
      const dayLabels = data.daily.map(r => r.day);
      const dayValues = data.daily.map(r => r.total);

      // Concatenate all labels for shared x-axis
      const allLabels = [...yearLabels, ...weekLabels, ...dayLabels];

      // Pad shorter datasets with nulls to match total length
      const totalLength = allLabels.length;
      const paddedYearValues = [...yearValues, ...Array(totalLength - yearValues.length).fill(null)];
      const paddedWeekValues = [...Array(yearLabels.length).fill(null), ...weekValues, ...Array(totalLength - (yearLabels.length + weekValues.length)).fill(null)];
      const paddedDayValues = [...Array(yearLabels.length + weekValues.length).fill(null), ...dayValues];

      // Create single combined line chart
      new Chart(document.getElementById("combinedChart"), {
        type: "line",
        data: {
          labels: allLabels,
          datasets: [
            {
              label: "Enrolled Students (Yearly)",
              data: paddedYearValues,
              fill: false,
              borderColor: "#c0392b",
              backgroundColor: "#e74c3c",
              tension: 0.1,
              pointRadius: 4,
              pointHoverRadius: 6
            },
            {
              label: "Enrolled Students (Weekly)",
              data: paddedWeekValues,
              fill: false,
              borderColor: "#c0392b",
              backgroundColor: "#f39c12",
              tension: 0.1,
              pointRadius: 4,
              pointHoverRadius: 6
            },
            {
              label: "Enrolled Students (Daily)",
              data: paddedDayValues,
              fill: false,
              borderColor: "#c0392b",
              backgroundColor: "#d68910",
              tension: 0.1,
              pointRadius: 4,
              pointHoverRadius: 6
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(192, 57, 43, 0.1)'
              }
            },
            x: {
              grid: {
                color: 'rgba(192, 57, 43, 0.1)'
              }
            }
          }
        }
      });
    })
    .catch(err => console.error("Chart error:", err));
}

loadCharts();
</script>

<script>
// Student monitoring auto-update
let autoUpdateInterval;

function loadStudentData(search = '', date = '') {
  const params = new URLSearchParams();
  if (search) params.append('search', search);
  if (date) params.append('date', date);
  const url = `student_monitoring_data.php?${params.toString()}`;

  fetch(url)
    .then(res => res.json())
    .then(students => {
      const tableBody = document.querySelector('#studentTable tbody') || document.createElement('tbody');
      tableBody.innerHTML = '';

      if (students.length > 0) {
        students.forEach(student => {
          const row = tableBody.insertRow();
          row.innerHTML = `
            <td>${student.ID}</td>
            <td>${student.fullname}</td>
            <td>${student.yr_lvl}</td>
            <td>${student.crs}</td>
            <td>${student.Student_Number}</td>
            <td>${student.status}</td>
            <td>${student.enrolled_status}</td>
            <td class='actionbtns'>
              <a class='edit-btn' href='edit_student.php?id=${student.ID}'>Edit</a>
              <a class='grades-btn' href='admin_student_grades.php?id=${student.ID}'>Manage Grades</a>
              ${student.enrolled_status === 'Enrolled' ?
                `<button onclick="changeStatus(${student.ID})" class='change-status-btn'>Change Status</button>` :
                `<button onclick="approveStudent(${student.ID})" class='approve-btn'>Approve</button>`
              }
            </td>
          `;
        });
      } else {
        const row = tableBody.insertRow();
        row.innerHTML = '<td colspan="8">No students found.</td>';
      }

      if (!document.querySelector('#studentTable tbody')) {
        document.querySelector('#studentTable').appendChild(tableBody);
      }
    })
    .catch(err => console.error('Error loading student data:', err));
}

function startAutoUpdate() {
  loadStudentData();
  autoUpdateInterval = setInterval(() => {
    const search = document.getElementById('search').value;
    const date = document.getElementById('date').value;
    loadStudentData(search, date);
  }, 10000); // Update every 10 seconds
}

function stopAutoUpdate() {
  if (autoUpdateInterval) {
    clearInterval(autoUpdateInterval);
  }
}

// Filter form submission
document.getElementById('filterForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const search = document.getElementById('search').value;
  const date = document.getElementById('date').value;
  loadStudentData(search, date);
});

// Reset link
document.querySelector('a[href="admin.php?view=monitor"]').addEventListener('click', function(e) {
  e.preventDefault();
  document.getElementById('search').value = '';
  document.getElementById('date').value = '';
  loadStudentData();
});

// Start auto-update when monitoring view is shown
function showBox(which) {
  var enrolled = document.getElementById("DashB");
  var registered = document.getElementById("moni");
  var adminCenter = document.getElementById("admin_center");

  if (which === "dash") {
    enrolled.style.display = "flex";
    registered.style.display = "none";
    adminCenter.style.display = "none";
    stopAutoUpdate();
  } else if (which === "monitor") {
    registered.style.display = "block";
    enrolled.style.display = "none";
    adminCenter.style.display = "none";
    startAutoUpdate();
  } else if (which === "admin_center") {
    adminCenter.style.display = "block";
    enrolled.style.display = "none";
    registered.style.display = "none";
    stopAutoUpdate();
  }
}

// Message display function
function showMessage(msg) {
  const alertBox = document.createElement('div');
  alertBox.style.cssText = 'padding:10px; background:#dff0d8; color:#3c763d; border:1px solid #3c763d; margin-bottom:10px; border-radius:5px; position:fixed; top:10px; right:10px; z-index:1000;';
  alertBox.textContent = msg;
  document.body.appendChild(alertBox);
  setTimeout(() => {
    alertBox.style.opacity = '0';
    setTimeout(() => document.body.removeChild(alertBox), 1000);
  }, 5000);
}

// AJAX functions for approve and change status
function approveStudent(id) {
  fetch('admin.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: new URLSearchParams({approve_id: id})
  })
  .then(res => res.json())
  .then(data => {
    showMessage(data.msg);
    loadStudentData();
  })
  .catch(err => console.error('Error:', err));
}

function changeStatus(id) {
  fetch('admin.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: new URLSearchParams({change_status_id: id})
  })
  .then(res => res.json())
  .then(data => {
    showMessage(data.msg);
    loadStudentData();
  })
  .catch(err => console.error('Error:', err));
}

// Initial load if monitoring view
if (window.location.search.includes('view=monitor')) {
  startAutoUpdate();
}
</script>

</body>
</html>
