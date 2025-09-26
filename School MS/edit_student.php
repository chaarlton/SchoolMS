<?php
include 'check_access.php';
requireRole('admin');
?>

<?php
// edit_student.php
$conn = mysqli_connect("localhost","root","","escr_dbase");
if (!$conn) die("DB connect error: " . mysqli_connect_error());

// Calculate current academic year and semester
$year_now = date("Y");
$current_year = $year_now . "-" . ($year_now + 1);
$month = date("n");
$current_semester = ($month >= 6 && $month <= 10) ? 1 : 2;

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

    // Fetch current mol
    $mol = "";
    $mol_sel = $conn->prepare("SELECT mol FROM student_registrations WHERE student_id = ? AND academic_year = ? AND semester = ?");
    $mol_sel->bind_param("isi", $id, $current_year, $current_semester);
    $mol_sel->execute();
    $mol_res = $mol_sel->get_result();
    if ($mol_row = $mol_res->fetch_assoc()) {
        $mol = $mol_row['mol'];
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
    $mol     = trim($_POST['mol'] ?? '');

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
            // Update mol and course in student_registrations
            $reg_sql = "UPDATE student_registrations SET mol = ?, course = ? WHERE student_id = ? AND academic_year = ? AND semester = ?";
            $reg_stmt = $conn->prepare($reg_sql);
            if ($reg_stmt) {
                $reg_stmt->bind_param("ssisi", $mol, $crs, $id, $current_year, $current_semester);
                $reg_stmt->execute();
                $reg_stmt->close();
            }

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
body { margin: 0; padding: 20px; background-color: #f4f4f4; }
.form { max-width: 800px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #333; }
label { display: block; margin-top: 10px; font-weight: bold; color: #555; }
input[type="text"], input[type="date"], select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; text-transform: uppercase; }
button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
button:hover { background-color: #0056b3; }
a { color: #007bff; text-decoration: none; margin-left: 10px; }
.notice { background: #ffeded; border: 1px solid #ff6b6b; padding: 10px; border-radius: 6px; color: #a00; margin-bottom: 10px; }
.ok { background: #e6f6e6; border: 1px solid #4caf50; padding: 10px; border-radius: 6px; color: #065; margin-bottom: 10px; }
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

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px; font-weight: bold;">Last name</td>
                <td style="padding: 10px;"><input type="text" name="L_name" value="<?php echo htmlspecialchars($student['L_name']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">First name</td>
                <td style="padding: 10px;"><input type="text" name="F_name" value="<?php echo htmlspecialchars($student['F_name']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Middle name</td>
                <td style="padding: 10px;"><input type="text" name="M_name" value="<?php echo htmlspecialchars($student['M_name']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Birthday</td>
                <td style="padding: 10px;"><input type="date" name="bday" value="<?php echo htmlspecialchars($student['bday']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Place of birth</td>
                <td style="padding: 10px;"><input type="text" name="pbirth" value="<?php echo htmlspecialchars($student['pbirth']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Gender</td>
                <td style="padding: 10px;"><input type="text" name="gender" value="<?php echo htmlspecialchars($student['gender']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Marital</td>
                <td style="padding: 10px;"><input type="text" name="marital" value="<?php echo htmlspecialchars($student['marital']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Year level</td>
                <td style="padding: 10px;"><input type="text" name="yr_lvl" value="<?php echo htmlspecialchars($student['yr_lvl']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Course</td>
                <td style="padding: 10px;">
                    <select name="crs">
                        <option value="INFORMATION TECHNOLOGY (BSIT)" <?php echo ($student['crs'] == 'INFORMATION TECHNOLOGY (BSIT)') ? 'selected' : ''; ?>>INFORMATION TECHNOLOGY (BSIT)</option>
                        <option value="COMPUTER SCIENCE (BSCS)" <?php echo ($student['crs'] == 'COMPUTER SCIENCE (BSCS)') ? 'selected' : ''; ?>>COMPUTER SCIENCE (BSCS)</option>
                        <option value="BSBA - MAJOR IN FM" <?php echo ($student['crs'] == 'BSBA - MAJOR IN FM') ? 'selected' : ''; ?>>BSBA - MAJOR IN FM</option>
                        <option value="BSBA - MAJOR IN MM" <?php echo ($student['crs'] == 'BSBA - MAJOR IN MM') ? 'selected' : ''; ?>>BSBA - MAJOR IN MM</option>
                        <option value="BSBA - MAJOR IN HRDM" <?php echo ($student['crs'] == 'BSBA - MAJOR IN HRDM') ? 'selected' : ''; ?>>BSBA - MAJOR IN HRDM</option>
                        <option value="BTVTED - FSM" <?php echo ($student['crs'] == 'BTVTED - FSM') ? 'selected' : ''; ?>>BTVTED - FSM</option>
                        <option value="BTVTED - ELEC" <?php echo ($student['crs'] == 'BTVTED - ELEC') ? 'selected' : ''; ?>>BTVTED - ELEC</option>
                        <option value="BSAIS - ACCOUNTING INFORMATION SYSTEM" <?php echo ($student['crs'] == 'BSAIS - ACCOUNTING INFORMATION SYSTEM') ? 'selected' : ''; ?>>BSAIS - ACCOUNTING INFORMATION SYSTEM</option>
                        <option value="BSOA - OFFICE ADMINISTRATION" <?php echo ($student['crs'] == 'BSOA - OFFICE ADMINISTRATION') ? 'selected' : ''; ?>>BSOA - OFFICE ADMINISTRATION</option>
                        <option value="SHS - HUMSS" <?php echo ($student['crs'] == 'SHS - HUMSS') ? 'selected' : ''; ?>>SHS - HUMSS</option>
                        <option value="SHS - ABM" <?php echo ($student['crs'] == 'SHS - ABM') ? 'selected' : ''; ?>>SHS - ABM</option>
                        <option value="SHS - ICT" <?php echo ($student['crs'] == 'SHS - ICT') ? 'selected' : ''; ?>>SHS - ICT</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Street</td>
                <td style="padding: 10px;"><input type="text" name="street" value="<?php echo htmlspecialchars($student['street']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Barangay</td>
                <td style="padding: 10px;"><input type="text" name="brgy" value="<?php echo htmlspecialchars($student['brgy']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Student Number</td>
                <td style="padding: 10px;"><input type="text" name="st" value="<?php echo htmlspecialchars($student['Student_Number']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">City</td>
                <td style="padding: 10px;"><input type="text" name="city_mun" value="<?php echo htmlspecialchars($student['city_mun']); ?>"></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Mode of Learning</td>
                <td style="padding: 10px;">
                    <select name="mol">
                        <option value="F2F" <?php echo ($mol == 'F2F') ? 'selected' : ''; ?>>F2F</option>
                        <option value="ONLINE" <?php echo ($mol == 'ONLINE') ? 'selected' : ''; ?>>ONLINE</option>
                    </select>
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-top: 20px;">
            <button type="submit">Update</button>
            <a href="admin.php" style="margin-left: 10px;">Cancel</a>
        </div>
    </form>
    <?php else: ?>
        <p>No student selected.</p>
    <?php endif; ?>
</div>
</body>
</html>
