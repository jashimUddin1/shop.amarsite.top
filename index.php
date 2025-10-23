<?php
require_once __DIR__ . "/db/dbcon.php";
session_start();

/** Helpers */
if (!function_exists('esc')) { function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
function get_section_map(mysqli $con): array {
  $map = [];
  if ($res = $con->query("SELECT section, data FROM site_sections")) {
    while ($row = $res->fetch_assoc()) $map[$row['section']] = json_decode($row['data'] ?? "{}", true) ?: [];
  }
  return $map;
}
function getv($arr, $path, $default=''){
  $v = $arr; foreach (explode('.', $path) as $seg) { if (!is_array($v) || !array_key_exists($seg,$v)) return $default; $v = $v[$seg]; }
  return is_scalar($v) ? $v : $default;
}
function col($row, $names, $def=''){ foreach((array)$names as $n){ if(isset($row[$n]) && $row[$n] !== '') return $row[$n]; } return $def; }

/** Sections */
$sections   = get_section_map($con);
$topbar      = $sections['topbar']       ?? [];
$header      = $sections['header']       ?? [];
$nav         = $sections['nav']          ?? [];
$slider      = $sections['slider']       ?? [];
$banner      = $sections['banner']       ?? [];
$productCard = $sections['product_card'] ?? [];
$footerNav   = $sections['footer_nav']   ?? [];
$footer      = $sections['footer']       ?? [];

/** Normalize nav menu */
$menu = $nav['menu'] ?? [];
if (is_string($menu)) $menu = array_filter(array_map('trim', explode(',', $menu)));
if (!is_array($menu)) $menu = [];

/** Product options */
$prod_count  = (int)($productCard['count'] ?? 8); if ($prod_count < 1) $prod_count = 8;
$prod_layout = $productCard['layout'] ?? 'grid';
$prod_style  = $productCard['style']  ?? 'classic';

/** Load products */
$products = [];
if ($stmt = $con->prepare("SELECT * FROM products ORDER BY id DESC LIMIT ?")) {
  $stmt->bind_param("i", $prod_count); $stmt->execute();
  $res = $stmt->get_result(); while ($r = $res->fetch_assoc()) $products[] = $r; $stmt->close();
} else {
  if ($res = $con->query("SELECT * FROM product ORDER BY id DESC LIMIT ".(int)$prod_count)) {
    while ($r = $res->fetch_assoc()) $products[] = $r;
  }
}
function product_card_data(array $row): array {
  $id    = col($row, ['id','product_id','pid'], 0);
  $name  = col($row, ['name','title','product_name'], 'Product');
  $price = col($row, ['price','sale_price','current_price','mrp'], '');
  $img   = col($row, ['image','thumbnail','thumb','photo','img','picture','image_url'], '');
  $slug  = col($row, ['slug','handle'], '');
  $href  = $slug ? "product.php?slug=".rawurlencode($slug) : "product.php?id=".urlencode((string)$id);
  return compact('id','name','price','img','href');
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc(getv($header,'title','FASHN BD')) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body{background:#fafafa;}
  .site-navbar{border-bottom:1px solid #eee;background:#fff;}
  .brand{font-weight:700;letter-spacing:.5px;}
  .pcard.classic .card{border:1px solid #eee}
  .pcard.minimal .card{border:none}
  .pcard.shadow  .card{border:none; box-shadow: 0 6px 20px rgba(0,0,0,.06)}
  .pcard.bordered .card{border:1px solid #ddd}
  .pcard .card img{object-fit:cover;height:220px}
  .footer{background:#111;color:#ddd}
  .footer a{color:#aaa;text-decoration:none}
  .footer a:hover{color:#fff}
</style>
</head>
<body>

<!-- ======== TOPBAR ======== -->
<?php
$tbEnabled = !empty($topbar['enabled']);
$tbBg    = $topbar['bg']    ?? '#111111';
$tbColor = $topbar['color'] ?? '#ffffff';
$tbPy    = (int)($topbar['py'] ?? 6);
$leftTx  = $topbar['left']['text'] ?? ($topbar['text'] ?? '');
$leftLn  = $topbar['left']['link'] ?? ($topbar['link'] ?? '');
$right   = $topbar['right'] ?? [];
if (!$right) {
  $right = [
    ['icon'=>'bi-truck','text'=>'Free delivery over BDT 3,500','link'=>'','color'=>$tbColor,'opacity'=>0.9],
    ['icon'=>'bi-arrow-repeat','text'=>'7 day easy returns','link'=>'','color'=>$tbColor,'opacity'=>0.8],
  ];
}
if ($tbEnabled && $leftTx !== ''):
?>
  <div class="topbar" style="background:<?=esc($tbBg)?>; color:<?=esc($tbColor)?>;">
    <div class="container d-flex align-items-center justify-content-between" style="padding-top:<?=$tbPy?>px;padding-bottom:<?=$tbPy?>px;">
      <!-- Left text -->
      <div class="small">
        <?php if ($leftLn): ?>
          <a href="<?=esc($leftLn)?>" class="text-decoration-none" style="color:<?=esc($tbColor)?>;"><?=esc($leftTx)?></a>
        <?php else: ?>
          <span><?=esc($leftTx)?></span>
        <?php endif; ?>
      </div>

      <!-- Right badges (each with its own color + opacity) -->
      <div class="d-flex align-items-center gap-4 small">
        <?php foreach ($right as $item):
          $icon    = trim($item['icon'] ?? '');
          $text    = trim($item['text'] ?? '');
          $link    = trim($item['link'] ?? '');
          $color   = trim($item['color'] ?? $tbColor);
          $opacity = is_numeric($item['opacity'] ?? null) ? (float)$item['opacity'] : 1;
          if ($opacity < 0.1) $opacity = 0.1; if ($opacity > 1) $opacity = 1;
          if ($text === '') continue;
        ?>
          <?php if ($link): ?>
            <a href="<?=esc($link)?>" class="d-inline-flex align-items-center gap-2 text-decoration-none"
               style="color:<?=esc($color)?>;opacity:<?=esc($opacity)?>;">
              <?php if ($icon): ?><i class="bi <?=esc($icon)?>"></i><?php endif; ?>
              <span><?=esc($text)?></span>
            </a>
          <?php else: ?>
            <span class="d-inline-flex align-items-center gap-2"
                  style="color:<?=esc($color)?>;opacity:<?=esc($opacity)?>;">
              <?php if ($icon): ?><i class="bi <?=esc($icon)?>"></i><?php endif; ?>
              <span><?=esc($text)?></span>
            </span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ======== NAVBAR / HEADER ======== -->
<nav class="navbar navbar-expand-lg site-navbar">
  <div class="container">
    <a class="navbar-brand brand d-flex align-items-center gap-2" href="index.php">
      <?php if(!empty(getv($header,'logo'))): ?>
        <img src="<?=esc(getv($header,'logo'))?>" alt="logo" style="height:36px">
      <?php endif; ?>
      <span><?= esc(getv($header,'title','FASHN BD')) ?></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="mainNav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <?php if ($menu): foreach ($menu as $item): ?>
          <li class="nav-item"><a class="nav-link" href="#"><?=esc($item)?></a></li>
        <?php endforeach; else: ?>
          <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="#">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- ======== SLIDER ======== -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <h1 class="display-6 mb-3"><?= esc(getv($slider,'title','Fresh Arrivals')) ?></h1>
        <p class="lead"><?= esc(getv($slider,'subtitle','Discover the latest trends and deals.')) ?></p>
        <?php if (getv($slider,'button_url') || getv($slider,'button_text')): ?>
          <a href="<?= esc(getv($slider,'button_url','#')) ?>" class="btn btn-dark mt-2">
            <?= esc(getv($slider,'button_text','Shop Now')) ?>
          </a>
        <?php endif; ?>
      </div>
      <div class="col-lg-6 text-center">
        <?php if (getv($slider,'image')): ?>
          <img src="<?= esc(getv($slider,'image')) ?>" class="img-fluid rounded" alt="slider">
        <?php else: ?>
          <div class="bg-secondary rounded" style="height:320px"></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ======== BANNER ======== -->
<?php if (getv($banner,'image')): ?>
<section class="py-4">
  <div class="container">
    <a href="<?= esc(getv($banner,'link','#')) ?>" class="d-block position-relative">
      <img class="img-fluid rounded w-100" src="<?= esc(getv($banner,'image')) ?>" alt="banner">
      <?php if (getv($banner,'text')): ?>
      <span class="position-absolute bottom-0 start-0 m-3 px-3 py-2 bg-dark text-white rounded">
        <?= esc(getv($banner,'text')) ?>
      </span>
      <?php endif; ?>
    </a>
  </div>
</section>
<?php endif; ?>

<!-- ======== PRODUCTS ======== -->
<section class="py-5 pcard <?= esc($prod_style) ?>">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h3 class="mb-0">Featured Products</h3>
      <a href="product.php" class="btn btn-outline-dark btn-sm">View All</a>
    </div>

    <?php if ($prod_layout === 'carousel'): ?>
      <?php $chunks = array_chunk($products, 4); $carouselId = "prodCarousel"; ?>
      <div id="<?= $carouselId ?>" class="carousel slide" data-bs-ride="false">
        <div class="carousel-inner">
          <?php foreach ($chunks as $i => $group): ?>
          <div class="carousel-item <?= $i===0?'active':'' ?>">
            <div class="row g-3">
              <?php foreach ($group as $row): $p = product_card_data($row); ?>
              <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100">
                  <?php if ($p['img']): ?>
                    <img src="<?=esc($p['img'])?>" class="card-img-top" alt="<?=esc($p['name'])?>">
                  <?php else: ?>
                    <div class="bg-light" style="height:220px"></div>
                  <?php endif; ?>
                  <div class="card-body">
                    <h6 class="card-title text-truncate mb-1"><?=esc($p['name'])?></h6>
                    <?php if ($p['price']!==''): ?><div class="fw-semibold mb-2">৳ <?=esc($p['price'])?></div><?php endif; ?>
                    <a href="<?=esc($p['href'])?>" class="btn btn-sm btn-dark">View</a>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if (count($chunks) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($products as $row): $p = product_card_data($row); ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="card h-100">
            <?php if ($p['img']): ?>
              <img src="<?=esc($p['img'])?>" class="card-img-top" alt="<?=esc($p['name'])?>">
            <?php else: ?>
              <div class="bg-light" style="height:220px"></div>
            <?php endif; ?>
            <div class="card-body">
              <h6 class="card-title text-truncate mb-1"><?=esc($p['name'])?></h6>
              <?php if ($p['price']!==''): ?><div class="fw-semibold mb-2">৳ <?=esc($p['price'])?></div><?php endif; ?>
              <a href="<?=esc($p['href'])?>" class="btn btn-sm btn-dark">View</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ======== FOOTER NAV ======== -->
<?php
$col1 = $footerNav['col1'] ?? []; $col2 = $footerNav['col2'] ?? [];
if (is_string($col1)) $col1 = array_filter(array_map('trim', explode(',', $col1)));
if (is_string($col2)) $col2 = array_filter(array_map('trim', explode(',', $col2)));
?>
<section class="py-5 bg-white border-top">
  <div class="container">
    <div class="row">
      <div class="col-md-6 mb-3">
        <h6 class="fw-bold">Links</h6>
        <ul class="list-unstyled">
          <?php if ($col1): foreach($col1 as $it): ?>
            <li><a href="#" class="link-dark text-decoration-none"><?=esc($it)?></a></li>
          <?php endforeach; else: ?>
            <li><a href="#" class="link-dark text-decoration-none">Shipping</a></li>
            <li><a href="#" class="link-dark text-decoration-none">Returns</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-md-6 mb-3">
        <h6 class="fw-bold">Support</h6>
        <ul class="list-unstyled">
          <?php if ($col2): foreach($col2 as $it): ?>
            <li><a href="#" class="link-dark text-decoration-none"><?=esc($it)?></a></li>
          <?php endforeach; else: ?>
            <li><a href="#" class="link-dark text-decoration-none">FAQ</a></li>
            <li><a href="#" class="link-dark text-decoration-none">Contact</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ======== FOOTER ======== -->
<footer class="footer py-4 mt-4">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
    <div class="small">
      <?= esc(getv($footer,'text','© '.date('Y').' FASHN BD. All rights reserved.')) ?>
    </div>
    <div class="d-flex align-items-center gap-3">
      <?php if (getv($footer,'social.facebook')): ?>
        <a href="<?=esc(getv($footer,'social.facebook'))?>" target="_blank"><i class="bi bi-facebook"></i></a>
      <?php endif; ?>
      <?php if (getv($footer,'social.instagram')): ?>
        <a href="<?=esc(getv($footer,'social.instagram'))?>" target="_blank"><i class="bi bi-instagram"></i></a>
      <?php endif; ?>
      <?php if (getv($footer,'social.youtube')): ?>
        <a href="<?=esc(getv($footer,'social.youtube'))?>" target="_blank"><i class="bi bi-youtube"></i></a>
      <?php endif; ?>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
