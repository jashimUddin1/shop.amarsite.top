<?php include 'includes/header.php';
$id=(int)($_GET['id']??0);
$order=$con->query("SELECT o.*,u.name AS uname FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id=$id")->fetch_assoc();
if(!$order){ echo "<div class='alert alert-danger'>Order not found.</div>"; include 'includes/footer.php'; exit; }

$items=$con->query("SELECT oi.*,p.name,p.image FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=$id");
?>
<h4>Order #<?=$order['id']?></h4>
<p class="text-muted">Customer: <?=esc($order['uname']??'Guest')?> | Status: <strong><?=$order['status']?></strong></p>

<table class="table table-bordered align-middle">
  <thead class="table-light"><tr><th>Image</th><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
  <tbody>
  <?php $total=0; while($i=$items->fetch_assoc()): $sub=$i['price']*$i['qty']; $total+=$sub; ?>
  <tr>
    <td><img src="<?=esc($i['image'])?>" style="width:60px;height:60px;object-fit:cover"></td>
    <td><?=esc($i['name'])?></td>
    <td><?=$i['qty']?></td>
    <td>৳<?=$i['price']?></td>
    <td>৳<?=$sub?></td>
  </tr>
  <?php endwhile; ?>
  </tbody>
  <tfoot><tr><th colspan="4" class="text-end">Total</th><th>৳<?=$total?></th></tr></tfoot>
</table>

<a href="orders.php" class="btn btn-outline-secondary">← Back</a>
<?php include 'includes/footer.php'; ?>
