<?php

include 'check_access.php';

// Only admin or student can save grades
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'student') {
        die("Unauthorized access.");
    }
} else {
    die("Unauthorized access.");
}

$conn = mysqli_connect("localhost", "root", "", "escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Get POST data
$student_id = $_POST['student_id'] ?? 0;
$grades = $_POST['grades'] ?? [];
$semester = isset($_POST['semester']) ? (int)$_POST['semester'] : 1;

if (!$student_id || empty($grades)) {
    die("No grades to save.");
}

// Insert or update grades
foreach ($grades as $subject_id => $grade) {
    $subject_id = (int)$subject_id;
    $grade = mysqli_real_escape_string($conn, $grade);

    if ($grade !== '') {
        $sql = "
        INSERT INTO student_grades (student_id, subject_id, grade, semester, date_recorded)
        VALUES ('$student_id', '$subject_id', '$grade', '$semester', NOW())
        ON DUPLICATE KEY UPDATE grade='$grade', date_recorded=NOW()
        ";
        mysqli_query($conn, $sql) or die("Failed to save grade: " . mysqli_error($conn));
    }
}

// Redirect back
if ($_SESSION['role'] === 'admin') {
    $year = $_POST['year'] ?? '';
    header("Location: admin_student_grades.php?id=$student_id&year=$year&semester=$semester&success=1");
    exit();
} else {
    header("Location: student_portal.php?success=1");
    exit();
}
