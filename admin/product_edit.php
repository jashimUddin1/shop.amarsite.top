<?php 
include 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$prod = $con->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
if (!$prod) { 
    echo "<div class='alert alert-danger'>Product not found.</div>"; 
    include 'includes/footer.php'; 
    exit; 
}

// --- Update Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $price    = (int)$_POST['price'];
    $old      = (int)$_POST['old_price'];
    $discount = (int)$_POST['discount'];
    $stock    = (int)$_POST['stock'];
    $rating   = (int)$_POST['rating'];
    $img      = $prod['image'];

    // --- File Upload Handling ---
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $fname = 'p_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $target = $uploadDir . $fname;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $img = 'uploads/' . $fname; // relative path for DB
        } else {
            $_SESSION['danger'] = "❌ Image upload failed! Check folder permission.";
        }
    }

    // --- Update DB ---
    $stmt = $con->prepare("UPDATE products SET name=?, image=?, price=?, old_price=?, discount=?, rating=?, stock=? WHERE id=?");
    $stmt->bind_param("ssiiiiii", $name, $img, $price, $old, $discount, $rating, $stock, $id);
    $ok = $stmt->execute();

    if ($ok) {
        $_SESSION['success'] = "✅ Product updated successfully!";
    } else {
        $_SESSION['danger'] = "❌ Error updating product: " . $con->error;
    }

    // redirect to avoid form resubmission
    header("Location: product_edit.php?id=$id");
    exit;
}

?>

<h4 class="mb-3">Edit Product #<?=$id?></h4>

<!-- 🧩 Show alert messages -->
<?php include "includes/session.php"; ?>

<form method="post" enctype="multipart/form-data" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Name</label>
    <input name="name" class="form-control" value="<?=esc($prod['name'])?>" required>
  </div>

  <div class="col-md-3">
    <label class="form-label">Price</label>
    <input type="number" name="price" class="form-control" value="<?=$prod['price']?>" required>
  </div>

  <div class="col-md-3">
    <label class="form-label">Old Price</label>
    <input type="number" name="old_price" class="form-control" value="<?=$prod['old_price']?>">
  </div>

  <div class="col-md-3">
    <label class="form-label">Discount %</label>
    <input type="number" name="discount" class="form-control" value="<?=$prod['discount']?>">
  </div>

  <div class="col-md-3">
    <label class="form-label">Rating</label>
    <input type="number" name="rating" class="form-control" value="<?=$prod['rating']?>" min="0" max="5">
  </div>

  <div class="col-md-3">
    <label class="form-label">Stock</label>
    <input type="number" name="stock" class="form-control" value="<?=$prod['stock']?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Image</label>
    <input type="file" name="image" class="form-control" accept="image/*">
    <?php if ($prod['image']): ?>
      <img src="<?=esc($prod['image'])?>" class="mt-2 rounded border" style="height:80px;object-fit:cover;">
    <?php endif; ?>
  </div>

  <div class="col-12">
    <button class="btn btn-dark">Update</button>
    <a href="products.php" class="btn btn-outline-secondary">Back</a>
  </div>
</form>

<?php include 'includes/footer.php'; ?>
