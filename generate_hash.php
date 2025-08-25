<?php
$password_to_hash = "password123"; // Your desired password
$hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);
echo "Hashed password for '{$password_to_hash}': " . $hashed_password;
?>