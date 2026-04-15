<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Checkout';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$errors = [];

$nama = '';
if (isset($_POST['nama_pembeli'])) {
    $nama = trim((string) $_POST['nama_pembeli']);
}

$telepon = '';
if (isset($_POST['telepon'])) {
    $telepon = trim((string) $_POST['telepon']);
}

$alamat = '';
if (isset($_POST['alamat'])) {
    $alamat = trim((string) $_POST['alamat']);
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
    if ($qty < 1) {
        continue;
    }

    $harga = (float) $p['harga'];
    $subtotal = $qty * $harga;
    $total = $total + $subtotal;

    $items[] = [
        'produk' => $p,
        'qty' => $qty,
        'subtotal' => $subtotal,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if ($telepon === '') {
        $errors[] = 'Telepon wajib diisi.';
    }
    if ($alamat === '') {
        $errors[] = 'Alamat wajib diisi.';
    }
    if (count($items) === 0) {
        $errors[] = 'Keranjang kosong atau stok tidak mencukupi.';
    }

    if (count($errors) === 0) {
        try {
            $pdo->beginTransaction();

            $sqlKunci = 'SELECT id, stok, harga FROM produk WHERE id = ? FOR UPDATE';
            $stmtKunci = $pdo->prepare($sqlKunci);

            $totalCheck = 0;
            $locked = [];

            foreach ($_SESSION['cart'] as $pidRaw => $qtyRaw) {
                $produkId = (int) $pidRaw;
                $qty = (int) $qtyRaw;
                if ($qty < 1) {
                    continue;
                }

                $stmtKunci->execute([$produkId]);
                $p = $stmtKunci->fetch();

                $stokSekarang = $p ? (int) $p['stok'] : 0;
                if (!$p || $qty > $stokSekarang) {
                    throw new Exception('Stok produk berubah. Silakan perbarui keranjang.');
                }

                $qtyFinal = $qty;
                if ($qtyFinal > $stokSekarang) {
                    $qtyFinal = $stokSekarang;
                }

                $harga = (float) $p['harga'];
                $locked[] = [
                    'id' => $produkId,
                    'qty' => $qtyFinal,
                    'harga' => $harga,
                ];
                $totalCheck = $totalCheck + ($qtyFinal * $harga);
            }

            if (count($locked) === 0) {
                throw new Exception('Tidak ada item valid.');
            }

            $sqlPesanan = 'INSERT INTO pesanan (nama_pembeli, telepon, alamat, total, status) VALUES (?,?,?,?,?)';
            $stmtPesanan = $pdo->prepare($sqlPesanan);
            $stmtPesanan->execute([$nama, $telepon, $alamat, $totalCheck, 'menunggu']);
            $pesananId = (int) $pdo->lastInsertId();

            $sqlDetail = 'INSERT INTO detail_pesanan (pesanan_id, produk_id, qty, harga_satuan, subtotal) VALUES (?,?,?,?,?)';
            $stmtDetail = $pdo->prepare($sqlDetail);

            $sqlKurangiStok = 'UPDATE produk SET stok = stok - ? WHERE id = ?';
            $stmtKurangiStok = $pdo->prepare($sqlKurangiStok);

            foreach ($locked as $row) {
                $sub = $row['qty'] * $row['harga'];
                $stmtDetail->execute([$pesananId, $row['id'], $row['qty'], $row['harga'], $sub]);
                $stmtKurangiStok->execute([$row['qty'], $row['id']]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            $_SESSION['checkout_ok'] = $pesananId;
            header('Location: ' . url('checkout.php'));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

$okId = null;
if (isset($_SESSION['checkout_ok'])) {
    $okId = $_SESSION['checkout_ok'];
    unset($_SESSION['checkout_ok']);
}

require __DIR__ . '/includes/header.php';
?>

<?php if ($okId): ?>
  <div class="alert alert-ok">
    Pesanan berhasil dikirim. Nomor pesanan: <strong>#<?= (int) $okId ?></strong>.
    Terima kasih telah berbelanja.
  </div>
  <p><a class="btn" href="<?= htmlspecialchars(url('index.php')) ?>">Kembali belanja</a></p>
<?php else: ?>

<h1>Checkout</h1>

<?php if (count($errors) > 0): ?>
  <div class="alert alert-err">
    <?php foreach ($errors as $e): ?>
      <div><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (count($items) === 0): ?>
  <p class="muted">Keranjang kosong. <a href="<?= htmlspecialchars(url('index.php')) ?>">Belanja dulu</a></p>
<?php else: ?>

  <h2>Ringkasan</h2>
  <table>
    <thead>
      <tr><th>Produk</th><th>Jumlah</th><th>Subtotal</th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['produk']['nama']) ?></td>
          <td><?= (int) $row['qty'] ?></td>
          <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p><strong>Total: Rp <?= number_format($total, 0, ',', '.') ?></strong></p>

  <h2>Data pembeli</h2>
  <form method="post">
    <div class="form-group">
      <label for="nama_pembeli">Nama</label>
      <input type="text" id="nama_pembeli" name="nama_pembeli" required maxlength="120"
             value="<?= htmlspecialchars($nama) ?>">
    </div>
    <div class="form-group">
      <label for="telepon">Telepon</label>
      <input type="text" id="telepon" name="telepon" required maxlength="32"
             value="<?= htmlspecialchars($telepon) ?>">
    </div>
    <div class="form-group">
      <label for="alamat">Alamat</label>
      <textarea id="alamat" name="alamat" required maxlength="2000"><?= htmlspecialchars($alamat) ?></textarea>
    </div>
    <button type="submit" class="btn">Konfirmasi pesanan</button>
    <a class="btn btn-ghost" href="<?= htmlspecialchars(url('keranjang.php')) ?>">Kembali ke keranjang</a>
  </form>
<?php endif; ?>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
