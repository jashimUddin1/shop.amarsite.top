<?php // includes/session_modal.php


$alerts = [];

if (isset($_SESSION['status'])) {
    $alerts[] = ['type' => 'success', 'message' => $_SESSION['status']];
    unset($_SESSION['status']);
}
if (isset($_SESSION['success'])) {
    $alerts[] = ['type' => 'success', 'message' => $_SESSION['success']];
    unset($_SESSION['success']);
}
if (isset($_SESSION['warning'])) {
    $alerts[] = ['type' => 'warning', 'message' => $_SESSION['warning']];
    unset($_SESSION['warning']);
}
if (isset($_SESSION['danger'])) {
    $alerts[] = ['type' => 'danger', 'message' => $_SESSION['danger']];
    unset($_SESSION['danger']);
}
 if (!empty($alerts)): ?>
  <!-- ✅ Bootstrap Modal -->
  <div class="modal fade" id="sessionModal" tabindex="-1" aria-labelledby="sessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="sessionModalLabel">📢 সিস্টেম বার্তা</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <?php foreach ($alerts as $alert): ?>
            <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show" role="alert">
              <?= $alert['message']; ?>
            </div>
          <?php endforeach; ?>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        </div>
        
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var sessionModal = new bootstrap.Modal(document.getElementById('sessionModal'));
      sessionModal.show();
    });
  </script>
<?php endif; ?>