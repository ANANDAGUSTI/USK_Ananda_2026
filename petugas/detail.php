<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('petugas');

$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}
$pageTitle = 'Detail Pesanan #' . $id;

$sqlPesanan = 'SELECT * FROM pesanan WHERE id = ?';
$stmtPesanan = $pdo->prepare($sqlPesanan);
$stmtPesanan->execute([$id]);
$pesanan = $stmtPesanan->fetch();
if (!$pesanan) {
    redirect('petugas/index.php');
}

$errors = [];
$labelStatus = [
    'menunggu' => 'Menunggu',
    'diproses' => 'Diproses',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];

$pilihanStatus = array_keys($labelStatus);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = (string) $_POST['status'];
    if (!in_array($newStatus, $pilihanStatus, true)) {
        $errors[] = 'Status tidak valid.';
    }
    if (count($errors) === 0) {
        $sqlUpdate = 'UPDATE pesanan SET status = ? WHERE id = ?';
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$newStatus, $id]);
        redirect('petugas/detail.php?id=' . $id);
    }
}

$sqlDetail = 'SELECT d.qty, d.harga_satuan, d.subtotal, pr.nama AS nama_produk
     FROM detail_pesanan d
     JOIN produk pr ON pr.id = d.produk_id
     WHERE d.pesanan_id = ?';
$stmtDetail = $pdo->prepare($sqlDetail);
$stmtDetail->execute([$id]);
$details = $stmtDetail->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h1>Pesanan #<?= (int) $pesanan['id'] ?></h1>

<?php if (count($errors) > 0): ?>
  <div class="alert alert-err"><?= htmlspecialchars($errors[0]) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:1rem;">
  <h2>Data pembeli</h2>
  <p><strong>Nama:</strong> <?= htmlspecialchars($pesanan['nama_pembeli']) ?></p>
  <p><strong>Telepon:</strong> <?= htmlspecialchars($pesanan['telepon']) ?></p>
  <p><strong>Alamat:</strong><br><?= nl2br(htmlspecialchars($pesanan['alamat'])) ?></p>
  <p><strong>Waktu:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($pesanan['created_at']))) ?></p>
</div>

<h2>Item</h2>
<table>
  <thead>
    <tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
  </thead>
  <tbody>
    <?php foreach ($details as $d): ?>
      <tr>
        <td><?= htmlspecialchars($d['nama_produk']) ?></td>
        <td><?= (int) $d['qty'] ?></td>
        <td>Rp <?= number_format((float) $d['harga_satuan'], 0, ',', '.') ?></td>
        <td>Rp <?= number_format((float) $d['subtotal'], 0, ',', '.') ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p><strong>Total: Rp <?= number_format((float) $pesanan['total'], 0, ',', '.') ?></strong></p>

<h2>Ubah status</h2>
<form method="post" class="stack">
  <label>
    Status:
    <select name="status">
      <?php foreach ($labelStatus as $k => $lab): ?>
        <option value="<?= htmlspecialchars($k) ?>" <?= $pesanan['status'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="btn">Simpan status</button>
</form>

<p><a href="<?= htmlspecialchars(url('petugas/index.php')) ?>">← Daftar transaksi</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
