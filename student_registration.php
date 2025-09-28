<?php
$conn = mysqli_connect("localhost","root","","escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $year_level = $_POST['year_level'];

    // Insert new registration
    $sql = "INSERT INTO student_registrations (student_id, academic_year, semester, year_level, status)
            VALUES ('$student_id', '$academic_year', '$semester', '$year_level', 'ENROLLED')";

    if (mysqli_query($conn, $sql)) {
        echo "✅ Student registered successfully!";
    } else {
        echo "❌ Error: " . mysqli_error($conn);
    }
}

// Fetch students for dropdown
$students = mysqli_query($conn, "SELECT ID, F_name, L_name, Student_Number FROM student_login");
?>

<h2>Register Student for Semester</h2>
<form method="POST">
    <label>Student</label>
    <select name="student_id" required>
        <option value="">-- Select Student --</option>
        <?php while($row = mysqli_fetch_assoc($students)) { ?>
            <option value="<?= $row['ID'] ?>">
                <?= $row['Student_Number'] . " - " . $row['L_name'] . ", " . $row['F_name'] ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    <label>Academic Year</label>
    <input type="text" name="academic_year" placeholder="2025-2026" required>
    <br><br>

    <label>Semester</label>
    <select name="semester" required>
        <option value="1">1st Semester</option>
        <option value="2">2nd Semester</option>
    </select>
    <br><br>

    <label>Year Level</label>
    <select name="year_level" required>
        <option value="1ST YEAR">1st Year</option>
        <option value="2ND YEAR">2nd Year</option>
        <option value="3RD YEAR">3rd Year</option>
        <option value="4TH YEAR">4th Year</option>
    </select>
    <br><br>

    <button type="submit">Register</button>
</form>
