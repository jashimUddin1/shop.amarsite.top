












































<?php //index.php 
// Single-file previewable PHP + Bootstrap demo (drop-in index.php)
$products = [
  ["id"=>1, "name"=>"Oversized Tee — Cloud White", "price"=>1490, "rating"=>4.7, "reviews"=>128, "img"=>"https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=1600&auto=format&fit=crop", "colors"=>["White","Black","Cream"], "sizes"=>["S","M","L","XL"], "tag"=>"New", "category"=>"Tops"],
  ["id"=>2, "name"=>"Relaxed Denim Jacket", "price"=>4490, "rating"=>4.5, "reviews"=>76, "img"=>"https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?q=80&w=1600&auto=format&fit=crop", "colors"=>["Indigo","Black"], "sizes"=>["S","M","L"], "tag"=>"Bestseller", "category"=>"Outerwear"],
  ["id"=>3, "name"=>"Linen Shirt — Sand", "price"=>2990, "rating"=>4.3, "reviews"=>53, "img"=>"https://images.unsplash.com/photo-1512436991641-6745cdb1723f?q=80&w=1600&auto=format&fit=crop", "colors"=>["Sand","Olive","Navy"], "sizes"=>["S","M","L","XL"], "tag"=>"-20%", "category"=>"Tops"],
  ["id"=>4, "name"=>"Wide-Leg Trousers", "price"=>3590, "rating"=>4.6, "reviews"=>41, "img"=>"https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=1600&auto=format&fit=crop", "colors"=>["Charcoal","Khaki"], "sizes"=>["28","30","32","34"], "tag"=>"New", "category"=>"Bottoms"],
  ["id"=>5, "name"=>"Ribbed Knit Dress", "price"=>3990, "rating"=>4.8, "reviews"=>210, "img"=>"https://images.unsplash.com/photo-1520975916090-3105956dac38?q=80&w=1600&auto=format&fit=crop", "colors"=>["Black","Forest"], "sizes"=>["XS","S","M","L"], "tag"=>"-15%", "category"=>"Dresses"],
  ["id"=>6, "name"=>"Athletic Hoodie", "price"=>3290, "rating"=>4.2, "reviews"=>88, "img"=>"https://images.unsplash.com/photo-1516826957135-700dedea698c?q=80&w=1600&auto=format&fit=crop", "colors"=>["Grey","Navy"], "sizes"=>["S","M","L","XL"], "tag"=>"Classic", "category"=>"Active"],
];
$categories = ["Tops","Bottoms","Dresses","Outerwear","Active","Accessories"];
function bdt($n){ return '৳'.number_format($n,0,'.',','); }
$q = isset($_GET['q']) ? trim(strtolower($_GET['q'])) : '';
$category = $_GET['category'] ?? '';
$items = array_filter($products, function($p) use($q,$category){
  if($q && strpos(strtolower($p['name']), $q) === false) return false;
  if($category && $p['category'] !== $category) return false;
  return true;
});
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FASHN BD — Demo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .product-card img{ transition: transform .35s ease; }
    .product-card:hover img{ transform: scale(1.05); }
    .sticky-top-blur { backdrop-filter: blur(6px); background: rgba(255,255,255,.85); }
    .hero { background: linear-gradient(180deg,#fafafa 0%,#fff 100%); }
  </style>
</head>
<body class="bg-white text-dark">
  <!-- Top bar -->
  <div class="bg-dark text-white small py-2">
    <div class="container d-flex justify-content-between align-items-center">
      <div><i class="bi bi-badge-percent me-2"></i>Mega Sale: Buy 2 get 1 free</div>
      <div class="d-none d-md-flex gap-4 opacity-75">
        <div><i class="bi bi-truck me-2"></i>Free delivery over BDT 3,500</div>
        <div><i class="bi bi-shield-check me-2"></i>7-day easy returns</div>
      </div>
    </div>
  </div>

  <!-- Header -->
  <nav class="sticky-top sticky-top-blur border-bottom">
    <div class="container py-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <a href="/" class="text-decoration-none text-dark fw-bold fs-4">FASHN</a>
        <span class="badge text-bg-secondary rounded-pill">BD</span>
      </div>
      <form class="d-none d-md-flex w-50" role="search" method="get">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Search tees, dresses, denim…">
        </div>
      </form>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary"><i class="bi bi-heart"></i></button>
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer">
          <i class="bi bi-bag"></i>
          <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
        </button>
      </div>
    </div>
    <div class="container d-flex gap-2 pb-3">
      <?php foreach($categories as $c): ?>
        <a class="btn btn-outline-secondary btn-sm rounded-pill" href="?category=<?=urlencode($c)?>"><?=htmlspecialchars($c)?></a>
      <?php endforeach; ?>
      <a class="btn btn-link btn-sm" href="/">All</a>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero py-5">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-md-6">
          <span class="badge rounded-pill text-bg-light border mb-3"><span class="badge rounded-pill bg-success me-2">&nbsp;</span> New drop live now</span>
          <h1 class="fw-bold display-5">Minimal streetwear for everyday comfort.</h1>
          <p class="text-muted">Build a clean, timeless wardrobe with premium basics and statement fits. Ethically made. Designed in Dhaka.</p>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="#grid" class="btn btn-dark rounded-pill px-4 py-2">Shop new arrivals</a>
            <a href="?category=Dresses" class="btn btn-outline-secondary rounded-pill px-4 py-2">Women</a>
            <a href="?category=Tops" class="btn btn-link rounded-pill px-4 py-2">Men</a>
          </div>
        </div>
        <div class="col-md-6">
          <div class="ratio ratio-4x5 rounded-4 overflow-hidden shadow">
            <img src="https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?q=80&w=1600&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="Hero">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Product grid -->
  <main id="grid" class="py-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-end mb-3">
        <h2 class="h3 fw-bold">Featured</h2>
        <div class="text-muted small"><?=count($items)?> items</div>
      </div>
      <div class="row g-4">
        <?php foreach($items as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card border-0 shadow-sm product-card h-100">
            <div class="position-relative ratio ratio-4x5">
              <img src="<?=htmlspecialchars($p['img'])?>" class="card-img-top object-fit-cover" alt="<?=htmlspecialchars($p['name'])?>">
              <?php if(!empty($p['tag'])): ?>
                <span class="badge text-bg-dark position-absolute top-0 start-0 m-2 rounded-pill"><?=htmlspecialchars($p['tag'])?></span>
              <?php endif; ?>
              <button class="btn btn-light position-absolute top-0 end-0 m-2 rounded-circle"><i class="bi bi-heart"></i></button>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between gap-2">
                <div>
                  <div class="fw-medium" title="<?=htmlspecialchars($p['name'])?>"><?=htmlspecialchars($p['name'])?></div>
                  <div class="small text-muted"><i class="bi bi-star-fill me-1"></i><?=$p['rating']?> <span class="opacity-75">(<?=$p['reviews']?>)</span></div>
                </div>
                <div class="fw-semibold"><?=bdt($p['price'])?></div>
              </div>
              <div class="d-flex gap-2 mt-3">
                <button class="btn btn-dark flex-grow-1" data-bs-toggle="modal" data-bs-target="#quickView" data-product='<?=json_encode($p, JSON_HEX_APOS | JSON_HEX_TAG)?>'>Quick view</button>
                <button class="btn btn-outline-secondary" onclick='addToCart(<?=json_encode(["id"=>$p["id"],"name"=>$p["name"],"price"=>$p["price"],"img"=>$p["img"]], JSON_HEX_APOS | JSON_HEX_TAG)?>)'>Add</button>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="border-top bg-light">
    <div class="container py-5">
      <div class="row g-4">
        <div class="col-md-3">
          <div class="fw-bold fs-5">FASHN</div>
          <p class="text-muted small mt-2">Premium basics, responsibly made in Bangladesh. Questions? support@fashn.example</p>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold mb-2">Shop</div>
          <ul class="list-unstyled text-muted small">
            <li>Women</li><li>Men</li><li>Accessories</li><li>Sale</li>
          </ul>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold mb-2">Help</div>
          <ul class="list-unstyled text-muted small">
            <li>Shipping</li><li>Returns</li><li>Payments</li><li>Size Guide</li>
          </ul>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold mb-2">Follow us</div>
          <div class="d-flex gap-3 text-muted">
            <i class="bi bi-instagram"></i>
            <i class="bi bi-facebook"></i>
            <i class="bi bi-youtube"></i>
          </div>
        </div>
      </div>
      <div class="text-center text-muted small border-top pt-3 mt-4">© <?=date('Y')?> FASHN BD — All rights reserved.</div>
    </div>
  </footer>

  <!-- Cart offcanvas -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="cartDrawer">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Your cart</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <div id="cartItems" class="vstack gap-3 small"></div>
      <div class="mt-3 border-top pt-3">
        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="cartSubtotal">৳0</strong></div>
        <button class="btn btn-dark w-100">Checkout</button>
        <p class="text-muted small mt-2">Taxes and shipping calculated at checkout.</p>
      </div>
    </div>
  </div>

  <!-- Quick View Modal -->
  <div class="modal fade" id="quickView" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body">
          <div class="row g-4 align-items-start">
            <div class="col-md-6">
              <img id="qvImg" src="" class="w-100 rounded-3" alt="">
            </div>
            <div class="col-md-6">
              <h5 id="qvName" class="mb-1"></h5>
              <div id="qvPrice" class="mb-2 fw-semibold"></div>
              <div class="text-muted small mb-3"><i class="bi bi-star-fill me-1"></i><span id="qvRating"></span> • <span id="qvReviews"></span> reviews</div>
              <div id="qvSizes" class="mb-3"></div>
              <div id="qvColors" class="mb-3"></div>
              <div class="d-flex gap-2">
                <button id="qvAddBtn" class="btn btn-dark flex-grow-1"><i class="bi bi-bag me-2"></i>Add to cart</button>
                <button class="btn btn-outline-secondary">Wishlist</button>
              </div>
              <ul class="small text-muted mt-3 mb-0">
                <li>200 GSM cotton / breathable</li>
                <li>Made in Bangladesh</li>
                <li>Free delivery over BDT 3,500</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // --- Simple cart with localStorage ---
    const cartKey = 'fashn_cart_v1';
    const cartItemsEl = document.getElementById('cartItems');
    const cartSubtotalEl = document.getElementById('cartSubtotal');
    const cartCountEl = document.getElementById('cartCount');

    function getCart(){ return JSON.parse(localStorage.getItem(cartKey) || '[]'); }
    function saveCart(items){ localStorage.setItem(cartKey, JSON.stringify(items)); renderCart(); }
    function addToCart(item){
      const cart = getCart();
      const key = `${item.id}-${item.size||''}-${item.color||''}`;
      const existing = cart.find(i => i.key === key);
      if(existing){ existing.qty += 1; }
      else { cart.push({...item, key, qty:1}); }
      saveCart(cart);
    }
    function removeFromCart(key){ saveCart(getCart().filter(i => i.key !== key)); }
    function subtotal(){ return getCart().reduce((s,i)=> s + i.price*i.qty, 0); }
    function bdt(n){ return '৳' + new Intl.NumberFormat('en-BD',{maximumFractionDigits:0}).format(n); }
    function renderCart(){
      const cart = getCart();
      if(cartItemsEl){
        cartItemsEl.innerHTML = cart.length ? '' : '<p class="text-muted">Your cart is empty.</p>';
        cart.forEach(i=>{
          const row = document.createElement('div');
          row.className = 'd-flex align-items-center gap-2';
          row.innerHTML = `
            <img src="${i.img}" class="rounded-3" style="width:64px;height:64px;object-fit:cover;" alt="">
            <div class="flex-grow-1">
              <div class="fw-medium">${i.name}</div>
              <div class="text-muted small">${i.color||''} ${i.size? '• '+i.size:''}</div>
              <div class="small">${bdt(i.price)} × ${i.qty}</div>
            </div>
            <button class="btn btn-link text-danger p-0">Remove</button>`;
          row.querySelector('button').addEventListener('click',()=>removeFromCart(i.key));
          cartItemsEl.appendChild(row);
        });
      }
      if(cartSubtotalEl) cartSubtotalEl.textContent = bdt(subtotal());
      if(cartCountEl) cartCountEl.textContent = getCart().reduce((s,i)=>s+i.qty,0);
    }
    renderCart();

    // --- Quick View modal population ---
    const quickView = document.getElementById('quickView');
    quickView.addEventListener('show.bs.modal', evt => {
      const btn = evt.relatedTarget; // button with data-product
      const p = JSON.parse(btn.getAttribute('data-product'));
      document.getElementById('qvImg').src = p.img;
      document.getElementById('qvName').textContent = p.name;
      document.getElementById('qvPrice').textContent = bdt(p.price);
      document.getElementById('qvRating').textContent = p.rating;
      document.getElementById('qvReviews').textContent = p.reviews;

      // Sizes
      const sizes = (p.sizes||[]).map(s=>`<button class='btn btn-outline-secondary btn-sm rounded-pill me-2 mb-2' data-size='${s}'>${s}</button>`).join('');
      document.getElementById('qvSizes').innerHTML = sizes ? `<div class='small fw-semibold mb-1'>Size</div>${sizes}` : '';
      // Colors
      const colors = (p.colors||[]).map(c=>`<button class='btn btn-outline-secondary btn-sm rounded-pill me-2 mb-2' data-color='${c}'>${c}</button>`).join('');
      document.getElementById('qvColors').innerHTML = colors ? `<div class='small fw-semibold mb-1'>Color</div>${colors}` : '';

      let selected = {size: p.sizes?.[0]||null, color: p.colors?.[0]||null};
      document.querySelectorAll('#qvSizes [data-size]').forEach(b=>{
        if(b.getAttribute('data-size')===selected.size) b.classList.replace('btn-outline-secondary','btn-dark');
        b.addEventListener('click',()=>{
          document.querySelectorAll('#qvSizes [data-size]').forEach(x=>x.classList.add('btn-outline-secondary')); 
          document.querySelectorAll('#qvSizes [data-size]').forEach(x=>x.classList.remove('btn-dark'));
          b.classList.remove('btn-outline-secondary'); b.classList.add('btn-dark');
          selected.size = b.getAttribute('data-size');
        });
      });
      document.querySelectorAll('#qvColors [data-color]').forEach(b=>{
        if(b.getAttribute('data-color')===selected.color) b.classList.replace('btn-outline-secondary','btn-dark');
        b.addEventListener('click',()=>{
          document.querySelectorAll('#qvColors [data-color]').forEach(x=>x.classList.add('btn-outline-secondary'));
          document.querySelectorAll('#qvColors [data-color]').forEach(x=>x.classList.remove('btn-dark'));
          b.classList.remove('btn-outline-secondary'); b.classList.add('btn-dark');
          selected.color = b.getAttribute('data-color');
        });
      });

      const addBtn = document.getElementById('qvAddBtn');
      addBtn.onclick = () => addToCart({ id:p.id, name:p.name, price:p.price, img:p.img, ...selected });
    });
  </script>
</body>
</html>




site setting

header  = header ar sob element and data change kora jabe. 
nav = sob element and data change kora jabe. 
slider = sob element and data change kora jabe. 
banner = sob element and data change kora jabe. 
product-card =  koyta product-card thakbe and tar design kemon hobe sob change kora jabe.
footer-nav = sob element and data change kora jabe. 
footer = sob element and data change kora jabe. 