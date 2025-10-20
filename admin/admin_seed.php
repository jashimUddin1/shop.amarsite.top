<?php
require_once "../db/dbcon.php";

$username = "admin";
$password = "admin123";
$role = "super"; // বা 'manager'

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $con->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hash, $role);
if ($stmt->execute()) {
    echo "✅ Admin created successfully!<br>";
    echo "Username: $username<br>Password: $password";
} else {
    echo "❌ Error: " . $con->error;
}
