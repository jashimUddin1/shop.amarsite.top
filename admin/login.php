<?php
session_start();
require_once "../db/dbcon.php";
$err = null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $u=$_POST['username']; $p=$_POST['password'];
  $stmt=$con->prepare("SELECT * FROM admins WHERE username=?");
  $stmt->bind_param("s",$u);
  $stmt->execute(); $res=$stmt->get_result();
  if($row=$res->fetch_assoc() and password_verify($p,$row['password'])){
    $_SESSION['admin']=$row;
    header("Location: index.php"); exit;
  } else $err="Invalid username or password!";
}
?>
<!doctype html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Admin Login</title></head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
<div class="card p-4 shadow" style="width:360px">
  <h4 class="text-center mb-3">Admin Login</h4>
  <?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>
  <form method="post">
    <input class="form-control mb-2" name="username" placeholder="Username" required>
    <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
    <button class="btn btn-dark w-100">Login</button>
  </form>
</div>
</body></html>
