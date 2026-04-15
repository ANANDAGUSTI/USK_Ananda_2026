<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$pageTitle = 'Dashboard Admin';

$countProduk = (int) $pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn();
$countPesanan = (int) $pdo->query('SELECT COUNT(*) FROM pesanan')->fetchColumn();
$pending = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'menunggu'")->fetchColumn();

require __DIR__ . '/../includes/header.php';
?>

<h1>Dashboard Admin</h1>
<p class="muted">Kelola produk dari menu di bawah. Data transaksi dapat dilihat oleh petugas.</p>

<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));">
  <div class="card">
    <h2>Produk</h2>
    <p style="font-size:1.5rem;margin:0;"><?= $countProduk ?></p>
    <a class="btn" href="<?= htmlspecialchars(url('admin/produk.php')) ?>">Kelola produk</a>
  </div>
  <div class="card">
    <h2>Total pesanan</h2>
    <p style="font-size:1.5rem;margin:0;"><?= $countPesanan ?></p>
    <span class="muted"><?= $pending ?> menunggu</span>
  </div>
</div>

<p style="margin-top:1.5rem;"><a href="<?= htmlspecialchars(url('index.php')) ?>">Lihat toko</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
