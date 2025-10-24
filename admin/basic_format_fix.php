<?php // admin/settings_basic.php
include 'includes/header.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once "../db/dbcon.php";

/** Allowed sections */
$allowed_sections = ['topbar', 'header', 'nav', 'hero', 'slider', 'banner', 'product_card', 'footer_nav', 'footer'];

/** Which tab should be active (default: topbar) */
$activeTab = $_GET['tab'] ?? 'topbar';
if (!in_array($activeTab, $allowed_sections, true)) {
  $activeTab = 'topbar';
}

/** Load all sections */
$sections = [];
if ($res = $con->query("SELECT * FROM site_sections ORDER BY id ASC")) {
  while ($row = $res->fetch_assoc()) {
    $sections[$row['section']] = json_decode($row['data'], true) ?: [];
  }
}

/** Save (Partial & All) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(400);
    die('Invalid CSRF token');
  }

  // --- Header logo upload handling (single input: name="header_logo") ---
  if (isset($_FILES['header_logo']) && is_array($_FILES['header_logo']) && $_FILES['header_logo']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['header_logo']['tmp_name'];
    $orig = $_FILES['header_logo']['name'];

    if (is_uploaded_file($tmp)) {
      $uploadDirFs = __DIR__ . "/uploads"; // admin/uploads/
      if (!is_dir($uploadDirFs)) {
        @mkdir($uploadDirFs, 0775, true);
      }

      $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
      $allow = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'];

      // size limit 3MB
      if (($_FILES['header_logo']['size'] ?? 0) > 3 * 1024 * 1024) {
        die('File too large');
      }

      // mime check
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime = $finfo->file($tmp);
      $mime_allow = ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'];
      if (!in_array($mime, $mime_allow, true)) {
        die('Invalid file type');
      }

      // normalize extension based on mime
      $ext = $mime === 'image/jpeg' ? 'jpg' : ($mime === 'image/svg+xml' ? 'svg' : $ext);
      if (!in_array($ext, $allow, true)) {
        $ext = 'png';
      }

      $fname = "logo_" . date("Ymd_His") . "_" . bin2hex(random_bytes(3)) . "." . $ext;
      $destFs = $uploadDirFs . DIRECTORY_SEPARATOR . $fname;

      if (move_uploaded_file($tmp, $destFs)) {
        $publicUrl = "uploads/" . $fname; // DB value

        if (!isset($_POST['header']))
          $_POST['header'] = [];
        $_POST['header']['logo'] = $publicUrl;
        $_POST['header']['logo_enabled'] = 1;
      } else {
        $_SESSION['error'] = "Logo upload failed (move). Please check folder permission.";
      }
    } else {
      $_SESSION['error'] = "Invalid upload (tmp not found).";
    }
  } elseif (!empty($_FILES['header_logo']['error']) && $_FILES['header_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $_SESSION['error'] = "Upload error code: " . (int) $_FILES['header_logo']['error'];
  }


  // ---------------- Helper function: Fix image path for admin preview ----------------
  function admin_asset_url(string $path): string
  {
    $p = trim($path);
    if ($p === '')
      return '';

    // Already absolute
    if (preg_match('~^(https?:)?//~i', $p))
      return $p;

    // If starts with admin/uploads/, admin page should load uploads/
    if (strpos($p, 'admin/uploads/') === 0) {
      return 'uploads/' . substr($p, strlen('admin/uploads/'));
    }

    // If starts with uploads/, keep it
    if (strpos($p, 'uploads/') === 0)
      return $p;

    // Fallback
    return '../' . ltrim($p, '/');
  }

  $saveAll = isset($_POST['save_all']);
  $targetSection = $_POST['save_section'] ?? null;

  foreach ($_POST as $key => $val) {
    if (!in_array($key, $allowed_sections, true))
      continue;

    // Partial save হলে কেবল target section সেভ
    if (!$saveAll && $key !== $targetSection)
      continue;

    if (!is_array($val))
      $val = [$key => $val];
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
    : ("✅ '" . htmlspecialchars(strtoupper($targetSection)) . "' section saved successfully!");

  $returnTab = $targetSection ?: ($_POST['active_tab'] ?? 'topbar');
  header("Location: settings_basic.php?tab=" . urlencode($returnTab));
  exit;
}

/** Helpers */
function g($arr, $path, $default = '')
{
  $p = explode('.', $path);
  $v = $arr;
  foreach ($p as $seg) {
    if (!is_array($v) || !array_key_exists($seg, $v))
      return $default;
    $v = $v[$seg];
  }
  return $v;
}

