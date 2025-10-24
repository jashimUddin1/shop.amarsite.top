<?php //index.php main file frontend
require_once __DIR__ . "/db/dbcon.php";
session_start();

/** Helpers */
if (!function_exists('esc')) {
  function esc($v)
  {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
  }
}
function get_section_map(mysqli $con): array
{
  $map = [];
  if ($res = $con->query("SELECT section, data FROM site_sections")) {
    while ($row = $res->fetch_assoc())
      $map[$row['section']] = json_decode($row['data'] ?? "{}", true) ?: [];
  }
  return $map;
}
function getv($arr, $path, $default = '')
{
  $v = $arr;
  foreach (explode('.', $path) as $seg) {
    if (!is_array($v) || !array_key_exists($seg, $v))
      return $default;
    $v = $v[$seg];
  }
  return is_scalar($v) || is_null($v) ? $v : $default;
}
function col($row, $names, $def = '')
{
  foreach ((array) $names as $n) {
    if (isset($row[$n]) && $row[$n] !== '')
      return $row[$n];
  }
  return $def;
}

/** Sections */
$sections = get_section_map($con);
$topbar = $sections['topbar'] ?? [];
$header = $sections['header'] ?? [];
$nav = $sections['nav'] ?? [];
$hero = $sections['hero'] ?? [];
$slider = $sections['slider'] ?? [];
$banner = $sections['banner'] ?? [];
$productCard = $sections['product_card'] ?? [];
$footerNav = $sections['footer_nav'] ?? [];
$footer = $sections['footer'] ?? [];

/** Normalize nav menu */
$menu = $nav['menu'] ?? [];
if (is_string($menu))
  $menu = array_filter(array_map('trim', explode(',', $menu)));
if (!is_array($menu))
  $menu = [];

/** Product options */
$prod_enabled = !empty($productCard['enabled']);
$prod_count = (int) ($productCard['count'] ?? 8);
if ($prod_count < 1)
  $prod_count = 8;
$prod_layout = $productCard['layout'] ?? 'grid';
$prod_style = $productCard['style'] ?? 'classic';

/** Load products if section enabled */
$products = [];
if ($prod_enabled) {
  if ($stmt = $con->prepare("SELECT * FROM products ORDER BY id DESC LIMIT ?")) {
    $stmt->bind_param("i", $prod_count);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc())
      $products[] = $r;
    $stmt->close();
  } else {
    if ($res = $con->query("SELECT * FROM product ORDER BY id DESC LIMIT " . (int) $prod_count)) {
      while ($r = $res->fetch_assoc())
        $products[] = $r;
    }
  }
}

function product_card_data(array $row): array
{
  $id = col($row, ['id', 'product_id', 'pid'], 0);
  $name = col($row, ['name', 'title', 'product_name'], 'Product');
  $price = col($row, ['price', 'sale_price', 'current_price', 'mrp'], '');
  $img = col($row, ['image', 'thumbnail', 'thumb', 'photo', 'img', 'picture', 'image_url'], '');
  $slug = col($row, ['slug', 'handle'], '');
  $href = $slug ? "product.php?slug=" . rawurlencode($slug) : "product.php?id=" . urlencode((string) $id);
  return compact('id', 'name', 'price', 'img', 'href');
}
?><!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc(getv($header, 'title', 'FASHN BD')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: #fafafa;
    }

    .site-navbar {
      border-bottom: 1px solid #eee;
      background: #fff;
    }

    .brand {
      font-weight: 700;
      letter-spacing: .5px;
    }

    .pcard.classic .card {
      border: 1px solid #eee
    }

    .pcard.minimal .card {
      border: none
    }

    .pcard.shadow .card {
      border: none;
      box-shadow: 0 6px 20px rgba(0, 0, 0, .06)
    }

    .pcard.bordered .card {
      border: 1px solid #ddd
    }

    .pcard .card img {
      object-fit: cover;
      height: 220px
    }

    .footer {
      background: #111;
      color: #ddd
    }

    .footer a {
      color: #aaa;
      text-decoration: none
    }

    .footer a:hover {
      color: #fff
    }
  </style>
</head>

