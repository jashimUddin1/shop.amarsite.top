<?php
include 'includes/header.php';
require_once "../db/dbcon.php";

// Fetch all sections
$sections = [];
$res = $con->query("SELECT * FROM site_sections ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
  $sections[$row['section']] = json_decode($row['data'], true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($_POST as $key => $val) {
    if (!in_array($key, ['header','nav','slider','banner','product_card','footer_nav','footer'])) continue;
    $json = json_encode($val, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    $stmt = $con->prepare("UPDATE site_sections SET data=? WHERE section=?");
    $stmt->bind_param("ss", $json, $key);
    $stmt->execute();
  }
  $_SESSION['success'] = "✅ Site sections updated successfully!";
  header("Location: settings.php");
  exit;
}
?>

<h4 class="mb-4">🧩 Site Settings</h4>
<?php include "includes/session.php"; ?>

<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#header">Header</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav">Navigation</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#slider">Slider</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#banner">Banner</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#product">Product Card</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#footer_nav">Footer Nav</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#footer">Footer</button></li>
</ul>

<form method="post" enctype="multipart/form-data">
<div class="tab-content border rounded p-4 bg-white shadow-sm">

  <!-- Header -->
  <div class="tab-pane fade show active" id="header">
    <h5>Header Settings</h5>
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label class="form-label">Site Name</label>
        <input type="text" name="header[site_name]" value="<?=htmlspecialchars($sections['header']['site_name']??'')?>" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Tagline</label>
        <input type="text" name="header[tagline]" value="<?=htmlspecialchars($sections['header']['tagline']??'')?>" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Logo (path)</label>
        <input type="text" name="header[logo]" value="<?=htmlspecialchars($sections['header']['logo']??'')?>" class="form-control">
        <?php if(!empty($sections['header']['logo'])): ?>
          <img src="<?=htmlspecialchars($sections['header']['logo'])?>" class="mt-2" style="height:60px;">
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <div class="tab-pane fade" id="nav">
    <h5>Navigation Menu</h5>
    <div class="mb-3">
      <label class="form-label">Menu Items (comma separated)</label>
      <input type="text" name="nav[menu]" value="<?=htmlspecialchars(implode(',',$sections['nav']['menu']??[]))?>" class="form-control">
    </div>
  </div>

  <!-- Slider -->
  <div class="tab-pane fade" id="slider">
    <h5>Homepage Slider</h5>
    <div class="mb-3">
      <label class="form-label">Image URLs (comma separated)</label>
      <input type="text" name="slider[images]" value="<?=htmlspecialchars(implode(',',$sections['slider']['images']??[]))?>" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Caption</label>
      <input type="text" name="slider[caption]" value="<?=htmlspecialchars($sections['slider']['caption']??'')?>" class="form-control">
    </div>
  </div>

  <!-- Banner -->
  <div class="tab-pane fade" id="banner">
    <h5>Promo Banner</h5>
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="banner[title]" value="<?=htmlspecialchars($sections['banner']['title']??'')?>" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Subtitle</label>
      <input type="text" name="banner[subtitle]" value="<?=htmlspecialchars($sections['banner']['subtitle']??'')?>" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Image URL</label>
      <input type="text" name="banner[image]" value="<?=htmlspecialchars($sections['banner']['image']??'')?>" class="form-control">
      <?php if(!empty($sections['banner']['image'])): ?>
        <img src="<?=htmlspecialchars($sections['banner']['image'])?>" class="mt-2 rounded" style="height:60px;">
      <?php endif; ?>
    </div>
  </div>

  <!-- Product Card -->
  <div class="tab-pane fade" id="product">
    <h5>Product Card Layout</h5>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Display Count</label>
        <input type="number" name="product_card[count]" value="<?=$sections['product_card']['count']??8?>" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Layout Style</label>
        <select name="product_card[layout]" class="form-select">
          <?php foreach(['grid','carousel','masonry'] as $l): ?>
            <option value="<?=$l?>" <?=$sections['product_card']['layout']==$l?'selected':''?>><?=$l?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Card Design</label>
        <select name="product_card[style]" class="form-select">
          <?php foreach(['classic','minimal','shadow','bordered'] as $s): ?>
            <option value="<?=$s?>" <?=$sections['product_card']['style']==$s?'selected':''?>><?=$s?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Footer Nav -->
  <div class="tab-pane fade" id="footer_nav">
    <h5>Footer Navigation</h5>
    <div class="mb-3">
      <label class="form-label">Links (comma separated)</label>
      <input type="text" name="footer_nav[links]" value="<?=htmlspecialchars(implode(',',$sections['footer_nav']['links']??[]))?>" class="form-control">
    </div>
  </div>

  <!-- Footer -->
  <div class="tab-pane fade" id="footer">
    <h5>Footer Information</h5>
    <div class="mb-3">
      <label class="form-label">Text</label>
      <input type="text" name="footer[text]" value="<?=htmlspecialchars($sections['footer']['text']??'')?>" class="form-control">
    </div>
    <div class="row g-2">
      <div class="col-md-4"><label>Facebook</label><input name="footer[social][facebook]" value="<?=htmlspecialchars($sections['footer']['social']['facebook']??'')?>" class="form-control"></div>
      <div class="col-md-4"><label>Instagram</label><input name="footer[social][instagram]" value="<?=htmlspecialchars($sections['footer']['social']['instagram']??'')?>" class="form-control"></div>
      <div class="col-md-4"><label>Youtube</label><input name="footer[social][youtube]" value="<?=htmlspecialchars($sections['footer']['social']['youtube']??'')?>" class="form-control"></div>
    </div>
  </div>

</div>

<div class="text-end mt-4">
  <button class="btn btn-dark px-4">💾 Save All Changes</button>
</div>
</form>

<?php include 'includes/footer.php'; ?>
