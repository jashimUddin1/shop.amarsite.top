<?php
include 'includes/header.php';
require_once "../db/dbcon.php";

// Load all sections
$sections = [];
$res = $con->query("SELECT * FROM site_sections ORDER BY id ASC");
while ($r = $res->fetch_assoc()) {
  $sections[$r['section']] = json_decode($r['data'], true);
}

// Handle POST (save)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($_POST as $section => $data) {
    if (!is_array($data)) continue;
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $stmt = $con->prepare("UPDATE site_sections SET data=? WHERE section=?");
    $stmt->bind_param("ss", $json, $section);
    $stmt->execute();
  }
  $_SESSION['success'] = "✅ All sections updated successfully!";
  header("Location: settings.php");
  exit;
}
?>

<h4 class="mb-4"><i class="bi bi-gear-wide-connected me-2"></i>Advanced Site Settings</h4>
<?php include "includes/session.php"; ?>

<form method="post" enctype="multipart/form-data" class="accordion" id="siteSettingsAccordion">

<!-- ===== HEADER SECTION ===== -->
<div class="accordion-item border mb-3 shadow-sm">
  <h2 class="accordion-header" id="headingHeader">
    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHeader" aria-expanded="true">
      <i class="bi bi-layout-text-window-reverse me-2 text-primary"></i> Header Settings
    </button>
  </h2>
  <div id="collapseHeader" class="accordion-collapse collapse show" data-bs-parent="#siteSettingsAccordion">
    <div class="accordion-body bg-light">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Site Name</label>
          <input name="header[site_name]" value="<?=htmlspecialchars($sections['header']['site_name']??'')?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tagline</label>
          <input name="header[tagline]" value="<?=htmlspecialchars($sections['header']['tagline']??'')?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Logo URL / Path</label>
          <input name="header[logo]" value="<?=htmlspecialchars($sections['header']['logo']??'')?>" class="form-control">
          <?php if(!empty($sections['header']['logo'])): ?>
            <img src="<?=htmlspecialchars($sections['header']['logo'])?>" class="mt-2 border rounded" style="height:60px;">
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== NAVIGATION ===== -->
<div class="accordion-item border mb-3 shadow-sm">
  <h2 class="accordion-header" id="headingNav">
    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNav">
      <i class="bi bi-menu-button-wide me-2 text-success"></i> Navigation Menu
    </button>
  </h2>
  <div id="collapseNav" class="accordion-collapse collapse" data-bs-parent="#siteSettingsAccordion">
    <div class="accordion-body bg-light">
      <div class="mb-3">
        <label class="form-label">Menu Items (comma separated)</label>
        <input type="text" name="nav[menu]" value="<?=htmlspecialchars(implode(',',$sections['nav']['menu']??[]))?>" class="form-control">
      </div>
    </div>
  </div>
</div>

<!-- ===== SLIDER ===== -->
<div class="accordion-item border mb-3 shadow-sm">
  <h2 class="accordion-header" id="headingSlider">
    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSlider">
      <i class="bi bi-images me-2 text-warning"></i> Slider Settings
    </button>
  </h2>
  <div id="collapseSlider" class="accordion-collapse collapse" data-bs-parent="#siteSettingsAccordion">
    <div class="accordion-body bg-light">
      <div class="mb-3">
        <label class="form-label">Image URLs (comma separated)</label>
        <textarea name="slider[images]" class="form-control" rows="2"><?=htmlspecialchars(implode(',',$sections['slider']['images']??[]))?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Slider Caption</label>
        <input type="text" name="slider[caption]" value="<?=htmlspecialchars($sections['slider']['caption']??'')?>" class="form-control">
      </div>
    </div>
  </div>
</div>