<body>

  <!-- ======== TOPBAR ======== -->
  <?php
  $tbEnabled = !empty($topbar['enabled']);
  $tbBg = $topbar['bg'] ?? '#111111';
  $tbColor = $topbar['color'] ?? '#ffffff';
  $tbPy = (int) ($topbar['py'] ?? 6);
  $left = $topbar['left'] ?? [];
  $left_on = !empty($left['enabled']);
  $leftTx = $left['text'] ?? ($topbar['text'] ?? '');
  $leftLn = $left['link'] ?? ($topbar['link'] ?? '');
  $right = $topbar['right'] ?? [];
  if (!$right) {
    $right = [
      ['enabled' => 1, 'icon' => 'bi-truck', 'text' => 'Free delivery over BDT 3,500', 'link' => '', 'color' => $tbColor, 'opacity' => 0.9],
      ['enabled' => 1, 'icon' => 'bi-arrow-repeat', 'text' => '7 day easy returns', 'link' => '', 'color' => $tbColor, 'opacity' => 0.8],
    ];
  }
  if ($tbEnabled && ($left_on || array_filter($right, fn($r) => !empty($r['enabled']) && !empty($r['text'])))):
    ?>
    <div class="topbar" style="background:<?= esc($tbBg) ?>; color:<?= esc($tbColor) ?>;">
      <div class="container d-flex align-items-center justify-content-between"
        style="padding-top:<?= $tbPy ?>px;padding-bottom:<?= $tbPy ?>px;">

        <!-- Left text -->
        <div class="small">
          <?php if ($left_on && $leftTx !== ''): ?>
            <?php if ($leftLn): ?>
              <a href="<?= esc($leftLn) ?>" class="text-decoration-none" style="color:<?= esc($tbColor) ?>;"><?= esc($leftTx) ?></a>
            <?php else: ?>
              <span><?= esc($leftTx) ?></span>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <!-- Right badges -->
        <div class="d-flex align-items-center gap-4 small">
          <?php foreach ($right as $item):
            if (empty($item['enabled']))
              continue;
            $icon = trim($item['icon'] ?? '');
            $text = trim($item['text'] ?? '');
            $link = trim($item['link'] ?? '');
            $color = trim($item['color'] ?? $tbColor);
            $opacity = is_numeric($item['opacity'] ?? null) ? (float) $item['opacity'] : 1;
            if ($opacity < 0.1)
              $opacity = 0.1;
            if ($opacity > 1)
              $opacity = 1;
            if ($text === '')
              continue;
            ?>
            <?php if ($link): ?>
              <a href="<?= esc($link) ?>" class="d-inline-flex align-items-center gap-2 text-decoration-none"
                style="color:<?= esc($color) ?>;opacity:<?= esc($opacity) ?>;">
                <?php if ($icon): ?><i class="bi <?= esc($icon) ?>"></i><?php endif; ?>
                <span><?= esc($text) ?></span>
              </a>
            <?php else: ?>
              <span class="d-inline-flex align-items-center gap-2" style="color:<?= esc($color) ?>;opacity:<?= esc($opacity) ?>;">
                <?php if ($icon): ?><i class="bi <?= esc($icon) ?>"></i><?php endif; ?>
                <span><?= esc($text) ?></span>
              </span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>


  <!-- ======== HEADER BAR (Custom Grid) (final + logo fallback) ======== -->
  <?php
  $header_on = !empty($header['enabled']);
  $title_on = !empty($header['title_enabled']);
  $logo_on = !empty($header['logo_enabled']);
  $title2_on = !empty($header['title2_enabled']);
  $title2_max = (int) ($header['title2_max_ch'] ?? 24);
  if ($title2_max < 8)
    $title2_max = 8;
  if ($title2_max > 60)
    $title2_max = 60;

  /* কাউন্টার—তোমার অ্যাপ থেকে সেট করো */
  $wishlistCount = isset($wishlistCount) ? (int) $wishlistCount : 0;
  $cartCount = isset($cartCount) ? (int) $cartCount : 0;

  // for test
  $wishlistCount = 2;
  $cartCount = 3;
  ?>
  <?php if ($header_on): ?>
    <style>
      /* mobile container width */
      @media (max-width:576px) {
        .header-container {
          width: 95% !important;
        }
      }

      .header-bar {
        background: #fff;
        border-bottom: 1px solid #eee;
      }

      .h-left,
      .h-center,
      .h-right {
        display: flex;
        align-items: center;
        gap: .65rem;
      }

      .h-left {
        flex: 0 0 20%;
      }

      .h-center {
        flex: 1 1 50%;
      }

      .h-right {
        flex: 0 0 20%;
        justify-content: flex-end;
      }

      @media (max-width:576px) {
        .h-left {
          flex: 0 0 25%;
        }

        .h-center {
          flex: 0 0 50%;
        }

        .h-right {
          flex: 0 0 20%;
        }
      }

      /* left */
      .hamburger-btn {
        width: 44px;
        height: 44px;
        border-radius: .6rem;
        border: 0;
        background: #111;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
      }

      .hamburger-btn:focus {
        outline: none;
        box-shadow: none
      }

      .logo-circle {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        object-fit: cover
      }

      /* Fallback placeholder when no logo */
      .logo-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        color: #fff;
        font-weight: 700;
        text-transform: uppercase;
        font-size: .8rem;
        user-select: none;
      }

      .brand-title {
        font-weight: 800;
        letter-spacing: .5px;
        margin-bottom: 0;
        white-space: nowrap
      }

      .brand-title2 {
        display: inline-block;
        margin-left: .5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: calc(1ch * var(--t2ch, 24));
        font-size: .85rem;
        padding: .1rem .45rem;
        border-radius: .8rem;
        background: #e6e9ed;
        color: #555;
      }

      /* center (search — unchanged style) */
      .search-wrap {
        width: 100%;
      }

      .search-wrap .form-control {
        height: 44px;
      }

      /* right icons */
      .icon-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border: 1px solid #dcdcdc;
        border-radius: .6rem;
        background: #fff;
      }

      .icon-btn:hover {
        background: #f7f7f7
      }

      .icon-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 18px;
        height: 18px;
        padding: 0 .3rem;
        border-radius: 999px;
        background: #dc3545;
        color: #fff;
        font-size: .7rem;
        line-height: 18px;
        text-align: center;
      }

      /* offcanvas */
      @media (min-width:992px) {
        #offcanvasMenu {
          width: 420px;
        }
      }
    </style>

    <div class="header-bar py-2">
      <div class="container header-container">
        <div class="d-flex align-items-center" style="gap:1rem;">

          <!-- LEFT 20% -->
          <div class="h-left">
            <!-- Hamburger -->
            <button class="hamburger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu"
              aria-controls="offcanvasMenu" title="Menu"><span style="font-size:1.25rem">☰</span></button>


            <!-- Logo (image or fallback "img") -->
            <?php $logoUrl = trim((string) ($header['logo'] ?? '')); ?>
            <a href="index.php" class="d-inline-flex align-items-center"
              title="<?= $logoUrl ? 'Please upload image/picture' : 'Home' ?>" aria-label="Home"
              style="position:relative">

              <!-- Placeholder by default -->
              <div class="logo-placeholder" id="logoPh" style="display:flex;">img</div>

              <!-- Real image (hidden if empty or load fails) -->
              <img src="admin/<?= esc($logoUrl) ?>" alt="logo" class="logo-circle"
                style="display:<?= $logoUrl ? 'block' : 'none' ?>;"
                onload="this.style.display='block'; this.previousElementSibling.style.display='none';"
                onerror="this.style.display='none'; this.previousElementSibling.style.display='flex';">
            </a>


            <!-- Title + Title2 -->
            <div class="d-flex align-items-center">
              <?php if ($title_on): ?>
                <a href="index.php" class="text-decoration-none text-dark">
                  <h5 class="brand-title mb-0"><?= esc(getv($header, 'title', 'FASHN')) ?></h5>
                </a>
              <?php endif; ?>
              <?php if ($title2_on && getv($header, 'title2')): ?>
                <?php

