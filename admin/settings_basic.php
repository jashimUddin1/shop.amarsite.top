<?php // admin/settings_basic.php
session_start();
include 'includes/header.php';
require_once "../db/dbcon.php";

/** Allowed sections (Topbar added) */
$allowed_sections = ['topbar','header','nav','slider','banner','product_card','footer_nav','footer'];

/** Load all sections */
$sections = [];
if ($res = $con->query("SELECT * FROM site_sections ORDER BY id ASC")) {
  while ($row = $res->fetch_assoc()) {
    $sections[$row['section']] = json_decode($row['data'], true) ?: [];
  }
}

/** Save (Partial & All) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $saveAll       = isset($_POST['save_all']);
  $targetSection = $_POST['save_section'] ?? null;

  foreach ($_POST as $key => $val) {
    if (!in_array($key, $allowed_sections, true)) continue;

    // Partial save হলে কেবল target section সেভ করবো
    if (!$saveAll && $key !== $targetSection) continue;

    if (!is_array($val)) $val = [$key => $val];
    $json = json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = $con->prepare("INSERT INTO site_sections (section, data)
                           VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE data = VALUES(data)");
    $stmt->bind_param("ss", $key, $json);
    $stmt->execute();
    $stmt->close();
  }

  $_SESSION['success'] = $saveAll
    ? "✅ All sections saved successfully!"
    : ("✅ '".htmlspecialchars(strtoupper($targetSection))."' section saved successfully!");

  header("Location: settings_basic.php");
  exit;
}

/** Helpers */
function g($arr, $path, $default='') {
  $p = explode('.', $path); $v = $arr;
  foreach ($p as $seg) { if (!is_array($v) || !array_key_exists($seg,$v)) return $default; $v = $v[$seg]; }
  return is_scalar($v) ? $v : $default;
}

/** Short aliases */
$topbar      = $sections['topbar']       ?? [];
$header      = $sections['header']       ?? [];
$nav         = $sections['nav']          ?? [];
$slider      = $sections['slider']       ?? [];
$banner      = $sections['banner']       ?? [];
$productCard = $sections['product_card'] ?? [];
$footerNav   = $sections['footer_nav']   ?? [];
$footer      = $sections['footer']       ?? [];
?>


<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0">Basic Settings</h3>
<?php if (!empty($_SESSION['success'])): ?>
  <div class="end-0 p-3" style="z-index:1080;">
    <div class="toast align-items-center text-bg-success border-0 show"
         role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
      <div class="d-flex">
        <div class="toast-body">
          <!-- <i class="bi bi-check-circle me-2"></i> -->
          <?= htmlspecialchars($_SESSION['success']); ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['success']); ?>
  
<?php endif; ?>
</div>

<!-- Tabs (Topbar first) -->
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item">
    <button id="btn-tabTopbar" type="button" class="nav-link active"
            role="tab" aria-selected="true" aria-controls="tabTopbar"
            data-bs-toggle="tab" data-bs-target="#tabTopbar">Topbar</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabHeader" type="button" class="nav-link"
            role="tab" aria-selected="false" aria-controls="tabHeader"
            data-bs-toggle="tab" data-bs-target="#tabHeader">Header</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabNav" type="button" class="nav-link"
            role="tab" aria-selected="false" aria-controls="tabNav"
            data-bs-toggle="tab" data-bs-target="#tabNav">Navigation</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabSlider" type="button" class="nav-link"
            role="tab" aria-selected="false" aria-controls="tabSlider"
            data-bs-toggle="tab" data-bs-target="#tabSlider">Slider</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabBanner" type="button" class="nav-link"
            role="tab" aria-selected="false" aria-controls="tabBanner"
            data-bs-toggle="tab" data-bs-target="#tabBanner">Banner</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabProduct" type="button" class="nav-link"
            role="tab" aria-selected="false" aria-controls="tabProduct"
            data-bs-toggle="tab" data-bs-target="#tabProduct">Product Card</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabFooterNav" type="button" class="nav-link"
            role="tab" aria-selected="false" aria-controls="tabFooterNav"
            data-bs-toggle="tab" data-bs-target="#tabFooterNav">Footer Nav</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabFooter" type="button" class="nav-link"
            role="tab" aria-selected="false" aria-controls="tabFooter"
            data-bs-toggle="tab" data-bs-target="#tabFooter">Footer</button>
  </li>
</ul>

