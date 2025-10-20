<?php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "shopdb";

$con = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($con->connect_error) {
    die("Database connection failed: " . $con->connect_error);
}
$con->set_charset("utf8mb4");
?>