$style = $header['title2_style'] ?? 'pill-gray';
$styleCss = [
  'pill-gray'   => 'background:#e6e9ed;color:#555;',
  'pill-dark'   => 'background:#111;color:#fff;',
  'pill-accent' => 'background:#0d6efd;color:#fff;',
  'outline'     => 'background:#fff;border:1px solid #ced4da;color:#333;',
  'circle'      => 'background:#fff;border:1px solid #ced4da;color:#333;border-radius:999px;',
];
$inline = $styleCss[$style] ?? $styleCss['pill-gray'];

 if (!empty($header['title2_enabled']) && !empty($header['title2'])): ?>
  <span class="brand-title2" style="<?= $inline ?>"><?= esc($header['title2']) ?></span>
<?php endif; ?>


              <?php endif; ?>
            </div>
          </div>

          <!-- Center 50% (search unchanged) -->
          <div class="h-center">
            <form class="search-wrap" action="search.php" method="get">
              <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search products, brands and more…">
                <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i></button>
              </div>
            </form>
          </div>

          <!-- Right 20% -->
          <div class="h-right">
            <!-- Wishlist: badge only if >0 -->
            <a href="wishlist.php" class="icon-btn" title="Wishlist">
              <i class="bi bi-heart"></i>
              <?php if ($wishlistCount > 0): ?><span class="icon-badge"><?= $wishlistCount ?></span><?php endif; ?>
            </a>

            <!-- Cart (as is) -->
            <a href="cart.php" class="icon-btn ms-2" title="Cart">
              <i class="bi bi-bag"></i>
              <?php if ($cartCount > 0): ?><span class="icon-badge"><?= $cartCount ?></span><?php endif; ?>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Offcanvas Menu (hamburger) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasMenuLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="list-unstyled">
          <li><a href="#" class="link-dark text-decoration-none d-block py-2"><i class="bi bi-house-door me-2"></i>
              Home</a></li>
          <li><a href="#" class="link-dark text-decoration-none d-block py-2"><i class="bi bi-stars me-2"></i> New
              Arrivals</a></li>

          <!-- Dropdown demo -->
          <li class="mt-2">
            <div class="dropdown">
              <a class="btn btn-outline-secondary dropdown-toggle w-100 text-start" href="#" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-grid-3x3-gap me-2"></i> Categories
              </a>
              <ul class="dropdown-menu w-100">
                <li><a class="dropdown-item" href="#">Men</a></li>
                <li><a class="dropdown-item" href="#">Women</a></li>
                <li><a class="dropdown-item" href="#">Kids</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li class="dropend">
                  <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">Accessories</a>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Bags</a></li>
                    <li><a class="dropdown-item" href="#">Belts</a></li>
                    <li><a class="dropdown-item" href="#">Sunglasses</a></li>
                  </ul>
                </li>
              </ul>
            </div>
          </li>

          <li class="mt-2"><a href="#" class="link-dark text-decoration-none d-block py-2"><i
                class="bi bi-percent me-2"></i> Offers</a></li>
          <li><a href="#" class="link-dark text-decoration-none d-block py-2"><i class="bi bi-telephone me-2"></i>
              Contact</a></li>
        </ul>
      </div>
    </div>
  <?php endif; ?>



  
  <!-- ========== HERO (between Nav and Slider) ========== -->
  <?php
  $hero_on = !empty($hero['enabled']);
  if ($hero_on):
    $badge_on = !empty($hero['badge_enabled']);
    $badge = trim((string)($hero['badge_text'] ?? ''));
    $title = trim((string)($hero['title'] ?? ''));
    $subtitle = trim((string)($hero['subtitle'] ?? ''));
    $btn_on = !empty($hero['button_enabled']);
    $btn_text = (string)($hero['button_text'] ?? 'Shop now');
    $btn_url  = (string)($hero['button_url']  ?? '#');
    $img_on = !empty($hero['image_enabled']) && !empty($hero['image']);
    $img    = (string)($hero['image'] ?? '');
  ?>
  <section class="py-4 py-md-5" style="background:#f8fafc;">
    <div class="container">
      <div class="row align-items-center g-4 g-lg-5">
        <div class="col-lg-7">
          <?php if ($badge_on && $badge !== ''): ?>
            <span class="d-inline-flex align-items-center gap-2 rounded-pill px-3 py-2 bg-white shadow-sm border">
              <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#16a34a;"></span>
              <strong class="small m-0"><?= esc($badge) ?></strong>
            </span>
          <?php endif; ?>

          <?php if ($title !== ''): ?>
            <h1 class="display-4 fw-bold mt-3 mb-3" style="letter-spacing:-.02em;"><?= esc($title) ?></h1>
          <?php endif; ?>

          <?php if ($subtitle !== ''): ?>
            <p class="fs-5 text-secondary mb-4" style="max-width:52ch;"><?= esc($subtitle) ?></p>
          <?php endif; ?>

          <?php if ($btn_on): ?>
            <a href="<?= esc($btn_url) ?>" class="btn btn-dark btn-lg rounded-pill px-4">
              <?= esc($btn_text) ?>
            </a>
          <?php endif; ?>
        </div>
        <div class="col-lg-5">
          
            <div class="ratio ratio-16x9 rounded-4 shadow-lg overflow-hidden bg-body">
              <img src="admin/<?= esc($img) ?>" alt="Hero image" class="w-100 h-100" style="object-fit:cover;">
            </div>

        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>


