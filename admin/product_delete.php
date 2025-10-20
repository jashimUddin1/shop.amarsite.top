<?php include 'includes/auth.php'; require_once "../db/dbcon.php";
$id=(int)($_GET['id']??0);
$stmt=$con->prepare("DELETE FROM products WHERE id=?");
$stmt->bind_param("i",$id); $stmt->execute();
$_SESSION['warning'] = "Product delete successful";
header("Location: products.php");
