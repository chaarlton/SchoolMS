<?php
include 'check_access.php';
requireRole('admin');
$conn = mysqli_connect("localhost","root","","escr_dbase");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $password, $user_id);
    if ($stmt->execute()) {
        header("Location: admin_accounts.php?success=1");
        exit();
    } else {
        $error = "Update failed: " . $stmt->error;
    }
}

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

    function openModal(userId, username, password) {
        document.getElementById('editUserId').value = userId;
        document.getElementById('editUsername').value = username;
        document.getElementById('editPassword').value = password;
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
  </script>
</head>
<body>
  <h2>🟢 Student Accounts</h2>
  <?php if (isset($_GET['success'])): ?>
    <p style="color: green; background: #e6f6e6; padding: 10px; border-radius: 5px;">Account updated successfully!</p>
  <?php endif; ?>
  <?php if (isset($error)): ?>
    <p style="color: red; background: #ffeded; padding: 10px; border-radius: 5px;"><?= htmlspecialchars($error); ?></p>
  <?php endif; ?>
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
            <button class="btn show-btn" onclick="openModal(<?= $row['user_id']; ?>, '<?= htmlspecialchars(addslashes($row['username'])); ?>', '<?= htmlspecialchars(addslashes($row['password'])); ?>')">Edit</button>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No student accounts created yet.</p>
  <?php endif; ?>

  <!-- Edit Modal -->
  <div id="editModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
    <div style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 400px; border-radius: 5px;">
      <h3>Edit Student Account</h3>
      <form method="post" id="editForm">
        <input type="hidden" name="user_id" id="editUserId">
        <label for="editUsername">Username:</label><br>
        <input type="text" id="editUsername" name="username" required style="width: 100%; padding: 8px; margin: 5px 0;"><br>
        <label for="editPassword">Password:</label><br>
        <input type="password" id="editPassword" name="password" required style="width: 100%; padding: 8px; margin: 5px 0;"><br><br>
        <button type="submit" style="background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Update</button>
        <button type="button" onclick="closeModal()" style="background: #95a5a6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Cancel</button>
      </form>
    </div>
  </div>
</body>
</html>
