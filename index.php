<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Belanja';

$method = $_SERVER['REQUEST_METHOD'];
$isTambahKeKeranjang = $method === 'POST' && isset($_POST['tambah'], $_POST['produk_id']);

if ($isTambahKeKeranjang) {
    $produkId = (int) $_POST['produk_id'];
    $jumlahMinta = (int) ($_POST['qty'] ?? 1);
    if ($jumlahMinta < 1) {
        $jumlahMinta = 1;
    }

    $sqlCekStok = 'SELECT id, stok FROM produk WHERE id = ?';
    $stmtCek = $pdo->prepare($sqlCekStok);
    $stmtCek->execute([$produkId]);
    $dataProduk = $stmtCek->fetch();

    $stokTersedia = $dataProduk ? (int) $dataProduk['stok'] : 0;
    if ($dataProduk && $stokTersedia > 0) {
        $jumlahMasuk = $jumlahMinta;
        if ($jumlahMasuk > $stokTersedia) {
            $jumlahMasuk = $stokTersedia;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $jumlahDiKeranjang = 0;
        if (isset($_SESSION['cart'][$produkId])) {
            $jumlahDiKeranjang = (int) $_SESSION['cart'][$produkId];
        }

        $jumlahBaru = $jumlahDiKeranjang + $jumlahMasuk;
        if ($jumlahBaru > $stokTersedia) {
            $jumlahBaru = $stokTersedia;
        }
        $_SESSION['cart'][$produkId] = $jumlahBaru;
    }

    header('Location: ' . url('index.php'));
    exit;
}

$sqlProduk = 'SELECT id, nama, deskripsi, harga, stok FROM produk WHERE stok > 0 ORDER BY nama';
$produk = $pdo->query($sqlProduk)->fetchAll();

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
