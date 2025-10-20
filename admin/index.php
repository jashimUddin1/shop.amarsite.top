<?php //admin/index.php
include 'includes/header.php'; 

$p = $con->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'] ?? 0;
$u = $con->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'] ?? 0;
$low = $con->query("SELECT COUNT(*) c FROM products WHERE stock<5")->fetch_assoc()['c'] ?? 0;
?>
<h4 class="mb-4">Dashboard</h4>
<div class="row g-3">
  <div class="col-md-4"><div class="p-4 bg-primary text-white rounded shadow-sm"><div class="fs-5">Products</div><div class="fs-2 fw-bold"><?=$p?></div></div></div>
  <div class="col-md-4"><div class="p-4 bg-success text-white rounded shadow-sm"><div class="fs-5">Users</div><div class="fs-2 fw-bold"><?=$u?></div></div></div>
  <div class="col-md-4"><div class="p-4 bg-warning text-white rounded shadow-sm"><div class="fs-5">Low Stock (&lt;5)</div><div class="fs-2 fw-bold"><?=$low?></div></div></div>
</div>
<?php include 'includes/footer.php'; ?>

