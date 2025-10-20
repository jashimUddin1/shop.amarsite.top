<?php include 'includes/header.php';
if(isset($_GET['del']) && is_super()){
  $id=(int)$_GET['del'];
  $con->query("DELETE FROM users WHERE id=$id");
  echo "<div class='alert alert-success'>User deleted.</div>";
}
$res=$con->query("SELECT id,name,email,role,created_at FROM users ORDER BY id DESC");
?>
<h4 class="mb-3">Users</h4>
<table class="table table-striped align-middle">
  <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr></thead>
  <tbody>
  <?php while($u=$res->fetch_assoc()): ?>
    <tr>
      <td><?=$u['id']?></td><td><?=esc($u['name'])?></td><td><?=esc($u['email'])?></td>
      <td><?=$u['role']?></td><td><?=$u['created_at']?></td>
      <td class="text-end">
        <?php if(is_super()): ?>
          <a class="btn btn-sm btn-outline-danger" href="?del=<?=$u['id']?>" onclick="return confirm('Delete user?')">Delete</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php include 'includes/footer.php'; ?>