<form method="post" enctype="multipart/form-data">
<div class="tab-content border rounded p-4 bg-white shadow-sm">

  <!-- ===== TOPBAR ===== -->
  <div class="tab-pane fade show active" id="tabTopbar" role="tabpanel" aria-labelledby="btn-tabTopbar">
    <h5 class="mb-3">Topbar (Announcement + Right badges)</h5>

    <div class="row g-3">
      <div class="col-md-3 d-flex align-items-center">
        <?php $enabled = !empty($topbar['enabled']); ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="tbEnabled" name="topbar[enabled]" value="1" <?=$enabled?'checked':''?>>
          <label class="form-check-label" for="tbEnabled">Enable Topbar</label>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Background</label>
        <input type="color" name="topbar[bg]" class="form-control form-control-color"
               value="<?=htmlspecialchars($topbar['bg'] ?? '#111111')?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Default text color</label>
        <input type="color" name="topbar[color]" class="form-control form-control-color"
               value="<?=htmlspecialchars($topbar['color'] ?? '#ffffff')?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Vertical padding (px)</label>
        <input type="number" min="0" name="topbar[py]" class="form-control"
               value="<?=htmlspecialchars($topbar['py'] ?? 6)?>">
      </div>
    </div>

    <hr>

    <h6 class="mb-2">Left side text</h6>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Text</label>
        <input type="text" name="topbar[left][text]" class="form-control"
               placeholder="Mega Sale: Buy 2 get 1 free"
               value="<?=htmlspecialchars($topbar['left']['text'] ?? ($topbar['text'] ?? ''))?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Link (optional)</label>
        <input type="text" name="topbar[left][link]" class="form-control"
               placeholder="https://example.com/offers"
               value="<?=htmlspecialchars($topbar['left']['link'] ?? ($topbar['link'] ?? ''))?>">
      </div>
    </div>

    <hr>

    <?php
      // Defaults for two right badges
      $r0 = $topbar['right'][0] ?? ['icon'=>'bi-truck','text'=>'Free delivery over BDT 3,500','link'=>'','color'=>'#ffffff','opacity'=>0.9];
      $r1 = $topbar['right'][1] ?? ['icon'=>'bi-arrow-repeat','text'=>'7 day easy returns','link'=>'','color'=>'#ffffff','opacity'=>0.8];
    ?>
    <h6 class="mb-2">Right side badges</h6>

    <div class="row g-3">
      <!-- Badge 1 -->
      <div class="col-md-6">
        <div class="border rounded p-3">
          <div class="row g-2 align-items-end">
            <!-- Text = 70% -->
            <div class="col-md-8">
              <label class="form-label">Badge 1 Text</label>
              <input type="text" class="form-control" name="topbar[right][0][text]" value="<?=htmlspecialchars($r0['text'])?>">
            </div>
            <!-- Color = 15% -->
            <div class="col-md-2">
              <label class="form-label">Color</label>
              <input type="color" class="form-control form-control-color w-100" name="topbar[right][0][color]" value="<?=htmlspecialchars($r0['color'] ?? '#ffffff')?>">
            </div>
            <!-- Opacity = 15% -->
            <div class="col-md-2">
              <label class="form-label">Opacity</label>
              <input type="number" step="0.1" min="0.1" max="1" class="form-control" name="topbar[right][0][opacity]" value="<?=htmlspecialchars($r0['opacity'] ?? 1)?>">
            </div>
          </div>

          <div class="row g-2 mt-2">
            <div class="col-md-6">
              <label class="form-label">Icon (Bootstrap Icons)</label>
              <input type="text" class="form-control" name="topbar[right][0][icon]" placeholder="bi-truck" value="<?=htmlspecialchars($r0['icon'])?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Link (optional)</label>
              <input type="text" class="form-control" name="topbar[right][0][link]" value="<?=htmlspecialchars($r0['link'])?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Badge 2 -->
      <div class="col-md-6">
        <div class="border rounded p-3">
          <div class="row g-2 align-items-end">
            <!-- Text = 70% -->
            <div class="col-md-8">
              <label class="form-label">Badge 2 Text</label>
              <input type="text" class="form-control" name="topbar[right][1][text]" value="<?=htmlspecialchars($r1['text'])?>">
            </div>
            <!-- Color = 15% -->
            <div class="col-md-2">
              <label class="form-label">Color</label>
              <input type="color" class="form-control form-control-color w-100" name="topbar[right][1][color]" value="<?=htmlspecialchars($r1['color'] ?? '#ffffff')?>">
            </div>
            <!-- Opacity = 15% -->
            <div class="col-md-2">
              <label class="form-label">Opacity</label>
              <input type="number" step="0.1" min="0.1" max="1" class="form-control" name="topbar[right][1][opacity]" value="<?=htmlspecialchars($r1['opacity'] ?? 1)?>">
            </div>
          </div>

          <div class="row g-2 mt-2">
            <div class="col-md-6">
              <label class="form-label">Icon (Bootstrap Icons)</label>
              <input type="text" class="form-control" name="topbar[right][1][icon]" placeholder="bi-arrow-repeat" value="<?=htmlspecialchars($r1['icon'])?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Link (optional)</label>
              <input type="text" class="form-control" name="topbar[right][1][link]" value="<?=htmlspecialchars($r1['link'])?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Buttons: Save (this tab) / Save All -->
    <div class="d-flex justify-content-between mt-4">
      <button type="submit" name="save_section" value="topbar" class="btn btn-secondary px-4">
        <i class="bi bi-save me-1"></i> Save
      </button>
      <button type="submit" name="save_all" class="btn btn-dark px-4">
        <i class="bi bi-save2 me-1"></i> Save All Changes
      </button>
    </div>
  </div>

  <!-- ===== HEADER ===== -->
  <div class="tab-pane fade" id="tabHeader" role="tabpanel" aria-labelledby="btn-tabHeader">
    <h5 class="mb-3">Header</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Site Title</label>
        <input type="text" name="header[title]" class="form-control"
               value="<?=htmlspecialchars(g($header,'title'))?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Logo URL</label>
        <input type="text" name="header[logo]" class="form-control"
               value="<?=htmlspecialchars(g($header,'logo'))?>">
        <?php if(!empty($header['logo'])): ?>
          <img src="<?=htmlspecialchars($header['logo'])?>" class="mt-2 rounded" style="height:48px;">
        <?php endif; ?>
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button type="submit" name="save_section" value="header" class="btn btn-secondary px-4">
        <i class="bi bi-save me-1"></i> Save
      </button>
      <button type="submit" name="save_all" class="btn btn-dark px-4">
        <i class="bi bi-save2 me-1"></i> Save All Changes
      </button>
    </div>
  </div>

  <!-- ===== NAV ===== -->
  <div class="tab-pane fade" id="tabNav" role="tabpanel" aria-labelledby="btn-tabNav">
    <h5 class="mb-3">Navigation</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Brand Text</label>
        <input type="text" name="nav[brand]" class="form-control"
               value="<?=htmlspecialchars(g($nav,'brand'))?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Menu (comma separated)</label>
        <input type="text" name="nav[menu]" class="form-control"
               placeholder="Home,Shop,About,Contact"
               value="<?=htmlspecialchars(is_array(g($nav,'menu')) ? implode(',', $nav['menu']) : g($nav,'menu'))?>">
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button type="submit" name="save_section" value="nav" class="btn btn-secondary px-4">
        <i class="bi bi-save me-1"></i> Save
      </button>
      <button type="submit" name="save_all" class="btn btn-dark px-4">
        <i class="bi bi-save2 me-1"></i> Save All Changes
      </button>
    </div>
  </div>

  <!-- ===== SLIDER ===== -->
  <div class="tab-pane fade" id="tabSlider" role="tabpanel" aria-labelledby="btn-tabSlider">
    <h5 class="mb-3">Slider</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="slider[title]" class="form-control"
               value="<?=htmlspecialchars(g($slider,'title'))?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Subtitle</label>
        <input type="text" name="slider[subtitle]" class="form-control"
               value="<?=htmlspecialchars(g($slider,'subtitle'))?>">
      </div>
      <div class="col-md-8">
        <label class="form-label">Image URL</label>
        <input type="text" name="slider[image]" class="form-control"
               value="<?=htmlspecialchars(g($slider,'image'))?>">
        <?php if(!empty($slider['image'])): ?>
          <img src="<?=htmlspecialchars($slider['image'])?>" class="mt-2 rounded" style="height:80px;">
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">Button Text</label>
        <input type="text" name="slider[button_text]" class="form-control"
               value="<?=htmlspecialchars(g($slider,'button_text'))?>">
        <label class="form-label mt-2">Button URL</label>
        <input type="text" name="slider[button_url]" class="form-control"
               value="<?=htmlspecialchars(g($slider,'button_url'))?>">
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button type="submit" name="save_section" value="slider" class="btn btn-secondary px-4">
        <i class="bi bi-save me-1"></i> Save
      </button>
      <button type="submit" name="save_all" class="btn btn-dark px-4">
        <i class="bi bi-save2 me-1"></i> Save All Changes
      </button>
    </div>
  </div>

  <!-- ===== BANNER ===== -->
  <div class="tab-pane fade" id="tabBanner" role="tabpanel" aria-labelledby="btn-tabBanner">
    <h5 class="mb-3">Banner</h5>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Image URL</label>
        <input type="text" name="banner[image]" class="form-control"
               value="<?=htmlspecialchars(g($banner,'image'))?>">
        <?php if(!empty($banner['image'])): ?>
          <img src="<?=htmlspecialchars($banner['image'])?>" class="mt-2 rounded" style="height:80px;">
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">Link URL</label>
        <input type="text" name="banner[link]" class="form-control"
               value="<?=htmlspecialchars(g($banner,'link'))?>">
        <label class="form-label mt-2">Text</label>
        <input type="text" name="banner[text]" class="form-control"
               value="<?=htmlspecialchars(g($banner,'text'))?>">
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button type="submit" name="save_section" value="banner" class="btn btn-secondary px-4">
        <i class="bi bi-save me-1"></i> Save
      </button>
      <button type="submit" name="save_all" class="btn btn-dark px-4">
        <i class="bi bi-save2 me-1"></i> Save All Changes
      </button>
    </div>
  </div>

  <!-- ===== PRODUCT CARD ===== -->
  <div class="tab-pane fade" id="tabProduct" role="tabpanel" aria-labelledby="btn-tabProduct">
    <h5 class="mb-3">Product Card</h5>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Display Count</label>
        <input type="number" name="product_card[count]" class="form-control"
               value="<?=htmlspecialchars($productCard['count'] ?? 8)?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Layout</label>
        <?php $layout = $productCard['layout'] ?? 'grid'; ?>
        <select name="product_card[layout]" class="form-select">
          <?php foreach(['grid','carousel','masonry'] as $l): ?>
            <option value="<?=$l?>" <?=$layout===$l?'selected':''?>><?=$l?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Card Style</label>
        <?php $style = $productCard['style'] ?? 'classic'; ?>
        <select name="product_card[style]" class="form-select">
          <?php foreach(['classic','minimal','shadow','bordered'] as $s): ?>
            <option value="<?=$s?>" <?=$style===$s?'selected':''?>><?=$s?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button type="submit" name="save_section" value="product_card" class="btn btn-secondary px-4">
        <i class="bi bi-save me-1"></i> Save
      </button>
      <button type="submit" name="save_all" class="btn btn-dark px-4">
        <i class="bi bi-save2 me-1"></i> Save All Changes
      </button>
    </div>
  </div>

  <!-- ===== FOOTER NAV ===== -->
  <div class="tab-pane fade" id="tabFooterNav" role="tabpanel" aria-labelledby="btn-tabFooterNav">
    <h5 class="mb-3">Footer Navigation</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Column 1 (comma separated)</label>
        <input type="text" name="footer_nav[col1]" class="form-control"
               value="<?=htmlspecialchars(is_array($footerNav['col1'] ?? null) ? implode(',', $footerNav['col1']) : ($footerNav['col1'] ?? ''))?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Column 2 (comma separated)</label>
        <input type="text" name="footer_nav[col2]" class="form-control"
               value="<?=htmlspecialchars(is_array($footerNav['col2'] ?? null) ? implode(',', $footerNav['col2']) : ($footerNav['col2'] ?? ''))?>">
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button type="submit" name="save_section" value="footer_nav" class="btn btn-secondary px-4">
        <i class="bi bi-save me-1"></i> Save
      </button>
      <button type="submit" name="save_all" class="btn btn-dark px-4">
        <i class="bi bi-save2 me-1"></i> Save All Changes
      </button>
    </div>
  </div>

  <!-- ===== FOOTER ===== -->
  <div class="tab-pane fade" id="tabFooter" role="tabpanel" aria-labelledby="btn-tabFooter">
    <h5 class="mb-3">Footer</h5>
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Text</label>
        <input type="text" name="footer[text]" class="form-control"
               value="<?=htmlspecialchars($footer['text'] ?? '')?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Facebook</label>
        <input type="text" name="footer[social][facebook]" class="form-control"
               value="<?=htmlspecialchars($footer['social']['facebook'] ?? '')?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Instagram</label>
        <input type="text" name="footer[social][instagram]" class="form-control"
               value="<?=htmlspecialchars($footer['social']['instagram'] ?? '')?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">YouTube</label>
        <input type="text" name="footer[social][youtube]" class="form-control"
               value="<?=htmlspecialchars($footer['social']['youtube'] ?? '')?>">
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <button type="submit" name="save_section" value="footer" class="btn btn-secondary px-4">
        <i class="bi bi-save me-1"></i> Save
      </button>
      <button type="submit" name="save_all" class="btn btn-dark px-4">
        <i class="bi bi-save2 me-1"></i> Save All Changes
      </button>
    </div>
  </div>

</div>
</form>

<?php include 'includes/footer.php'; ?>
