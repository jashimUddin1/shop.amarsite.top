<?php //admin/index.php
include 'includes/header.php'; 

$p   = $con->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'] ?? 0;
$u   = $con->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'] ?? 0;
$low = $con->query("SELECT COUNT(*) c FROM products WHERE stock<5")->fetch_assoc()['c'] ?? 0;
?>

<h4 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard Overview</h4>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm bg-primary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-6 fw-semibold">Total Products</div>
            <div class="fs-2 fw-bold"><?=$p?></div>
          </div>
          <i class="bi bi-box-seam fs-1 opacity-75"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-0 shadow-sm bg-success text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-6 fw-semibold">Registered Users</div>
            <div class="fs-2 fw-bold"><?=$u?></div>
          </div>
          <i class="bi bi-people fs-1 opacity-75"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-0 shadow-sm bg-warning text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fs-6 fw-semibold">Low Stock (&lt;5)</div>
            <div class="fs-2 fw-bold"><?=$low?></div>
          </div>
          <i class="bi bi-exclamation-triangle fs-1 opacity-75"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="mt-5">
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Welcome</h5>
      <p class="text-muted mb-0">
        Welcome to your <strong>FASHN Admin Panel</strong> 👋 <br>
        Manage products, users, orders and customize your site from the sidebar.
      </p>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>


