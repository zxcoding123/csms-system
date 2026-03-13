<?php
$newPassword = "TestStaff123!";
$newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
echo $newHashedPassword;
?>