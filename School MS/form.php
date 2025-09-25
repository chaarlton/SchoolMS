<?php
error_reporting(0);
$conn = mysqli_connect("localhost","root","","escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic Info
    $lname = $_POST['lname'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $bday = $_POST['bday'];
    $pbirth = $_POST['pbirth'];
    $gender = $_POST['gender'];
    $marital = $_POST['marital'];
    $yr_lvl = $_POST['year_level'];
    $course = $_POST['course'];
    $mol = $_POST['mol'];
    // Address
    $street = $_POST['street'];
    $brgy = $_POST['brgy'];
    $city = $_POST['city'];

    // Academic info
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];

    // Check if student already exists
    $check_sql = "SELECT * FROM student_login 
                  WHERE L_name='$lname' AND F_name='$fname' 
                  AND M_name='$mname' AND bday='$bday'";
    $result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        $errorMsg = "❌ Student already exists!";
    } else {
        // Insert profile into student_login ONLY
        $sql = "INSERT INTO student_login 
           (L_name, F_name, M_name, bday, pbirth, gender, marital, yr_lvl, crs, street, brgy, city_mun, mol) 
           VALUES 
           ('$lname', '$fname', '$mname', '$bday', '$pbirth', '$gender', '$marital', '$yr_lvl', '$course', '$street', '$brgy', '$city', '$mol')";

        if (mysqli_query($conn, $sql)) {
            $msg = "✅ Profile submitted successfully! Await admin approval for enrollment.";
            // Do NOT insert into student_registrations here
        } else {
            $errorMsg = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCR | Student Registration Form</title>
    <link rel="stylesheet" href="Form.css?v=8">
    <link rel="shortcut icon" href="Picture3.png" type="image/x-icon">
</head>
<body>
    <div class="main-container">
    <section class="side">
        <div class="side-nav">
        <input type="checkbox" name="" id="" class="magic">
          <div class="line1"></div>
          <div class="line1"></div>
          <div class="line1"></div>
          <div class="menu">
            <div class="side-menu">
         <nav class="side-nav">
             <img src="Picture3.png">
            <ul>
                <li><a>HOME</a></li>
                <li><a>ANNOUNCEMENT</a></li>
                <li><a>NEWS</a></li>
                <li><a>FORMS</a></li>
            </ul>
        </nav>
            </div>
          </div>
        </div>
        </section>

<div class="main">
    <h1>STUDENT REGISTRATION FORM</h1>
    <div class="reg-form">
            <?php if (!empty($errorMsg)): ?>
  <div class="another">
    <div class="notice">
      <p><?= htmlspecialchars($errorMsg) ?></p>
    </div>
  </div>
<?php endif; ?>
        <div class="contents">
            
        <form method="POST" action="form.php" >
            <h3 style="margin-bottom: -10px;">BASIC INFORMATION</h3>
            <div class="txt">
                <input type="text" class="txt1" value="" required name="lname">
                <label class="txt1" >LAST NAME</label>
                </div>
            <div class="txt">
                <input type="text" class="txt1" required name="fname">
                <label class="txt1" >FIRST NAME</label>
                </div>
            <div class="txt">
                <input type="text" class="txt1" required name="mname">
                <label class="txt1" >MIDDLE NAME</label>
                </div>
            <div class="txt">
                <label style="color: black;">DATE OF BIRTH</label>
                <input class="non-text" type="date" required name="bday">
                </div>
            <div style="margin-top: -10px;" class="txt">
                <input type="text" class="txt1" required name="pbirth">
                <label class="txt1">PLACE OF BIRTH</label>
                </div>

            <div class="txt" style="margin-bottom: 0;">
                <label style="color: black; font-size: 12px;">GENDER:</label>
                <label style="color: black; font-size: 12px; margin-left: 40px;">MARITAL STATUS:</label><br>
                <select style="margin-left: 10px; border-radius: 10px; " name="gender">
                    <option value="MALE">MALE</option>
                    <option value="FEMALE">FEMALE</option>
                </select>
                <select style="margin-left: 30px; border-radius: 10px; margin-bottom: -50px;" name="marital">
                    <option value="SINGLE">SINGLE</option>
                    <option value="MARRIED">MARRIED</option>
                    <option value="WIDOWED">WIDOWED</option>
                </select>
                </div>
    <div class="no-text">
    <h3 style="margin-bottom: 0px;">YEAR LEVEL</h3>
    <h3 style="text-align: center; font-size:7px ; margin-bottom: -10px; color:gray;">INDICATE WHAT LEVEL YOU ARE CURRENTLY ENROLLING TO:</h3>    
    <div class="r-button">
    <input class="stand-alone" type="radio" name="year_level" id="" value="1ST YEAR COLLEGE"> 1ST YEAR COLLEGE
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="year_level" id="" value="2ND YEAR COLLEGE"> 2ND YEAR COLLEGE
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="year_level" id="" value="3RD YEAR COLLEGE"> 3RD YEAR COLLEGE
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="year_level" id="" value="4TH YEAR COLLEGE"> 4TH YEAR COLLEGE
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="year_level" id="" value="TRANSFEREE"> TRANSFEREE
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="year_level" id="" value="OLD/RETURNING STUDENT"> OLD/RETURNING STUDENT
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="year_level" id="" value="SHS - GRADE 11"> SHS - GRADE 11
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="year_level" id="" value="SHS - GRADE 12"> SHS - GRADE 12
    </div>
    <h3>ACADEMIC YEAR & SEMESTER</h3>
<div class="txt">
    <label style="color:black;">Academic Year</label>
    <input type="text" name="academic_year" required placeholder="2025-2026" value="2025-2026" readonly>
</div>
<div class="section1">
<div class="txt">
    <label style="color:black;">Semester</label>
    <select name="semester" required>
        <option value="1" selected contenteditable="false">1st Semester</option>
        <option value="2" disabled>2nd Semester</option>
    </select>
</div>
<div class="txt">
    <label style="color:black;">MODE OF LEARNING</label>
    <select name="mol" required>
        <option value="ONLINE">ONLINE/HYBRID(1 DAY F2F)</option>
        <option value="F2F" selected>FULL FACE TO FACE </option>
    </select>
</div>
</div>
<div class="no-text" >
    <h3 style="margin-bottom: 5px;">COURSE/PROGRAM</h3>
    
    <div class="r-button" >
    <input class="stand-alone" type="radio" name="course" id="" value="INFORMATION TECHNOLOGY (BSIT)">
    <div class="lbl">INFORMATION TECHNOLOGY (BSIT)
    </div> 
    </div>

    <div class="r-button" >
    <input class="stand-alone" type="radio" name="course" id="" value="COMPUTER SCIENCE (BSCS)">
    <div class="lbl">COMPUTER SCIENCE (BSCS)
    </div> 
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="BSBA - MAJOR IN FM">     
    <div class="lbl">BSBA - MAJOR IN FM</div> 
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="BSBA - MAJOR IN MM">     
    <div class="lbl">BSBA - MAJOR IN MM</div> 
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="BSBA - MAJOR IN HRDM">     
    <div class="lbl">BSBA - MAJOR IN HRDM</div> 
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="BTVTED - FSM">     
    <div class="lbl">BTVTED - FSM</div> 
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="BTVTED - ELEC">     
    <div class="lbl">BTVTED - ELEC</div> 
    </div>

            <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="BSAIS - ACCOUNTING INFORMATION SYSTEM">     
    <div class="lbl">BSAIS - ACCOUNTING INFORMATION SYSTEM</div> 
    </div>

        <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="BSOA - OFFICE ADMINISTRATION">     
    <div class="lbl">BSOA - OFFICE ADMINISTRATION</div> 
    </div>


    <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="SHS - HUMSS">     
    <div class="lbl">SHS - HUMSS</div> 
    </div>

    <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="SHS - ABM">     
    <div class="lbl">SHS - ABM</div> 
</div>
    <div class="r-button">
    <input class="stand-alone" type="radio" name="course" id="" value="SHS - ICT">     
    <div class="lbl">SHS - ICT</div> 

    </div>

    
    </div>
    </div>

                <h3 >ADDRESS</h3>
                <div style="margin-top: -10px;" class="txt">
                <input type="text" class="txt1" required name="street">
                <label class="txt1">Street/House No./Subd/Village</label>
                </div>
                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required name="brgy">
                <label class="txt1">BARANGAY</label>
                </div>

                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required name="city">
                <label class="txt1">CITY/MUNICIPALITY</label>
                </div>

                <div style="margin-top: 20px;" class="txt" name="province">
                <input type="text" class="txt1" required >
                <label class="txt1">PROVINCE</label>
                </div>

                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">ZIP CODE</label>
                </div>
                <h3 >CONTACT INFORMATION</h3>

                <div style="margin-top: 0px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">CONTACT NUMBER</label>
                </div>
                
                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">EMAIL ADDRESS</label>
                </div> 
                <h3>OTHER INFORMATION</h3>
                
                <div style="margin-top: 0px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">MOTHER'S MAIDEN NAME</label>
                </div> 

                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">FATHER'S NAME</label>
                </div> 

                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">GUARDIAN'S NAME</label>
                </div> 
                
                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">GUARDIAN'S ADDRESS</label>
                </div> 

                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">EMERGENCY CONTACT NAME</label>
                </div>

                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">EMERGENCY CONTACT NUMBER</label>
                </div> 

                <h3>EDUCATIONAL <br>BACKGROUND</h3>

                
                <div style="margin-top: 0px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">PRIMARY SCHOOL</label>
                </div>
                
                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">SECONDARY SCHOOL</label>
                </div> 

                <div style="margin-top: 20px;" class="txt">
                <input type="text" class="txt1" required >
                <label class="txt1">COLLEGE/UNIVERSITY (For Transferee)</label>
                </div>
                <hr style="background-color: none; height: 20px; border: none;">
            </div>

        
        </div>
        <table width="100%">
  <td><hr color="red"   /></td>
  <td style=" width:1px; padding: 0 10px; white-space: nowrap;">END OF FORM</td>
  <td ><hr color="red"  /></td>
</table>
<hr style="width: 98%; background-color: black; border: 2px solid black;">
        <div class="terms">
    <div class="tad">
    <input type="checkbox" name="" id="">
    </div>
    <p> By checking this box, you are agreeing to our terms of service.</p>
    </div>
    <div class="terms">
    <div class="tad">
    <input type="checkbox" name="" id="">
    </div>
    <p> By checking this box, you are agreeing that all of the information above are accurate and true.</p>
    </div>
    <button type="submit" class="sub-button">SUBMIT</button>
            </form>
    </div>

</div>
<div class="footer">
    <p align="center" style="color: gray; margin-left: 20%;">East Systems Colleges of Rizal 2025</p>
</div>
</body>
</html>
