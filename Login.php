<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
        exit();
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: student_portal.php");
        exit();
    }
}
?>


<?php

$conn = mysqli_connect("sql112.infinityfree.com", "if0_40025418", "milkosry4", "if0_40025418_escr_dbase");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Plain-text password check
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // Store session variables
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] == 'admin') {
            header("Location: admin.php");
            exit();
        } elseif ($row['role'] == 'student') {
            $_SESSION['student_id'] = $row['student_id']; // link to student_login table
            header("Location: student_portal.php");
            exit();
        }
    } else {
        $error = "❌ Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>ESCR | PORTAL LOGIN</title>
  <link rel="stylesheet" href="login.css?v4">
      <link rel="shortcut icon" href="Picture3.png" type="image/x-icon">
</head>
<body>
  <div class="main">
    <div class="bg">
      <img src="Picture3.png">
    </div>
    <div class="frm">
      <div class="login-text">
        <h2 style="color: white; text-align: center;">EAST SYSTEMS COLLEGES OF RIZAL</h2>
        <img src="Picture3.png">

        <form method="POST">
          <div class="txt">
            <input type="text" name="username" required>
            <label>USERNAME</label>
          </div>
          <div class="txt">
            <input type="password" name="password" required>
            <label>PASSWORD</label>
          </div>
          <div class="txt" style="margin-top: -10px;">
            <button class="sbmt" type="submit">LOGIN</button>
          </div>
        </form>

        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
      </div>
    </div>
  </div>
</body>
</html>
