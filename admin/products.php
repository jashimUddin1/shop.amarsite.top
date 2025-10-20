
<?php include 'includes/header.php'; include '../db/dbcon.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Manage Products</h4>
  <a href="product_add.php" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
</div>

<?php include "includes/session.php" ?>

<table class="table table-bordered table-hover align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>Image</th>
      <th>Name</th>
      <th>Price</th>
      <th>Stock</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php
  $res = $con->query("SELECT * FROM products");
  while($row = $res->fetch_assoc()):
  ?>
  <tr>
    <td><?=$row['id']?></td>
    <td><img src="<?=$row['image']?>" width="60"></td>
    <td><?=$row['name']?></td>
    <td>৳<?=$row['price']?></td>
    <td><?=$row['stock']?></td>
    <td>
      <a href="product_edit.php?id=<?=$row['id']?>" class="btn btn-sm btn-warning">Edit</a>
      <a href="product_delete.php?id=<?=$row['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
    </td>
  </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<?php include 'includes/footer.php'; ?>
