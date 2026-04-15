<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Keranjang';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hapus_id'])) {
        $idHapus = (int) $_POST['hapus_id'];
        unset($_SESSION['cart'][$idHapus]);
    } elseif (isset($_POST['update'])) {
        $daftarQty = $_POST['qty'] ?? [];
        foreach ($daftarQty as $idRaw => $qtyRaw) {
            $produkId = (int) $idRaw;
            $qtyInput = (int) $qtyRaw;
            if ($qtyInput < 0) {
                $qtyInput = 0;
            }

            if ($qtyInput === 0) {
                unset($_SESSION['cart'][$produkId]);
            } else {
                $sqlStok = 'SELECT stok FROM produk WHERE id = ?';
                $stmtStok = $pdo->prepare($sqlStok);
                $stmtStok->execute([$produkId]);
                $barisStok = $stmtStok->fetch();
                $stokMax = 0;
                if ($barisStok && isset($barisStok['stok'])) {
                    $stokMax = (int) $barisStok['stok'];
                }

                $qtySimpan = $qtyInput;
                if ($qtySimpan > $stokMax) {
                    $qtySimpan = $stokMax;
                }
                $_SESSION['cart'][$produkId] = $qtySimpan;
            }
        }
    }

    header('Location: ' . url('keranjang.php'));
    exit;
}

$items = [];
$total = 0;

foreach ($_SESSION['cart'] as $pidRaw => $qtyRaw) {
    $produkId = (int) $pidRaw;
    $qty = (int) $qtyRaw;
    if ($qty < 1) {
        continue;
    }

    $sqlProduk = 'SELECT id, nama, harga, stok FROM produk WHERE id = ?';
    $stmtProduk = $pdo->prepare($sqlProduk);
    $stmtProduk->execute([$produkId]);
    $p = $stmtProduk->fetch();

    if (!$p) {
        unset($_SESSION['cart'][$produkId]);
        continue;
    }

    $stok = (int) $p['stok'];
    if ($qty > $stok) {
        $qty = $stok;
    }
    $_SESSION['cart'][$produkId] = $qty;

    $harga = (float) $p['harga'];
    $subtotal = $qty * $harga;
    $total = $total + $subtotal;

    $items[] = [
        'produk' => $p,
        'qty' => $qty,
        'subtotal' => $subtotal,
    ];
}

require __DIR__ . '/includes/header.php';
?>
<h1>Keranjang</h1>

<?php if (empty($items)): ?>
  <p class="muted">Keranjang kosong. <a href="<?= htmlspecialchars(url('index.php')) ?>">Belanja</a></p>
<?php else: ?>
  <form method="post">
    <table>
      <thead>
        <tr>
          <th>Produk</th>
          <th>Harga</th>
          <th>Jumlah</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $row): ?>
          <?php $p = $row['produk']; ?>
          <tr>
            <td><?= htmlspecialchars($p['nama']) ?></td>
            <td>Rp <?= number_format((float) $p['harga'], 0, ',', '.') ?></td>
            <td>
              <input type="number" name="qty[<?= (int) $p['id'] ?>]" value="<?= (int) $row['qty'] ?>"
                     min="0" max="<?= (int) $p['stok'] ?>" style="width:4rem;">
            </td>
            <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
            <td>
              <button type="submit" name="hapus_id" value="<?= (int) $p['id'] ?>" class="btn btn-ghost btn-sm">Hapus</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="stack" style="margin-top:1rem;">
      <button type="submit" name="update" value="1" class="btn btn-ghost">Perbarui keranjang</button>
      <a class="btn" href="<?= htmlspecialchars(url('checkout.php')) ?>">Checkout</a>
    </p>
  </form>
  <p><strong>Total: Rp <?= number_format($total, 0, ',', '.') ?></strong></p>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
