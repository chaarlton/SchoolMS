<?php
include 'check_access.php';

// ✅ Allow only admin or student
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'student'])) {
    die("Unauthorized access.");
}

// ✅ Database connection
$conn = mysqli_connect(
    "sql112.infinityfree.com",
    "if0_40025418",
    "milkosry4",
    "if0_40025418_escr_dbase"
);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ✅ Get POST data
$student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
$grades     = $_POST['grades'] ?? [];
$semester   = isset($_POST['semester']) ? (int)$_POST['semester'] : 1;
$year       = $_POST['year'] ?? ''; // only needed for admin redirect

if ($student_id <= 0 || empty($grades)) {
    die("No grades to save.");
}

// ✅ Save grades (insert or update)
foreach ($grades as $subject_id => $grade) {
    $subject_id = (int)$subject_id;
    $grade = trim($grade);

    if ($grade !== '') {
        $sql = "
            INSERT INTO student_grades (student_id, subject_id, grade, semester, date_recorded)
            VALUES ('$student_id', '$subject_id', '$grade', '$semester', NOW())
            ON DUPLICATE KEY UPDATE 
                grade = VALUES(grade),
                date_recorded = NOW()
        ";
        if (!mysqli_query($conn, $sql)) {
            error_log("Failed to save grade for subject $subject_id: " . mysqli_error($conn));
        }
    }
}

// ✅ Redirect back
if ($_SESSION['role'] === 'admin') {
    header("Location: admin_student_grades.php?id=$student_id&year=$year&semester=$semester&success=1");
} else {
    header("Location: student_portal.php?success=1");
}
exit();
