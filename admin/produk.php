<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$pageTitle = 'Kelola Produk';

$rows = $pdo->query('SELECT id, nama, harga, stok, created_at FROM produk ORDER BY id DESC')->fetchAll();

$flashErr = null;
if (isset($_SESSION['flash_err'])) {
    $flashErr = $_SESSION['flash_err'];
    unset($_SESSION['flash_err']);
}

require __DIR__ . '/../includes/header.php';
?>

<h1>Produk</h1>

<?php if (!empty($flashErr)): ?>
  <div class="alert alert-err"><?= htmlspecialchars($flashErr) ?></div>
<?php endif; ?>
<p><a class="btn" href="<?= htmlspecialchars(url('admin/produk_form.php')) ?>">Tambah produk</a></p>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Nama</th>
      <th>Harga</th>
      <th>Stok</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int) $r['id'] ?></td>
        <td><?= htmlspecialchars($r['nama']) ?></td>
        <td>Rp <?= number_format((float) $r['harga'], 0, ',', '.') ?></td>
        <td><?= (int) $r['stok'] ?></td>
        <td class="stack">
          <a class="btn btn-sm" href="<?= htmlspecialchars(url('admin/produk_form.php?id=' . (int) $r['id'])) ?>">Edit</a>
          <a class="btn btn-sm btn-danger" href="<?= htmlspecialchars(url('admin/produk_hapus.php?id=' . (int) $r['id'])) ?>"
             onclick="return confirm('Hapus produk ini?');">Hapus</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if (empty($rows)): ?>
  <p class="muted">Belum ada produk.</p>
<?php endif; ?>

<p><a href="<?= htmlspecialchars(url('admin/index.php')) ?>">← Dashboard</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
