<?php // includes/session.php
if (session_status() === PHP_SESSION_NONE) session_start();

$types = ['status' => 'success', 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger'];

foreach ($types as $key => $class) {
    if (isset($_SESSION[$key])) {
        echo "
        <div class='alert alert-{$class} alert-dismissible fade show mt-2' role='alert'>
            {$_SESSION[$key]}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
        ";
        unset($_SESSION[$key]);
    }
}
?>