/** Aliases */
$topbar = $sections['topbar'] ?? [];
$header = $sections['header'] ?? [];
$nav = $sections['nav'] ?? [];
$slider = $sections['slider'] ?? [];
$banner = $sections['banner'] ?? [];
$productCard = $sections['product_card'] ?? [];
$footerNav = $sections['footer_nav'] ?? [];
$footer = $sections['footer'] ?? [];
?>

<?php
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>



<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0">Basic Settings</h3>
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="end-0 p-3" style="z-index:1080;">
      <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive"
        aria-atomic="true" data-bs-delay="4000">
        <div class="d-flex">
          <div class="toast-body">
            <?= htmlspecialchars($_SESSION['success']); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['success']); ?>
    <script>
      (function () {
        document.querySelectorAll('.toast').forEach(function (t) {
          new bootstrap.Toast(t).show();
        });
      })();
    </script>
  <?php endif; ?>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item">
    <button id="btn-tabTopbar" type="button" class="nav-link <?= $activeTab === 'topbar' ? 'active' : '' ?>" role="tab"
      aria-selected="<?= $activeTab === 'topbar' ? 'true' : 'false' ?>" aria-controls="tabTopbar" data-bs-toggle="tab"
      data-bs-target="#tabTopbar">Topbar</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabHeader" type="button" class="nav-link <?= $activeTab === 'header' ? 'active' : '' ?>" role="tab"
      aria-selected="<?= $activeTab === 'header' ? 'true' : 'false' ?>" aria-controls="tabHeader" data-bs-toggle="tab"
      data-bs-target="#tabHeader">Header</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabNav" type="button" class="nav-link <?= $activeTab === 'nav' ? 'active' : '' ?>" role="tab"
      aria-selected="<?= $activeTab === 'nav' ? 'true' : 'false' ?>" aria-controls="tabNav" data-bs-toggle="tab"
      data-bs-target="#tabNav">Navigation</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabSlider" type="button" class="nav-link <?= $activeTab === 'slider' ? 'active' : '' ?>" role="tab"
      aria-selected="<?= $activeTab === 'slider' ? 'true' : 'false' ?>" aria-controls="tabSlider" data-bs-toggle="tab"
      data-bs-target="#tabSlider">Slider</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabBanner" type="button" class="nav-link <?= $activeTab === 'banner' ? 'active' : '' ?>" role="tab"
      aria-selected="<?= $activeTab === 'banner' ? 'true' : 'false' ?>" aria-controls="tabBanner" data-bs-toggle="tab"
      data-bs-target="#tabBanner">Banner</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabProduct" type="button" class="nav-link <?= $activeTab === 'product_card' ? 'active' : '' ?>"
      role="tab" aria-selected="<?= $activeTab === 'product_card' ? 'true' : 'false' ?>" aria-controls="tabProduct"
      data-bs-toggle="tab" data-bs-target="#tabProduct">Product Card</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabFooterNav" type="button" class="nav-link <?= $activeTab === 'footer_nav' ? 'active' : '' ?>"
      role="tab" aria-selected="<?= $activeTab === 'footer_nav' ? 'true' : 'false' ?>" aria-controls="tabFooterNav"
      data-bs-toggle="tab" data-bs-target="#tabFooterNav">Footer Nav</button>
  </li>
  <li class="nav-item">
    <button id="btn-tabFooter" type="button" class="nav-link <?= $activeTab === 'footer' ? 'active' : '' ?>" role="tab"
      aria-selected="<?= $activeTab === 'footer' ? 'true' : 'false' ?>" aria-controls="tabFooter" data-bs-toggle="tab"
      data-bs-target="#tabFooter">Footer</button>
  </li>
