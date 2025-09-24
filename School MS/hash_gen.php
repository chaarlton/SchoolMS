<?php
echo "Admin hash: " . password_hash("admin123", PASSWORD_DEFAULT) . "<br>";
echo "Student hash: " . password_hash("student123", PASSWORD_DEFAULT) . "<br>";
?>