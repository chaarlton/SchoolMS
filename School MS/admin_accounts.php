<?php
include 'check_access.php';
requireRole('admin');
$conn = mysqli_connect("localhost","root","","escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Fetch all student accounts
$sql = "SELECT u.id AS user_id, u.username, u.role, u.created_at, u.password,
               s.F_name, s.L_name, s.Student_Number
        FROM users u
        JOIN student_login s ON u.student_id = s.ID
        ORDER BY s.L_name, s.F_name";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Accounts</title>
  <style>
      table { width: 100%; border-collapse: collapse; margin-top: 15px; }
      table, th, td { border: 1px solid #ddd; }
      th { background: #2c3e50; color: #fff; padding: 10px; text-align: left; }
      td { padding: 8px; font-size: 14px; }
      tr:nth-child(even) { background: #f9f9f9; }
      tr:hover { background: #f1f1f1; }
      .btn { padding:5px 10px; border-radius:5px; cursor:pointer; }
      .show-btn { background:#3498db; color:#fff; border:none; }
  </style>
  <script>
    function togglePassword(id) {
        const span = document.getElementById("pw-" + id);
        if (span.dataset.visible === "false") {
            span.innerText = span.dataset.password;
            span.dataset.visible = "true";
        } else {
            span.innerText = "••••••••";
            span.dataset.visible = "false";
        }
    }
  </script>
</head>
<body>
  <h2>🟢 Student Accounts</h2>
  <?php if ($result && mysqli_num_rows($result) > 0): ?>
    <table>
      <tr>
        <th>User ID</th>
        <th>Student Name</th>
        <th>Student Number</th>
        <th>Username</th>
        <th>Role</th>
        <th>Date Created</th>
        <th>Password</th>
        <th>Action</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $row['user_id']; ?></td>
          <td><?= htmlspecialchars($row['L_name'].", ".$row['F_name']); ?></td>
          <td><?= htmlspecialchars($row['Student_Number']); ?></td>
          <td><?= htmlspecialchars($row['username']); ?></td>
          <td><?= htmlspecialchars($row['role']); ?></td>
          <td><?= htmlspecialchars($row['created_at']); ?></td>
          <td>
            <span id="pw-<?= $row['user_id']; ?>" 
                  data-password="<?= htmlspecialchars($row['password']); ?>" 
                  data-visible="false">••••••••</span>
          </td>
          <td>
            <button class="btn show-btn" onclick="togglePassword(<?= $row['user_id']; ?>)">Show/Hide</button>
            <button class="btn show-btn" onclick="togglePassword(<?= $row['user_id']; ?>)">Edit</button>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No student accounts created yet.</p>
  <?php endif; ?>
</body>
</html>
