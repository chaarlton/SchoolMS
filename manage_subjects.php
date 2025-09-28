<?php

include 'check_access.php';
requireRole('admin');

$conn = mysqli_connect("sql112.infinityfree.com", "if0_40025418", "milkosry4", "if0_40025418_escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Edit subject
$subject_id = $_GET['id'] ?? null;
$subj_code = $subj_desc = $unit_count = $level = $subj_sort = $semester = '';

if ($subject_id) {
    $subject_id = (int)$subject_id;
    $sql = "SELECT * FROM escr_subjects WHERE ID = $subject_id";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res)) {
        $subject = mysqli_fetch_assoc($res);
        $subj_code = $subject['subj_code'];
        $subj_desc = $subject['subj_desc'];
        $unit_count = $subject['unit_count'];
        $level = $subject['level'];
        $subj_sort = $subject['subj_sort'];
        $semester = $subject['semester'];
    } else die("Subject not found.");
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subj_code = mysqli_real_escape_string($conn, $_POST['subj_code']);
    $subj_desc = mysqli_real_escape_string($conn, $_POST['subj_desc']);
    $unit_count = (int)$_POST['unit_count'];
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    $subj_sort = mysqli_real_escape_string($conn, $_POST['subj_sort']);
    $semester = (int)$_POST['semester'];

    if (!empty($_POST['subject_id'])) {
        $update_id = (int)$_POST['subject_id'];
        $sql = "UPDATE escr_subjects SET 
                subj_code='$subj_code',
                subj_desc='$subj_desc',
                unit_count='$unit_count',
                level='$level',
                subj_sort='$subj_sort',
                semester='$semester'
                WHERE ID=$update_id";
        mysqli_query($conn, $sql) or die("Update failed: " . mysqli_error($conn));
        $message = "Subject updated successfully!";
    } else {
        $sql = "INSERT INTO escr_subjects (subj_code, subj_desc, unit_count, level, subj_sort, semester)
                VALUES ('$subj_code','$subj_desc','$unit_count','$level','$subj_sort','$semester')";
        mysqli_query($conn, $sql) or die("Insert failed: " . mysqli_error($conn));
        $message = "New subject added successfully!";
    }
}

// Fetch all subjects
$all_subjects = mysqli_query($conn, "SELECT * FROM escr_subjects ORDER BY level, semester, subj_sort, subj_code");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Subjects</title>
<link rel="stylesheet" href="admin.css?v1">
  <link rel="shortcut icon" href="Picture3.png" type="image/x-icon">
<style>
form { margin-bottom:20px; } input, select { padding:5px; margin:5px 0; width:100%; max-width:300px; }
table { border-collapse: collapse; width:100%; margin-top:20px; }
th, td { border:1px solid #ddd; padding:8px; } th { background:#2c3e50; color:#fff; }
tr:nth-child(even) { background:#f9f9f9; } tr:hover { background:#f1f1f1; }
</style>
</head>
<body>
<h2><?php echo $subject_id ? "Edit Subject" : "Add New Subject"; ?></h2>
<?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

<form method="POST">
    <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subject_id); ?>">

    <label>Subject Code</label>
    <input type="text" name="subj_code" value="<?php echo htmlspecialchars($subj_code); ?>" required>

    <label>Subject Description</label>
    <input type="text" name="subj_desc" value="<?php echo htmlspecialchars($subj_desc); ?>" required>

    <label>Unit Count</label>
    <input type="number" name="unit_count" value="<?php echo htmlspecialchars($unit_count); ?>" required min="1">

    <label>Level</label>
    <select name="level" required>
        <option value="">-- Select Level --</option>
        <?php
        foreach (['1ST YEAR','2ND YEAR','3RD YEAR','4TH YEAR'] as $yr) {
            echo "<option value='$yr' ".($level==$yr?'selected':'').">$yr</option>";
        }
        ?>
    </select>

    <label>Course</label>
    <select name="subj_sort" required>
        <option value="">-- Select Course --</option>
        <option value="IT" <?php if($subj_sort=='IT') echo 'selected'; ?>>INFORMATION TECHNOLOGY (BSIT)</option>
        <option value="CS" <?php if($subj_sort=='CS') echo 'selected'; ?>>COMPUTER SCIENCE (BSCS)</option>
        <option value="BSA" <?php if($subj_sort=='BSA') echo 'selected'; ?>>ACCOUNTANCY (BSA)</option>
    </select>

    <label>Semester</label>
    <select name="semester" required>
        <option value="1" <?php if($semester==1) echo 'selected'; ?>>1st Semester</option>
        <option value="2" <?php if($semester==2) echo 'selected'; ?>>2nd Semester</option>
    </select>

    <button type="submit"><?php echo $subject_id ? "Update Subject" : "Add Subject"; ?></button>
</form>

<h3>All Subjects</h3>
<table>
<tr>
<th>ID</th><th>Code</th><th>Description</th><th>Units</th><th>Level</th><th>Course</th><th>Semester</th><th>Action</th>
</tr>
<?php while ($row = mysqli_fetch_assoc($all_subjects)): ?>
<tr>
<td><?php echo $row['ID']; ?></td>
<td><?php echo htmlspecialchars($row['subj_code']); ?></td>
<td><?php echo htmlspecialchars($row['subj_desc']); ?></td>
<td><?php echo $row['unit_count']; ?></td>
<td><?php echo $row['level']; ?></td>
<td><?php echo $row['subj_sort']; ?></td>
<td><?php echo $row['semester']==1?'1st':'2nd'; ?></td>
<td><a href="?id=<?php echo $row['ID']; ?>" style="color:blue;">Edit</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
