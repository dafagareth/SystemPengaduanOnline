<?php
$password = 'superadmin';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash untuk password 'admin123':<br>";
echo $hash;
?>