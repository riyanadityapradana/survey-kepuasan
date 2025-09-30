<?php
// admin/logout.php - Logout admin
session_start();
session_destroy();
header('Location: ../login.php');
exit;
?>
