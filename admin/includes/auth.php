<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit;
}
function is_super() {
  return isset($_SESSION['admin']['role']) && $_SESSION['admin']['role'] === 'super';
}
function is_manager() {
  return isset($_SESSION['admin']['role']) && $_SESSION['admin']['role'] === 'manager';
}
function esc($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
