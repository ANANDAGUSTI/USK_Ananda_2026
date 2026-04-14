<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Belanja';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'], $_POST['produk_id'])) {
    $pid = (int) $_POST['produk_id'];
    $qty = max(1, (int) ($_POST['qty'] ?? 1));
    $st = $pdo->prepare('SELECT id, stok FROM produk WHERE id = ?');
    $st->execute([$pid]);
    $row = $st->fetch();
    if ($row && $row['stok'] > 0) {
        $add = min($qty, (int) $row['stok']);
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $cur = (int) ($_SESSION['cart'][$pid] ?? 0);
        $_SESSION['cart'][$pid] = min($cur + $add, (int) $row['stok']);
    }
    header('Location: ' . url('index.php'));
    exit;
}

$produk = $pdo->query('SELECT id, nama, deskripsi, harga, stok FROM produk WHERE stok > 0 ORDER BY nama')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1>Produk</h1>
<p class="muted">Pilih produk dan tambahkan ke keranjang. Anda tidak perlu membuat akun.</p>

<div class="grid">
  <?php foreach ($produk as $p): ?>
    <div class="card">
      <h3><?= htmlspecialchars($p['nama']) ?></h3>
      <?php if ($p['deskripsi']): ?>
        <p class="muted"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>
      <?php endif; ?>
      <p class="price">Rp <?= number_format((float) $p['harga'], 0, ',', '.') ?></p>
      <p class="muted">Stok: <?= (int) $p['stok'] ?></p>
      <form method="post" class="stack">
        <input type="hidden" name="produk_id" value="<?= (int) $p['id'] ?>">
        <label class="muted" style="display:flex;align-items:center;gap:0.35rem;">
          Jumlah
          <input type="number" name="qty" value="1" min="1" max="<?= (int) $p['stok'] ?>" style="width:4rem;">
        </label>
        <button type="submit" name="tambah" value="1" class="btn">Tambah ke keranjang</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<?php if (empty($produk)): ?>
  <p class="muted">Belum ada produk tersedia.</p>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