</ul>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
  <!-- keep track of current tab -->
  <input type="hidden" name="active_tab" id="activeTabInput" value="<?= htmlspecialchars($activeTab) ?>">

  <div class="tab-content border rounded p-4 bg-white shadow-sm">

    <!-- ===== TOPBAR ===== -->
    <div class="tab-pane fade <?= $activeTab === 'topbar' ? 'show active' : '' ?>" id="tabTopbar" role="tabpanel"
      aria-labelledby="btn-tabTopbar">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Topbar (Announcement + Right badges)</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="topbar_enabled" name="topbar[enabled]" value="1"
            <?= !empty($topbar['enabled']) ? 'checked' : ''; ?>>
          <label class="form-check-label small" for="topbar_enabled">Enable section</label>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Background</span>
            <!-- no per-field enable needed -->
          </label>
          <input type="color" name="topbar[bg]" class="form-control form-control-color"
            value="<?= htmlspecialchars($topbar['bg'] ?? '#111111') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Default text color</span>
          </label>
          <input type="color" name="topbar[color]" class="form-control form-control-color"
            value="<?= htmlspecialchars($topbar['color'] ?? '#ffffff') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Vertical padding (px)</span>
          </label>
          <input type="number" min="0" name="topbar[py]" class="form-control"
            value="<?= htmlspecialchars($topbar['py'] ?? 6) ?>">
        </div>
      </div>

      <hr>

      <!-- Left text -->
      <?php
      $left = $topbar['left'] ?? [];
      $left_enabled = !empty($left['enabled']);
      ?>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Left side text</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="topbar[left][enabled]" value="1"
                <?= !empty($left['enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
        </div>
        <div class="col-md-8">
          <input type="text" name="topbar[left][text]" class="form-control" placeholder="Mega Sale: Buy 2 get 1 free"
            value="<?= htmlspecialchars($left['text'] ?? ($topbar['text'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
          <input type="text" name="topbar[left][link]" class="form-control" placeholder="https://example.com/offers"
            value="<?= htmlspecialchars($left['link'] ?? ($topbar['link'] ?? '')) ?>">
        </div>
      </div>

      <hr>

      <?php
      // Defaults for two right badges
      $r0 = $topbar['right'][0] ?? ['enabled' => 1, 'icon' => 'bi-truck', 'text' => 'Free delivery over BDT 3,500', 'link' => '', 'color' => '#ffffff', 'opacity' => 0.9];
      $r1 = $topbar['right'][1] ?? ['enabled' => 1, 'icon' => 'bi-arrow-repeat', 'text' => '7 day easy returns', 'link' => '', 'color' => '#ffffff', 'opacity' => 0.8];
      ?>
      <h6 class="mb-2">Right side badges</h6>

      <div class="row g-3">
        <!-- Badge 1 -->
        <div class="col-md-6">
          <div class="border rounded p-3">
            <div class="row g-2 align-items-end">
              <!-- Label + Enable -->
              <div class="col-12 d-flex align-items-center justify-content-between">
                <label class="form-label mb-1">Badge 1</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="topbar[right][0][enabled]" value="1"
                    <?= !empty($r0['enabled']) ? 'checked' : ''; ?>>
                </div>
              </div>
              <!-- Text = 70% -->
              <div class="col-md-8">
                <input type="text" class="form-control" name="topbar[right][0][text]"
                  value="<?= htmlspecialchars($r0['text']) ?>" placeholder="Text">
              </div>
              <!-- Color = 15% -->
              <div class="col-md-2">
                <input type="color" class="form-control form-control-color w-100" name="topbar[right][0][color]"
                  value="<?= htmlspecialchars($r0['color'] ?? '#ffffff') ?>">
              </div>
              <!-- Opacity = 15% -->
              <div class="col-md-2">
                <input type="number" step="0.1" min="0.1" max="1" class="form-control" name="topbar[right][0][opacity]"
                  value="<?= htmlspecialchars($r0['opacity'] ?? 1) ?>" placeholder="0.8">
              </div>
            </div>

            <div class="row g-2 mt-2">
              <div class="col-md-6">
                <label class="form-label">Icon</label>
                <input type="text" class="form-control" name="topbar[right][0][icon]" placeholder="bi-truck"
                  value="<?= htmlspecialchars($r0['icon']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Link (optional)</label>
                <input type="text" class="form-control" name="topbar[right][0][link]"
                  value="<?= htmlspecialchars($r0['link']) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- Badge 2 -->
        <div class="col-md-6">
          <div class="border rounded p-3">
            <div class="row g-2 align-items-end">
              <!-- Label + Enable -->
              <div class="col-12 d-flex align-items-center justify-content-between">
                <label class="form-label mb-1">Badge 2</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="topbar[right][1][enabled]" value="1"
                    <?= !empty($r1['enabled']) ? 'checked' : ''; ?>>
                </div>
              </div>
              <!-- Text = 70% -->
              <div class="col-md-8">
                <input type="text" class="form-control" name="topbar[right][1][text]"
                  value="<?= htmlspecialchars($r1['text']) ?>" placeholder="Text">
              </div>
              <!-- Color = 15% -->
              <div class="col-md-2">
                <input type="color" class="form-control form-control-color w-100" name="topbar[right][1][color]"
                  value="<?= htmlspecialchars($r1['color'] ?? '#ffffff') ?>">
              </div>
              <!-- Opacity = 15% -->
              <div class="col-md-2">
                <input type="number" step="0.1" min="0.1" max="1" class="form-control" name="topbar[right][1][opacity]"
                  value="<?= htmlspecialchars($r1['opacity'] ?? 1) ?>" placeholder="0.8">
              </div>
            </div>

            <div class="row g-2 mt-2">
              <div class="col-md-6">
                <label class="form-label">Icon</label>
                <input type="text" class="form-control" name="topbar[right][1][icon]" placeholder="bi-arrow-repeat"
                  value="<?= htmlspecialchars($r1['icon']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Link (optional)</label>
                <input type="text" class="form-control" name="topbar[right][1][link]"
                  value="<?= htmlspecialchars($r1['link']) ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Buttons -->
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
    <div class="tab-pane fade <?= $activeTab === 'header' ? 'show active' : '' ?>" id="tabHeader" role="tabpanel"
      aria-labelledby="btn-tabHeader">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Header</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="header_enabled" name="header[enabled]" value="1"
            <?= !empty($header['enabled']) ? 'checked' : ''; ?>>
          <label class="form-check-label small" for="header_enabled">Enable section</label>
        </div>
      </div>

      <?php
      $logoUrl = trim((string) ($header['logo'] ?? ''));
      $title_enabled = !empty($header['title_enabled']);
      $title2_enabled = !empty($header['title2_enabled']);
      $title2_style = $header['title2_style'] ?? 'pill-gray'; // default
      ?>

      <!-- Row 1: Logo upload + Site Title -->
      <!-- Row 1: Logo upload + Site Title (single file input + instant preview) -->
      <div class="row g-3 align-items-end">
        <!-- Logo upload / preview -->
        <div class="col-md-6">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Logo</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="header[logo_enabled]" value="1"
                <?= !empty($header['logo_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>

          <?php
          $logoUrl = trim((string) ($header['logo'] ?? ''));
          $logoSrc = function_exists('admin_asset_url') ? admin_asset_url($logoUrl) : $logoUrl;
          ?>

          <!-- একটাই file input (hidden) -->
          <input id="header_logo" type="file" name="header_logo" accept="image/*" hidden>

          <div class="border rounded p-3">
            <!-- Preview wrapper -->
            <div id="logoPreviewWrap" class="<?= $logoUrl ? '' : 'd-none' ?>">
              <div class="d-flex align-items-center gap-3">
                <img id="logoPreviewImg" src="<?= htmlspecialchars($logoSrc) ?>" alt="logo" class="rounded"
                  style="height:48px; width:auto;">
                <div class="flex-grow-1">
                  <div class="small text-muted">
                    <span id="logoPreviewNote"><?= $logoUrl ? 'Current logo' : '' ?></span>
                    <span id="logoUnsavedBadge" class="badge bg-warning text-dark ms-2 d-none">Unsaved preview</span>
                  </div>
                  <div class="d-flex align-items-center gap-2 mt-1">
                    <!-- Change triggers the same input -->
                    <label for="header_logo" class="btn btn-sm btn-outline-secondary mb-0">Change</label>
                    <button type="button" id="logoResetBtn"
                      class="btn btn-sm btn-outline-secondary d-none">Reset</button>
                    <a id="logoViewBtn" class="btn btn-sm btn-outline-dark <?= $logoUrl ? '' : 'd-none' ?>"
                      href="<?= htmlspecialchars($logoSrc) ?>" target="_blank" rel="noopener">View</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Placeholder -->
            <div id="logoPlaceholderWrap" class="text-center p-3 bg-light rounded <?= $logoUrl ? 'd-none' : '' ?>">
              <div class="mb-2" style="width:64px;height:64px;border-radius:999px;background:#000;color:#fff;
                    display:inline-flex;align-items:center;justify-content:center;font-weight:700;">
                img
              </div>
              <div class="small text-muted mb-2">No logo yet</div>
              <!-- Upload triggers the same input -->
              <label for="header_logo" class="btn btn-sm btn-dark mb-0">Upload</label>
              <div class="form-text">Please upload image/picture</div>
            </div>
          </div>
        </div>

        <!-- Site Title -->
        <div class="col-md-6">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Site Title</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="header[title_enabled]" value="1"
                <?= !empty($header['title_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="header[title]" class="form-control" placeholder="e.g. FASHN"
            value="<?= htmlspecialchars($header['title'] ?? '') ?>">
        </div>
      </div>



      <hr>

      <!-- Row 2: Title 2 + Shave Type (inline small select) -->
      <div class="row g-3 align-items-end">
        <div class="col-md-8">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Title 2</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="header[title2_enabled]" value="1"
                <?= !empty($header['title2_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="header[title2]" class="form-control"
            placeholder="e.g. BD / New Collection · Winter '25"
            value="<?= htmlspecialchars($header['title'] ?? '') ? htmlspecialchars($header['title2'] ?? '') : htmlspecialchars($header['title2'] ?? '') ?>">
          <div class="form-text">লম্বা হলে ফ্রন্টএন্ডে স্বয়ংক্রিয়ভাবে ellipsis (… shave) হবে।</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Shave Type (Style)</label>
          <select name="header[title2_style]" class="form-select form-select-sm w-auto d-inline-block">
            <?php
            $options = [
              'pill-gray' => 'Pill — Gray',
              'pill-dark' => 'Pill — Dark',
              'pill-accent' => 'Pill — Accent',
              'outline' => 'Outline',
              'circle' => 'Circle',
            ];
            foreach ($options as $val => $label):
              ?>
              <option value="<?= $val ?>" <?= $title2_style === $val ? 'selected' : ''; ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
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
    <div class="tab-pane fade <?= $activeTab === 'nav' ? 'show active' : '' ?>" id="tabNav" role="tabpanel"
      aria-labelledby="btn-tabNav">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Navigation</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="nav_enabled" name="nav[enabled]" value="1"
            <?= !empty($nav['enabled']) ? 'checked' : ''; ?>>
          <label class="form-check-label small" for="nav_enabled">Enable section</label>
        </div>
      </div>

      <?php $brand_enabled = !empty($nav['brand_enabled']);
      $menu_enabled = !empty($nav['menu_enabled']); ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Brand Text</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="nav[brand_enabled]" value="1"
                <?= !empty($nav['brand_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="nav[brand]" class="form-control"
            value="<?= htmlspecialchars($nav['brand'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Menu (comma separated)</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="nav[menu_enabled]" value="1"
                <?= !empty($nav['menu_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="nav[menu]" class="form-control" placeholder="Home,Shop,About,Contact"
            value="<?= htmlspecialchars(is_array($nav['menu'] ?? null) ? implode(',', $nav['menu']) : ($nav['menu'] ?? '')) ?>">
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
    <div class="tab-pane fade <?= $activeTab === 'slider' ? 'show active' : '' ?>" id="tabSlider" role="tabpanel"
      aria-labelledby="btn-tabSlider">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Slider</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="slider_enabled" name="slider[enabled]" value="1"
            <?= !empty($slider['enabled']) ? 'checked' : ''; ?>>
          <label class="form-check-label small" for="slider_enabled">Enable section</label>
        </div>
      </div>

      <?php
      $sl_title_en = !empty($slider['title_enabled']);
      $sl_sub_en = !empty($slider['subtitle_enabled']);
      $sl_img_en = !empty($slider['image_enabled']);
      $sl_btn_en = !empty($slider['button_enabled']);
      ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Title</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="slider[title_enabled]" value="1"
                <?= !empty($slider['title_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="slider[title]" class="form-control"
            value="<?= htmlspecialchars($slider['title'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Subtitle</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="slider[subtitle_enabled]" value="1"
                <?= !empty($slider['subtitle_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="slider[subtitle]" class="form-control"
            value="<?= htmlspecialchars($slider['subtitle'] ?? '') ?>">
        </div>
        <div class="col-md-8">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Image URL</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="slider[image_enabled]" value="1"
                <?= !empty($slider['image_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="slider[image]" class="form-control"
            value="<?= htmlspecialchars($slider['image'] ?? '') ?>">
          <?php if (!empty($slider['image'])): ?>
            <img src="<?= htmlspecialchars($slider['image']) ?>" class="mt-2 rounded" style="height:80px;">
          <?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Button</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="slider[button_enabled]" value="1"
                <?= !empty($slider['button_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="slider[button_text]" class="form-control mb-2" placeholder="Button text"
            value="<?= htmlspecialchars($slider['button_text'] ?? '') ?>">
          <input type="text" name="slider[button_url]" class="form-control" placeholder="Button URL"
            value="<?= htmlspecialchars($slider['button_url'] ?? '') ?>">
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
    <div class="tab-pane fade <?= $activeTab === 'banner' ? 'show active' : '' ?>" id="tabBanner" role="tabpanel"
      aria-labelledby="btn-tabBanner">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Banner</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="banner_enabled" name="banner[enabled]" value="1"
            <?= !empty($banner['enabled']) ? 'checked' : ''; ?>>
          <label class="form-check-label small" for="banner_enabled">Enable section</label>
        </div>
      </div>

      <?php $bn_img_en = !empty($banner['image_enabled']);
      $bn_txt_en = !empty($banner['text_enabled']); ?>
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Image URL</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="banner[image_enabled]" value="1"
                <?= !empty($banner['image_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="banner[image]" class="form-control"
            value="<?= htmlspecialchars($banner['image'] ?? '') ?>">
          <?php if (!empty($banner['image'])): ?>
            <img src="<?= htmlspecialchars($banner['image']) ?>" class="mt-2 rounded" style="height:80px;">
          <?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Text & Link</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="banner[text_enabled]" value="1"
                <?= !empty($banner['text_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="banner[link]" class="form-control mb-2" placeholder="Link URL"
            value="<?= htmlspecialchars($banner['link'] ?? '') ?>">
          <input type="text" name="banner[text]" class="form-control" placeholder="Overlay text"
            value="<?= htmlspecialchars($banner['text'] ?? '') ?>">
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
    <div class="tab-pane fade <?= $activeTab === 'product_card' ? 'show active' : '' ?>" id="tabProduct" role="tabpanel"
      aria-labelledby="btn-tabProduct">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Product Card</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="product_enabled" name="product_card[enabled]" value="1"
            <?= !empty($productCard['enabled']) ? 'checked' : ''; ?>>
          <label class="form-check-label small" for="product_enabled">Enable section</label>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Display Count</label>
          <input type="number" name="product_card[count]" class="form-control"
            value="<?= htmlspecialchars($productCard['count'] ?? 8) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Layout</label>
          <?php $layout = $productCard['layout'] ?? 'grid'; ?>
          <select name="product_card[layout]" class="form-select">
            <?php foreach (['grid', 'carousel', 'masonry'] as $l): ?>
              <option value="<?= $l ?>" <?= $layout === $l ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Card Style</label>
          <?php $style = $productCard['style'] ?? 'classic'; ?>
          <select name="product_card[style]" class="form-select">
            <?php foreach (['classic', 'minimal', 'shadow', 'bordered'] as $s): ?>
              <option value="<?= $s ?>" <?= $style === $s ? 'selected' : '' ?>><?= $s ?></option>
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
    <div class="tab-pane fade <?= $activeTab === 'footer_nav' ? 'show active' : '' ?>" id="tabFooterNav" role="tabpanel"
      aria-labelledby="btn-tabFooterNav">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Footer Navigation</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="footernav_enabled" name="footer_nav[enabled]" value="1"
            <?= !empty($footerNav['enabled']) ? 'checked' : ''; ?>>
          <label class="form-check-label small" for="footernav_enabled">Enable section</label>
        </div>
      </div>

      <?php $col1_en = !empty($footerNav['col1_enabled']);
      $col2_en = !empty($footerNav['col2_enabled']); ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Column 1 (comma separated)</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="footer_nav[col1_enabled]" value="1"
                <?= !empty($footerNav['col1_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="footer_nav[col1]" class="form-control"
            value="<?= htmlspecialchars(is_array($footerNav['col1'] ?? null) ? implode(',', $footerNav['col1']) : ($footerNav['col1'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Column 2 (comma separated)</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="footer_nav[col2_enabled]" value="1"
                <?= !empty($footerNav['col2_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="footer_nav[col2]" class="form-control"
            value="<?= htmlspecialchars(is_array($footerNav['col2'] ?? null) ? implode(',', $footerNav['col2']) : ($footerNav['col2'] ?? '')) ?>">
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
    <div class="tab-pane fade <?= $activeTab === 'footer' ? 'show active' : '' ?>" id="tabFooter" role="tabpanel"
      aria-labelledby="btn-tabFooter">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0">Footer</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="footer_enabled" name="footer[enabled]" value="1"
            <?= !empty($footer['enabled']) ? 'checked' : ''; ?>>
          <label class="form-check-label small" for="footer_enabled">Enable section</label>
        </div>
      </div>

      <?php
      $ft_text_en = !empty($footer['text_enabled']);
      $fb_en = !empty($footer['social']['facebook_enabled'] ?? null);
      $ig_en = !empty($footer['social']['instagram_enabled'] ?? null);
      $yt_en = !empty($footer['social']['youtube_enabled'] ?? null);
      ?>
      <div class="row g-3">
        <div class="col-md-12">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Footer Text</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="footer[text_enabled]" value="1"
                <?= !empty($footer['text_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="footer[text]" class="form-control"
            value="<?= htmlspecialchars($footer['text'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Facebook</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="footer[social][facebook_enabled]" value="1"
                <?= !empty($footer['social']['facebook_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="footer[social][facebook]" class="form-control"
            value="<?= htmlspecialchars($footer['social']['facebook'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>Instagram</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="footer[social][instagram_enabled]" value="1"
                <?= !empty($footer['social']['instagram_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="footer[social][instagram]" class="form-control"
            value="<?= htmlspecialchars($footer['social']['instagram'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label d-flex align-items-center justify-content-between">
            <span>YouTube</span>
            <span class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="footer[social][youtube_enabled]" value="1"
                <?= !empty($footer['social']['youtube_enabled']) ? 'checked' : ''; ?>>
            </span>
          </label>
          <input type="text" name="footer[social][youtube]" class="form-control"
            value="<?= htmlspecialchars($footer['social']['youtube'] ?? '') ?>">
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

<!-- Keep active tab in URL + hidden input when switching tabs -->
<script>
  document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (btn) {
    btn.addEventListener('shown.bs.tab', function (e) {
      var target = e.target.getAttribute('data-bs-target'); // e.g. #tabTopbar
      var key = (target || '').replace('#tab', '').toLowerCase(); // topbar
      var input = document.getElementById('activeTabInput');
      if (input && key) input.value = key;
      if (key) {
        var url = new URL(window.location);
        url.searchParams.set('tab', key);
        window.history.replaceState({}, '', url);
      }
    });
  });
</script>


<script>
  (function () {
    const fileInput = document.getElementById('header_logo');
    if (!fileInput) return;

    const placeholder = document.getElementById('logoPlaceholderWrap');
    const previewWrap = document.getElementById('logoPreviewWrap');
    const previewImg = document.getElementById('logoPreviewImg');
    const unsavedBadge = document.getElementById('logoUnsavedBadge');
    const previewNote = document.getElementById('logoPreviewNote');
    const resetBtn = document.getElementById('logoResetBtn');
    const viewBtn = document.getElementById('logoViewBtn');

    const initialSrc = previewImg ? previewImg.getAttribute('src') : '';
    let blobUrl = null;

    function updateViewButton(src, isUnsaved) {
      if (!viewBtn) return;
      if (src) {
        viewBtn.classList.remove('d-none');
        viewBtn.setAttribute('href', src);
        viewBtn.textContent = isUnsaved ? 'View (preview)' : 'View';
      } else {
        viewBtn.classList.add('d-none');
        viewBtn.removeAttribute('href');
      }
    }

    function showPreview(src, isUnsaved) {
      if (!previewWrap || !previewImg) return;
      previewImg.src = src || '';
      previewWrap.classList.remove('d-none');
      if (placeholder) placeholder.classList.add('d-none');

      if (unsavedBadge) unsavedBadge.classList.toggle('d-none', !isUnsaved);
      if (resetBtn) resetBtn.classList.toggle('d-none', !isUnsaved);
      if (previewNote) previewNote.textContent = isUnsaved ? 'Selected (not saved yet)' : (initialSrc ? 'Current logo' : '');
      updateViewButton(src, isUnsaved);
    }

    function resetPreview() {
      if (blobUrl) { URL.revokeObjectURL(blobUrl); blobUrl = null; }
      try { fileInput.value = ''; } catch (e) { }
      if (initialSrc) {
        showPreview(initialSrc, false);
      } else {
        if (previewWrap) previewWrap.classList.add('d-none');
        if (placeholder) placeholder.classList.remove('d-none');
        updateViewButton('', false);
      }
    }

    fileInput.addEventListener('change', function (e) {
      const f = e.target.files && e.target.files[0];
      if (!f) return;
      if (blobUrl) { URL.revokeObjectURL(blobUrl); blobUrl = null; }
      blobUrl = URL.createObjectURL(f);
      showPreview(blobUrl, true);
    });

    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        resetPreview();
      });
    }
  })();
</script>




<?php include 'includes/footer.php'; ?>