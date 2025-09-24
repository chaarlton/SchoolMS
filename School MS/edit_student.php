<?php
include 'check_access.php';
requireRole('admin');
?>

<?php
// edit_student.php
$conn = mysqli_connect("localhost","root","","escr_dbase");
if (!$conn) die("DB connect error: " . mysqli_connect_error());

$error = "";
$success = "";

// Load student for GET ?id=...
$student = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sel = $conn->prepare("SELECT ID, L_name, F_name, M_name, bday, pbirth, gender, marital, yr_lvl, crs, street, brgy, city_mun, Student_Number, `Date` FROM student_login WHERE ID = ?");
    if (!$sel) {
        die("Prepare failed: " . $conn->error);
    }
    $sel->bind_param("i", $id);
    $sel->execute();
    $res = $sel->get_result();
    $student = $res->fetch_assoc();
    if (!$student) {
        die("Student not found.");
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize / fetch posted values
    $id     = (int)$_POST['ID'];
    $lname  = trim($_POST['L_name']);
    $fname  = trim($_POST['F_name']);
    $mname  = trim($_POST['M_name']);
    $bday   = trim($_POST['bday']);
    $pbirth = trim($_POST['pbirth']);
    $gender = trim($_POST['gender']);
    $marital= trim($_POST['marital']);
    $yr_lvl = trim($_POST['yr_lvl']);
    $crs    = trim($_POST['crs']);
    $street = trim($_POST['street']);
    $brgy   = trim($_POST['brgy']);
    $stunum   = trim($_POST['st']);
    $city   = trim($_POST['city_mun']);

    // Prepared UPDATE (12 text fields + ID int => 12 's' and 1 'i')
    $sql = "UPDATE student_login
            SET L_name = ?, F_name = ?, M_name = ?, bday = ?, pbirth = ?, gender = ?, marital = ?, yr_lvl = ?, crs = ?, street = ?, brgy = ?, Student_Number = ?, city_mun = ?
            WHERE ID = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = "Prepare failed: " . $conn->error;
    } else {
        $types = str_repeat('s', 13) . 'i'; // 12 strings then integer
        // bind params (order must match the placeholders)
        if (!$stmt->bind_param($types,
            $lname, $fname, $mname, $bday, $pbirth, $gender, $marital,
            $yr_lvl, $crs, $street, $brgy, $stunum, $city, $id)) {
            $error = "Bind failed: " . $stmt->error;
        } elseif (!$stmt->execute()) {
            $error = "Execute failed: " . $stmt->error;
        } else {
            $success = "Student updated successfully.";
            // redirect back to admin panel (optional)
            header("Location: admin.php");
            exit();
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Edit Student</title>
<style>
.form { max-width:700px; margin:20px auto; }
label{display:block;margin-top:8px}
.notice{background:#ffeded;border:1px solid #ff6b6b;padding:10px;border-radius:6px;color:#a00}
.ok{background:#e6f6e6;border:1px solid #4caf50;padding:10px;border-radius:6px;color:#065}
</style>
</head>
<body>
<div class="form">
    <h2>Edit Student</h2>

    <?php if ($error): ?>
        <div class="notice"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="ok"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($student): ?>
    <form method="post">
        <input type="hidden" name="ID" value="<?php echo htmlspecialchars($student['ID']); ?>">

        <label>Last name <input name="L_name" value="<?php echo htmlspecialchars($student['L_name']); ?>"></label>
        <label>First name <input name="F_name" value="<?php echo htmlspecialchars($student['F_name']); ?>"></label>
        <label>Middle name <input name="M_name" value="<?php echo htmlspecialchars($student['M_name']); ?>"></label>
        <label>Birthday <input type="date" name="bday" value="<?php echo htmlspecialchars($student['bday']); ?>"></label>
        <label>Place of birth <input name="pbirth" value="<?php echo htmlspecialchars($student['pbirth']); ?>"></label>
        <label>Gender <input name="gender" value="<?php echo htmlspecialchars($student['gender']); ?>"></label>
        <label>Marital <input name="marital" value="<?php echo htmlspecialchars($student['marital']); ?>"></label>
        <label>Year level <input name="yr_lvl" value="<?php echo htmlspecialchars($student['yr_lvl']); ?>"></label>
        <label>Course <input name="crs" value="<?php echo htmlspecialchars($student['crs']); ?>"></label>
        <label>Street <input name="street" value="<?php echo htmlspecialchars($student['street']); ?>"></label>
        <label>Barangay <input name="brgy" value="<?php echo htmlspecialchars($student['brgy']); ?>"></label>
        <label>Student Number <input name="st" value="<?php echo htmlspecialchars($student['Student_Number']); ?>"></label>
        <label>City <input name="city_mun" value="<?php echo htmlspecialchars($student['city_mun']); ?>"></label>

        <div style="margin-top:12px">
            <button type="submit">Update</button>
            <a href="admin.php" style="margin-left:10px">Cancel</a>
        </div>
    </form>
    <?php else: ?>
        <p>No student selected.</p>
    <?php endif; ?>
</div>
</body>
</html>
