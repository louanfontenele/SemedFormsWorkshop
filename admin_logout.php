<?php
session_start();
unset($_SESSION['admin_logged_in']);
header("Location: admin.php");
exit;
?>
