<?php include 'includes/header.php'; $msg=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']); $price=(int)$_POST['price']; $old=(int)$_POST['old_price'];
  $discount=(int)$_POST['discount']; $stock=(int)$_POST['stock']; $rating=(int)$_POST['rating'];
  $imgPath=null;
  if(!empty($_FILES['image']['name'])){
    $ext=strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $fname='uploads/p_'.time().'_'.bin2hex(random_bytes(3)).'.'.$ext;
    move_uploaded_file($_FILES['image']['tmp_name'], $fname);
    $imgPath=$fname;
  }
  $stmt=$con->prepare("INSERT INTO products(name,image,price,old_price,discount,rating,stock) VALUES(?,?,?,?,?,?,?)");
  $stmt->bind_param("ssiiiii",$name,$imgPath,$price,$old,$discount,$rating,$stock);
  $ok=$stmt->execute(); $msg=$ok?"Product added!":"Error: ".$con->error;
}
?>
<h4 class="mb-3">Add Product</h4>
<?php if($msg):?><div class="alert alert-info"><?=$msg?></div><?php endif;?>
<form method="post" enctype="multipart/form-data" class="row g-3">
  <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">Price</label><input type="number" name="price" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">Old Price</label><input type="number" name="old_price" class="form-control"></div>
  <div class="col-md-3"><label class="form-label">Discount %</label><input type="number" name="discount" class="form-control" value="0"></div>
  <div class="col-md-3"><label class="form-label">Rating (0-5)</label><input type="number" name="rating" class="form-control" value="0" min="0" max="5"></div>
  <div class="col-md-3"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" value="0"></div>
  <div class="col-md-12"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
  <div class="col-12"><button class="btn btn-dark">Save</button> <a href="products.php" class="btn btn-outline-secondary">Back</a></div>
</form>
<?php include 'includes/footer.php'; ?>
