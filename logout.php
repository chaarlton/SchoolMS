<?php
session_start();
session_unset();
session_destroy();
header("Location:https://eastsystemsrizal.fwh.is/Login.php");
exit();
?>