<!-- ===== BANNER ===== -->
<div class="accordion-item border mb-3 shadow-sm">
  <h2 class="accordion-header" id="headingBanner">
    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBanner">
      <i class="bi bi-megaphone me-2 text-danger"></i> Promo Banner
    </button>
  </h2>
  <div id="collapseBanner" class="accordion-collapse collapse" data-bs-parent="#siteSettingsAccordion">
    <div class="accordion-body bg-light">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input type="text" name="banner[title]" value="<?=htmlspecialchars($sections['banner']['title']??'')?>" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Subtitle</label>
          <input type="text" name="banner[subtitle]" value="<?=htmlspecialchars($sections['banner']['subtitle']??'')?>" class="form-control">
        </div>
        <div class="col-md-12">
          <label class="form-label">Banner Image URL</label>
          <input type="text" name="banner[image]" value="<?=htmlspecialchars($sections['banner']['image']??'')?>" class="form-control">
          <?php if(!empty($sections['banner']['image'])): ?>
            <img src="<?=htmlspecialchars($sections['banner']['image'])?>" class="mt-2 rounded" style="height:80px;">
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== PRODUCT CARD ===== -->
<div class="accordion-item border mb-3 shadow-sm">
  <h2 class="accordion-header" id="headingProduct">
    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProduct">
      <i class="bi bi-grid-3x3-gap me-2 text-info"></i> Product Card Settings
    </button>
  </h2>
  <div id="collapseProduct" class="accordion-collapse collapse" data-bs-parent="#siteSettingsAccordion">
    <div class="accordion-body bg-light">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Product Count</label>
          <input type="number" name="product_card[count]" value="<?=$sections['product_card']['count']??8?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Layout Type</label>
          <select name="product_card[layout]" class="form-select">
            <?php foreach(['grid','carousel','masonry'] as $opt): ?>
              <option value="<?=$opt?>" <?=$opt==($sections['product_card']['layout']??'grid')?'selected':''?>><?=$opt?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Card Style</label>
          <select name="product_card[style]" class="form-select">
            <?php foreach(['classic','minimal','shadow','bordered'] as $opt): ?>
              <option value="<?=$opt?>" <?=$opt==($sections['product_card']['style']??'classic')?'selected':''?>><?=$opt?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== FOOTER NAV ===== -->
<div class="accordion-item border mb-3 shadow-sm">
  <h2 class="accordion-header" id="headingFooterNav">
    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFooterNav">
      <i class="bi bi-link-45deg me-2 text-secondary"></i> Footer Navigation
    </button>
  </h2>
  <div id="collapseFooterNav" class="accordion-collapse collapse" data-bs-parent="#siteSettingsAccordion">
    <div class="accordion-body bg-light">
      <label class="form-label">Links (comma separated)</label>
      <input name="footer_nav[links]" value="<?=htmlspecialchars(implode(',',$sections['footer_nav']['links']??[]))?>" class="form-control">
    </div>
  </div>
</div>

<!-- ===== FOOTER ===== -->
<div class="accordion-item border shadow-sm">
  <h2 class="accordion-header" id="headingFooter">
    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFooter">
      <i class="bi bi-ui-checks-grid me-2 text-dark"></i> Footer Section
    </button>
  </h2>
  <div id="collapseFooter" class="accordion-collapse collapse" data-bs-parent="#siteSettingsAccordion">
    <div class="accordion-body bg-light">
      <div class="mb-3">
        <label class="form-label">Footer Text</label>
        <input name="footer[text]" value="<?=htmlspecialchars($sections['footer']['text']??'')?>" class="form-control">
      </div>
      <div class="row g-2">
        <div class="col-md-4"><label>Facebook</label><input name="footer[social][facebook]" value="<?=htmlspecialchars($sections['footer']['social']['facebook']??'')?>" class="form-control"></div>
        <div class="col-md-4"><label>Instagram</label><input name="footer[social][instagram]" value="<?=htmlspecialchars($sections['footer']['social']['instagram']??'')?>" class="form-control"></div>
        <div class="col-md-4"><label>Youtube</label><input name="footer[social][youtube]" value="<?=htmlspecialchars($sections['footer']['social']['youtube']??'')?>" class="form-control"></div>
      </div>
    </div>
  </div>
</div>

<div class="text-end mt-4">
  <button class="btn btn-dark px-4 py-2"><i class="bi bi-save2 me-2"></i>Save All Changes</button>
</div>
</form>

<?php include 'includes/footer.php'; ?>