<!-- ======== SLIDER ======== -->
  <?php
  $slider_on = !empty($slider['enabled']);
  $sl_title_on = !empty($slider['title_enabled']);
  $sl_sub_on = !empty($slider['subtitle_enabled']);
  $sl_img_on = !empty($slider['image_enabled']);
  $sl_btn_on = !empty($slider['button_enabled']);
  ?>
  <?php if ($slider_on): ?>
    <section class="py-5 bg-light">
      <div class="container">
        <div class="row align-items-center g-4">
          <div class="col-lg-6">
            <?php if ($sl_title_on): ?>
              <h1 class="display-6 mb-3"><?= esc(getv($slider, 'title', 'Fresh Arrivals')) ?></h1>
            <?php endif; ?>
            <?php if ($sl_sub_on): ?>
              <p class="lead"><?= esc(getv($slider, 'subtitle', 'Discover the latest trends and deals.')) ?></p>
            <?php endif; ?>
            <?php if ($sl_btn_on && (getv($slider, 'button_url') || getv($slider, 'button_text'))): ?>
              <a href="<?= esc(getv($slider, 'button_url', '#')) ?>" class="btn btn-dark mt-2">
                <?= esc(getv($slider, 'button_text', 'Shop Now')) ?>
              </a>
            <?php endif; ?>
          </div>
          <div class="col-lg-6 text-center">
            <?php if ($sl_img_on && getv($slider, 'image')): ?>
              <img src="admin/<?= esc(getv($slider, 'image')) ?>" class="img-fluid rounded" alt="slider">
            <?php else: ?>
              <div class="bg-secondary rounded" style="height:320px"></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ======== BANNER ======== -->
  <?php
  $banner_on = !empty($banner['enabled']);
  $bn_img_on = !empty($banner['image_enabled']);
  $bn_txt_on = !empty($banner['text_enabled']);
  ?>
  <?php if ($banner_on && ($bn_img_on || $bn_txt_on)): ?>
    <section class="py-4">
      <div class="container">
        <a href="<?= esc(getv($banner, 'link', '#')) ?>" class="d-block position-relative">
          <?php if ($bn_img_on && getv($banner, 'image')): ?>
            <img class="img-fluid rounded w-100" src="admin/<?= esc(getv($banner, 'image')) ?>" alt="banner">
          <?php else: ?>
            <div class="bg-secondary rounded w-100" style="height:220px"></div>
          <?php endif; ?>
          <?php if ($bn_txt_on && getv($banner, 'text')): ?>
            <span class="position-absolute bottom-0 start-0 m-3 px-3 py-2 bg-dark text-white rounded">
              <?= esc(getv($banner, 'text')) ?>
            </span>
          <?php endif; ?>
        </a>
      </div>
    </section>
  <?php endif; ?>

  <!-- ======== PRODUCTS ======== -->
  <?php if ($prod_enabled): ?>
    <section class="py-5 pcard <?= esc($prod_style) ?>">
      <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h3 class="mb-0">Featured Products</h3>
          <a href="product.php" class="btn btn-outline-dark btn-sm">View All</a>
        </div>

        <?php if ($prod_layout === 'carousel'): ?>
          <?php $chunks = array_chunk($products, 4);
          $carouselId = "prodCarousel"; ?>
          <div id="<?= $carouselId ?>" class="carousel slide" data-bs-ride="false">
            <div class="carousel-inner">
              <?php foreach ($chunks as $i => $group): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                  <div class="row g-3">
                    <?php foreach ($group as $row):
                      $p = product_card_data($row); ?>
                      <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                          <?php if ($p['img']): ?>
                            <img src="<?= esc($p['img']) ?>" class="card-img-top" alt="<?= esc($p['name']) ?>">
                          <?php else: ?>
                            <div class="bg-light" style="height:220px"></div>
                          <?php endif; ?>
                          <div class="card-body">
                            <h6 class="card-title text-truncate mb-1"><?= esc($p['name']) ?></h6>
                            <?php if ($p['price'] !== ''): ?>
                              <div class="fw-semibold mb-2">৳ <?= esc($p['price']) ?></div><?php endif; ?>
                            <a href="<?= esc($p['href']) ?>" class="btn btn-sm btn-dark">View</a>
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
            <?php foreach ($products as $row):
              $p = product_card_data($row); ?>
              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100">
                  <?php if ($p['img']): ?>
                    <img src="<?= esc($p['img']) ?>" class="card-img-top" alt="<?= esc($p['name']) ?>">
                  <?php else: ?>
                    <div class="bg-light" style="height:220px"></div>
                  <?php endif; ?>
                  <div class="card-body">
                    <h6 class="card-title text-truncate mb-1"><?= esc($p['name']) ?></h6>
                    <?php if ($p['price'] !== ''): ?>
                      <div class="fw-semibold mb-2">৳ <?= esc($p['price']) ?></div><?php endif; ?>
                    <a href="<?= esc($p['href']) ?>" class="btn btn-sm btn-dark">View</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- ======== FOOTER NAV ======== -->
  <?php
  $footernav_on = !empty($footerNav['enabled']);
  $col1 = $footerNav['col1'] ?? [];
  $col2 = $footerNav['col2'] ?? [];
  if (is_string($col1))
    $col1 = array_filter(array_map('trim', explode(',', $col1)));
  if (is_string($col2))
    $col2 = array_filter(array_map('trim', explode(',', $col2)));
  $col1_on = !empty($footerNav['col1_enabled']);
  $col2_on = !empty($footerNav['col2_enabled']);
  ?>
  <?php if ($footernav_on && ($col1_on || $col2_on)): ?>
    <section class="py-5 bg-white border-top">
      <div class="container">
        <div class="row">
          <?php if ($col1_on): ?>
            <div class="col-md-6 mb-3">
              <h6 class="fw-bold">Links</h6>
              <ul class="list-unstyled">
                <?php if ($col1):
                  foreach ($col1 as $it): ?>
                    <li><a href="#" class="link-dark text-decoration-none"><?= esc($it) ?></a></li>
                  <?php endforeach; else: ?>
                  <li><a href="#" class="link-dark text-decoration-none">Shipping</a></li>
                  <li><a href="#" class="link-dark text-decoration-none">Returns</a></li>
                <?php endif; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if ($col2_on): ?>
            <div class="col-md-6 mb-3">
              <h6 class="fw-bold">Support</h6>
              <ul class="list-unstyled">
                <?php if ($col2):
                  foreach ($col2 as $it): ?>
                    <li><a href="#" class="link-dark text-decoration-none"><?= esc($it) ?></a></li>
                  <?php endforeach; else: ?>
                  <li><a href="#" class="link-dark text-decoration-none">FAQ</a></li>
                  <li><a href="#" class="link-dark text-decoration-none">Contact</a></li>
                <?php endif; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ======== FOOTER ======== -->
  <?php
  $footer_on = !empty($footer['enabled']);
  $ft_text_on = !empty($footer['text_enabled']);
  $fb_on = !empty($footer['social']['facebook_enabled'] ?? null);
  $ig_on = !empty($footer['social']['instagram_enabled'] ?? null);
  $yt_on = !empty($footer['social']['youtube_enabled'] ?? null);
  ?>
  <?php if ($footer_on): ?>
    <footer class="footer py-4 mt-4">
      <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
        <div class="small">
          <?php if ($ft_text_on): ?>
            <?= esc(getv($footer, 'text', '© ' . date('Y') . ' FASHN BD. All rights reserved.')) ?>
          <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-3">
          <?php if ($fb_on && getv($footer, 'social.facebook')): ?>
            <a href="<?= esc(getv($footer, 'social.facebook')) ?>" target="_blank"><i class="bi bi-facebook"></i></a>
          <?php endif; ?>
          <?php if ($ig_on && getv($footer, 'social.instagram')): ?>
            <a href="<?= esc(getv($footer, 'social.instagram')) ?>" target="_blank"><i class="bi bi-instagram"></i></a>
          <?php endif; ?>
          <?php if ($yt_on && getv($footer, 'social.youtube')): ?>
            <a href="<?= esc(getv($footer, 'social.youtube')) ?>" target="_blank"><i class="bi bi-youtube"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </footer>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>