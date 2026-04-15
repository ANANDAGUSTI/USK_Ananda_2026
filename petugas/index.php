<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('petugas');

$pageTitle = 'Transaksi';

$flashErr = null;
if (isset($_SESSION['flash_err'])) {
    $flashErr = $_SESSION['flash_err'];
    unset($_SESSION['flash_err']);
}

$flashOk = null;
if (isset($_SESSION['flash_ok'])) {
    $flashOk = $_SESSION['flash_ok'];
    unset($_SESSION['flash_ok']);
}

$status = '';
if (isset($_GET['status'])) {
    $status = $_GET['status'];
}

$where = '';
$params = [];
$listStatus = ['menunggu', 'diproses', 'selesai', 'dibatalkan'];
if (in_array($status, $listStatus, true)) {
    $where = 'WHERE p.status = ?';
    $params[] = $status;
}

$sqlList = "SELECT p.id, p.nama_pembeli, p.telepon, p.total, p.status, p.created_at
        FROM pesanan p
        $where
        ORDER BY p.created_at DESC";
$stmtList = $pdo->prepare($sqlList);
$stmtList->execute($params);
$rows = $stmtList->fetchAll();

$labelStatus = [
    'menunggu' => 'Menunggu',
    'diproses' => 'Diproses',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];

require __DIR__ . '/../includes/header.php';
?>

<h1>Data transaksi & pembeli</h1>
<p class="muted">Kelola status pesanan dan lihat data pembeli.</p>

<?php if (!empty($flashErr)): ?>
  <div class="alert alert-err"><?= htmlspecialchars($flashErr) ?></div>
<?php endif; ?>
<?php if (!empty($flashOk)): ?>
  <div class="alert alert-ok"><?= htmlspecialchars($flashOk) ?></div>
<?php endif; ?>

<p class="stack">
  <a href="<?= htmlspecialchars(url('petugas/index.php')) ?>" class="btn btn-sm <?= $status === '' ? '' : 'btn-ghost' ?>">Semua</a>
  <?php foreach ($listStatus as $s): ?>
    <a href="<?= htmlspecialchars(url('petugas/index.php?status=' . $s)) ?>"
       class="btn btn-sm <?= $status === $s ? '' : 'btn-ghost' ?>"><?= $labelStatus[$s] ?></a>
  <?php endforeach; ?>
</p>

<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Tanggal</th>
      <th>Pembeli</th>
      <th>Telepon</th>
      <th>Total</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>#<?= (int) $r['id'] ?></td>
        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
        <td><?= htmlspecialchars($r['nama_pembeli']) ?></td>
        <td><?= htmlspecialchars($r['telepon']) ?></td>
        <td>Rp <?= number_format((float) $r['total'], 0, ',', '.') ?></td>
        <td><span class="badge"><?= $labelStatus[$r['status']] ?? $r['status'] ?></span></td>
        <td class="stack">
          <a class="btn btn-sm" href="<?= htmlspecialchars(url('petugas/detail.php?id=' . (int) $r['id'])) ?>">Detail</a>
          <?php if (($r['status'] ?? '') === 'selesai'): ?>
            <a class="btn btn-sm btn-danger"
               href="<?= htmlspecialchars(url('petugas/pesanan_hapus.php?id=' . (int) $r['id'])) ?>"
               onclick="return confirm('Hapus pesanan ini?');">Hapus</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if (empty($rows)): ?>
  <p class="muted">Belum ada pesanan.</p>
<?php endif; ?>

<p><a href="<?= htmlspecialchars(url('index.php')) ?>">Lihat toko</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
