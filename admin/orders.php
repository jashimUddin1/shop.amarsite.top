<?php include 'includes/header.php'; ?>
<h4 class="mb-3">Orders</h4>

<?php
if(isset($_POST['update_status'])){
  $oid=(int)$_POST['oid'];
  $status=$_POST['status'];
  $stmt=$con->prepare("UPDATE orders SET status=? WHERE id=?");
  $stmt->bind_param("si",$status,$oid);
  $stmt->execute();
  echo '<div class="alert alert-success">Order updated!</div>';
}

$res=$con->query("SELECT o.id,o.total,o.status,o.created_at,u.name AS user_name 
                  FROM orders o LEFT JOIN users u ON u.id=o.user_id 
                  ORDER BY o.id DESC");
?>
<table class="table table-hover align-middle">
  <thead class="table-light">
    <tr><th>#</th><th>User</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr>
  </thead>
  <tbody>
  <?php while($o=$res->fetch_assoc()): ?>
    <tr>
      <td><?=$o['id']?></td>
      <td><?=esc($o['user_name'] ?? 'Guest')?></td>
      <td>৳<?=number_format($o['total'])?></td>
      <td>
        <?php if(is_manager() || is_super()): ?>
          <form method="post" class="d-flex align-items-center gap-1">
            <input type="hidden" name="oid" value="<?=$o['id']?>">
            <select name="status" class="form-select form-select-sm w-auto">
              <?php foreach(['Pending','Shipped','Delivered','Cancelled'] as $st): ?>
                <option value="<?=$st?>" <?=$st==$o['status']?'selected':''?>><?=$st?></option>
              <?php endforeach; ?>
            </select>
            <button name="update_status" class="btn btn-sm btn-outline-dark">Save</button>
          </form>
        <?php else: ?>
          <span class="badge bg-secondary"><?=$o['status']?></span>
        <?php endif; ?>
      </td>
      <td><?=$o['created_at']?></td>
      <td><a href="order_view.php?id=<?=$o['id']?>" class="btn btn-sm btn-dark">View</a></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php include 'includes/footer.php'; ?>
