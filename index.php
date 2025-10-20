<?php
// index.php
require_once __DIR__ . '/db/dbcon.php';

// Site settings fetch
$settings = $con->query("SELECT * FROM settings WHERE id=1")->fetch_assoc();
$site_name = $settings['site_name'] ?? 'FASHN BD';
$footer_text = $settings['footer_text'] ?? '© FASHN BD';
$logo = $settings['logo'] ?? '';
$banner = $settings['banner'] ?? 'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?q=80&w=1600&auto=format&fit=crop';

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $con->query($sql);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($site_name) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>

  <!-- 🔹 Top Bar -->
  <div class="bg-dark text-white small py-2">
    <div class="container d-flex justify-content-between align-items-center">
      <div><i class="bi bi-badge-percent me-2"></i>Mega Sale: Buy 2 get 1 free</div>
      <div class="d-none d-md-flex gap-4 opacity-75">
        <div><i class="bi bi-truck me-2"></i>Free delivery over BDT 3,500</div>
        <div><i class="bi bi-shield-check me-2"></i>7-day easy returns</div>
      </div>
    </div>
  </div>

  <!-- 🔹 Navbar -->
  <nav class="sticky-top bg-white border-bottom py-3">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <img class="img-fluid rounded-circle" src="admin/<?= htmlspecialchars($logo) ?>" alt="Logo" style="height:38px;object-fit:contain;">
        <span class="fw-bold fs-4"><?= htmlspecialchars($site_name) ?></span>
        <span class="badge text-bg-secondary rounded-pill">BD</span>
      </div>
      <form class="d-none d-md-flex w-50" method="get">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" name="q" placeholder="Search tees, dresses, denim…">
        </div>
      </form>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary"><i class="bi bi-heart"></i></button>
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas"
          data-bs-target="#cartDrawer">
          <i class="bi bi-bag"></i>
          <span id="cartCount"
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
        </button>
      </div>
    </div>
  </nav>

  <!-- 🔹 Hero Section -->
  <section class="hero py-5 bg-light">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-md-6">
          <span class="badge rounded-pill text-bg-light border mb-3"><span
              class="badge rounded-pill bg-success me-2">&nbsp;</span> New drop live now</span>
          <h1 class="fw-bold display-6">Minimal streetwear for everyday comfort.</h1>
          <p class="text-muted">Build a clean, timeless wardrobe with premium basics and statement fits. Ethically made.
            Designed in Dhaka.</p>
          <a href="#grid" class="btn btn-dark rounded-pill px-4 py-2">Shop now</a>
        </div>
        <div class="col-md-6">
          <img src="https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?q=80&w=1600&auto=format&fit=crop"
            class="img-fluid rounded-4 shadow" alt="Hero">
        </div>
      </div>
    </div>
  </section>



  <div style="height:50vh" class="col-md-12">

    <img src="admin/<?= htmlspecialchars($banner) ?>" class="w-100 h-100 object-fit-cover" alt="Banner">

  </div>

  <!-- 🔹 Product Grid -->
  <main id="grid" class="py-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-end mb-4">
        <h2 class="h4 fw-bold">Featured Products</h2>
        <div class="text-muted small"><?= $result->num_rows ?> items</div>
      </div>

      <div class="row g-3">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card position-relative text-center">
              <?php if ($row['discount'] > 0): ?>
                <span class="discount-badge"><?= $row['discount'] ?>% OFF</span>
              <?php endif; ?>
              <img src="admin/<?= htmlspecialchars($row['image']) ?>" class="card-img-top"
                alt="<?= htmlspecialchars($row['name']) ?>">
              <div class="product-info">
                <p class="text-muted small mb-1"><i class="bi bi-clock me-1"></i>12-24 HOURS</p>
                <h6><?= htmlspecialchars($row['name']) ?></h6>
                <div class="rating mb-1">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi <?= ($i <= $row['rating']) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                  <?php endfor; ?>
                  <span class="small text-muted">(0)</span>
                </div>
                <div>
                  <?php if ($row['old_price'] > $row['price']): ?>
                    <span class="price-old">৳<?= $row['old_price'] ?></span>
                  <?php endif; ?>
                  <span class="price-new ms-1">৳<?= $row['price'] ?></span>
                </div>
                <button class="add-btn">ADD</button>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </main>

  <!-- 🔹 Footer -->
  <footer class="border-top bg-light">
    <div class="container py-5">
      <div class="row g-4">
        <div class="col-md-3">
          <div class="fw-bold fs-5">FASHN</div>
          <p class="text-muted small mt-2">Premium basics, responsibly made in Bangladesh.</p>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold mb-2">Shop</div>
          <ul class="list-unstyled text-muted small">
            <li>Women</li>
            <li>Men</li>
            <li>Accessories</li>
            <li>Sale</li>
          </ul>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold mb-2">Help</div>
          <ul class="list-unstyled text-muted small">
            <li>Shipping</li>
            <li>Returns</li>
            <li>Payments</li>
            <li>Size Guide</li>
          </ul>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold mb-2">Follow us</div>
          <div class="d-flex gap-3 text-muted">
            <i class="bi bi-instagram"></i><i class="bi bi-facebook"></i><i class="bi bi-youtube"></i>
          </div>
        </div>
      </div>
      <div class="text-center text-muted small border-top pt-3 mt-4">
        <?= htmlspecialchars($footer_text) ?>
      </div>
    </div>
  </footer>

  <!-- 🔹 Cart Drawer -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="cartDrawer">
    <div class="offcanvas-header">
      <h5>Your cart</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <div id="cartItems" class="vstack gap-3 small"></div>
      <div class="mt-3 border-top pt-3">
        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="cartSubtotal">৳0</strong>
        </div>
        <button class="btn btn-dark w-100">Checkout</button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function bdt(n) { return '৳' + new Intl.NumberFormat('en-BD').format(n); }
    const cartKey = 'fashn_cart'; function getCart() { return JSON.parse(localStorage.getItem(cartKey) || '[]'); }
    function saveCart(c) { localStorage.setItem(cartKey, JSON.stringify(c)); renderCart(); }
    function addToCart(i) { let c = getCart(); let e = c.find(x => x.id === i.id); if (e) e.qty++; else c.push({ ...i, qty: 1 }); saveCart(c); }
    function removeFromCart(id) { saveCart(getCart().filter(i => i.id !== id)); }
    function subtotal() { return getCart().reduce((s, i) => s + i.price * i.qty, 0); }
    function renderCart() {
      let c = getCart(), ci = document.getElementById('cartItems'); ci.innerHTML = '';
      if (!c.length) { ci.innerHTML = '<p class="text-muted">Your cart is empty.</p>'; }
      c.forEach(i => {
        let row = document.createElement('div'); row.className = 'd-flex align-items-center gap-2';
        row.innerHTML = `<img src="${i.img}" style="width:60px;height:60px;object-fit:cover;" class="rounded">
<div class="flex-grow-1">
<div class="fw-medium">${i.name}</div>
<div class="small">${bdt(i.price)} × ${i.qty}</div>
</div>
<button class="btn btn-link text-danger p-0">Remove</button>`;
        row.querySelector('button').onclick = () => removeFromCart(i.id);
        ci.appendChild(row);
      });
      document.getElementById('cartSubtotal').textContent = bdt(subtotal());
      document.getElementById('cartCount').textContent = getCart().reduce((s, i) => s + i.qty, 0);
    }
    renderCart();
  </script>
</body>

</html>