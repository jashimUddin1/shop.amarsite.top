<?php // admin/settings_advance.php
session_start();
include 'includes/header.php';
require_once "../db/dbcon.php";

/** Load all sections */
$sections = [];
if ($res = $con->query("SELECT * FROM site_sections ORDER BY id ASC")) {
  while ($r = $res->fetch_assoc()) {
    $sections[$r['section']] = json_decode($r['data'], true) ?: [];
  }
}

/** Save raw JSON per section */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adv']) && is_array($_POST['adv'])) {
  foreach ($_POST['adv'] as $sectionKey => $jsonString) {
    if (!is_string($jsonString)) continue;
    $decoded = json_decode($jsonString, true);
    if ($decoded === null && trim($jsonString) !== '') {
      $_SESSION['error_'.$sectionKey] = "❌ Invalid JSON for '{$sectionKey}'. Not saved.";
      continue;
    }
    $json = json_encode($decoded ?: new stdClass(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $stmt = $con->prepare("INSERT INTO site_sections (section, data) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE data = VALUES(data)");
    $stmt->bind_param("ss", $sectionKey, $json);
    $stmt->execute();
    $stmt->close();
    $_SESSION['ok_'.$sectionKey] = "✅ '{$sectionKey}' updated.";
    $sections[$sectionKey] = $decoded ?: [];
  }
  $_SESSION['success'] = "✅ Advanced settings processed.";
  header("Location: settings_advance.php");
  exit;
}

/** Helpers */
function pretty($arr){ return empty($arr) ? "{\n}" : json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); }

?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="mb-0">Advanced Settings</h3>
  <?php if(!empty($_SESSION['success'])): ?>
    <div class="alert alert-success py-2 px-3 mb-0"><?=htmlspecialchars($_SESSION['success']); unset($_SESSION['success']);?></div>
  <?php endif; ?>
</div>

<form method="post">
<div class="accordion" id="accordionSettings">
<?php
  // Topbar added to priority
  $priority = ['topbar','header','nav','slider','banner','product_card','footer_nav','footer'];
  $keys = array_unique(array_merge($priority, array_keys($sections)));
  $isFirst = true;
  foreach ($keys as $key):
    if ($key === '' || $key === null) continue;
    if (!array_key_exists($key, $sections)) $sections[$key] = [];
    $collapseId = 'acc_' . preg_replace('/[^a-z0-9_]/i', '', $key);
    $headingId  = 'heading_' . $collapseId;
    $show        = $isFirst ? 'show'   : '';
    $collapsed   = $isFirst ? ''       : 'collapsed';
    $ariaExpanded= $isFirst ? 'true'   : 'false';
?>
  <div class="accordion-item mb-2">
    <h2 class="accordion-header" id="<?=$headingId?>">
      <button class="accordion-button <?=$collapsed?>" type="button"
              data-bs-toggle="collapse"
              data-bs-target="#<?=$collapseId?>"
              aria-expanded="<?=$ariaExpanded?>"
              aria-controls="<?=$collapseId?>">
        <?= htmlspecialchars(ucfirst(str_replace('_',' ', $key))) ?>
      </button>
    </h2>
    <div id="<?=$collapseId?>" class="accordion-collapse collapse <?=$show?>"
         aria-labelledby="<?=$headingId?>" data-bs-parent="#accordionSettings">
      <div class="accordion-body bg-light">
        <?php if(!empty($_SESSION['error_'.$key])): ?>
          <div class="alert alert-danger py-2 px-3 mb-3"><?=$_SESSION['error_'.$key]; unset($_SESSION['error_'.$key]);?></div>
        <?php endif; ?>
        <?php if(!empty($_SESSION['ok_'.$key])): ?>
          <div class="alert alert-success py-2 px-3 mb-3"><?=$_SESSION['ok_'.$key]; unset($_SESSION['ok_'.$key]);?></div>
        <?php endif; ?>

        <label class="form-label">Raw JSON for <code><?=htmlspecialchars($key)?></code></label>
        <textarea name="adv[<?=htmlspecialchars($key)?>]" rows="12" class="form-control font-monospace"
                  spellcheck="false"><?=htmlspecialchars(pretty($sections[$key]))?></textarea>
      </div>
    </div>
  </div>
<?php
    $isFirst = false;
  endforeach;
?>
</div>

<div class="text-end mt-4">
  <button class="btn btn-dark px-4 py-2"><i class="bi bi-save2 me-2"></i>Save All Changes</button>
</div>
</form>

<?php include 'includes/footer.php'; ?>
