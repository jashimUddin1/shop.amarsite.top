<?php //admin/includes/header.php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../db/dbcon.php";
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel — FASHN BD</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: #f7f8fa;
    }

    .sidebar {
      min-height: 100vh;
      background: #fff;
      border-right: 1px solid #eee;
    }

    /* base state */
    .sidebar .nav-link {
      color: #333;
      border-radius: .5rem
    }

    /* hover + active states (nav-link) */
    .sidebar .nav-link:hover {
      background: cadetblue;
      color: #fff !important
    }

    .sidebar .nav-link.active {
      background: cadetblue;
      color: #fff !important
    }

    /* Settings button inside sidebar dropdown */
    .sidebar .dropdown .btn {
      background: #f9f9f9;
      /* light default */
      border: 1px solid rgba(0, 0, 0, .08);
      width: 100%;
      text-align: left;
      color: #333;
    }

    .sidebar .dropdown .btn:focus {
      box-shadow: none;
    }

    /* hover/expanded = make it clearly visible */
    .sidebar .dropdown .btn:hover,
    .sidebar .dropdown .btn[aria-expanded="true"] {
      background: cadetblue;
      color: #fff !important;
      border-color: cadetblue;
    }

    /* children links inside the collapse */
    .sidebar .dropdown .collapse .nav-link {
      font-size: 0.9rem;
      color: #555;
      padding-left: 2rem;
    }

    .sidebar .dropdown .collapse .nav-link:hover,
    .sidebar .dropdown .collapse .nav-link.active {
      background: cadetblue;
      color: #fff !important;
      border-radius: .4rem;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-semibold" href="index.php">FASHN Admin</a>
      <div class="text-white small">
        Hi, <?= esc($_SESSION['admin']['username']) ?> (<?= esc($_SESSION['admin']['role']) ?>)
        <a href="logout.php" class="link-light ms-2">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-2 sidebar p-3">
        <nav class="nav flex-column gap-1">

          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php"><i
              class="bi bi-speedometer2 me-2"></i>Dashboard</a>
          <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'products') ? 'active' : '' ?>" href="products.php"><i
              class="bi bi-box-seam me-2"></i>Products</a>
          <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'orders') ? 'active' : '' ?>" href="orders.php"><i
              class="bi bi-cart-check me-2"></i>Orders</a>

          <?php if (is_super()): ?>
            <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'users') ? 'active' : '' ?>" href="users.php"><i
                class="bi bi-people me-2"></i>Users</a>

            <!-- Settings Dropdown -->
            <div class="dropdown">
              <button class="btn d-flex align-items-center justify-content-between" type="button"
                data-bs-toggle="collapse" data-bs-target="#settingsMenu"
                aria-expanded="<?= str_contains($_SERVER['PHP_SELF'], 'settings') ? 'true' : 'false' ?>">
                <span><i class="bi bi-gear me-2"></i>Settings</span>
                <i class="bi bi-chevron-down small"></i>
              </button>

              <div id="settingsMenu"
                class="collapse <?= str_contains($_SERVER['PHP_SELF'], 'settings') ? 'show' : '' ?> mt-1 ps-2">
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) == 'settings_basic.php' ? 'active' : '' ?>"
                  href="settings_basic.php">
                  <i class="bi bi-sliders me-1 text-muted"></i> Basic Settings
                </a>
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) == 'settings_advance.php' ? 'active' : '' ?>"
                  href="settings_advance.php">
                  <i class="bi bi-tools me-1 text-muted"></i> Advanced Settings
                </a>
              </div>
            </div>
          <?php endif; ?>

        </nav>
      </aside>

      <main class="col-md-10 p-4">