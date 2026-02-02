<?php
session_start();
$_SESSION['is_admin'] = true;
echo "Admin Session Set. <a href='notices_full.php'>Go to Notices</a>";
?>
