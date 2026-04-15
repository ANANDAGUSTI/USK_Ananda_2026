<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

if ($id > 0) {
    $pageTitle = 'Edit Produk';
} else {
    $pageTitle = 'Tambah Produk';
}

$row = ['nama' => '', 'deskripsi' => '', 'harga' => '', 'stok' => '0'];
if ($id > 0) {
    $sqlEdit = 'SELECT id, nama, deskripsi, harga, stok FROM produk WHERE id = ?';
    $stmtEdit = $pdo->prepare($sqlEdit);
    $stmtEdit->execute([$id]);
    $row = $stmtEdit->fetch();
    if (!$row) {
        redirect('admin/produk.php');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = isset($_POST['nama']) ? trim((string) $_POST['nama']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? trim((string) $_POST['deskripsi']) : '';
    $hargaRaw = isset($_POST['harga']) ? (string) $_POST['harga'] : '0';
    $harga = (float) str_replace(',', '.', $hargaRaw);
    $stok = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;

    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if ($harga < 0) {
        $errors[] = 'Harga tidak valid.';
    }
    if ($stok < 0) {
        $errors[] = 'Stok tidak valid.';
    }

    if (count($errors) === 0) {
        $deskripsiDb = $deskripsi;
        if ($deskripsiDb === '') {
            $deskripsiDb = null;
        }

        if ($id > 0) {
            $sqlUpdate = 'UPDATE produk SET nama=?, deskripsi=?, harga=?, stok=? WHERE id=?';
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([$nama, $deskripsiDb, $harga, $stok, $id]);
        } else {
            $sqlInsert = 'INSERT INTO produk (nama, deskripsi, harga, stok) VALUES (?,?,?,?)';
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([$nama, $deskripsiDb, $harga, $stok]);
        }
        redirect('admin/produk.php');
    }

    $row = [
        'nama' => $nama,
        'deskripsi' => $deskripsi,
        'harga' => $harga,
        'stok' => $stok,
    ];
}

require __DIR__ . '/../includes/header.php';
?>

<h1><?php echo $id > 0 ? 'Edit' : 'Tambah'; ?> Produk</h1>

<?php if (count($errors) > 0): ?>
  <div class="alert alert-err">
    <?php foreach ($errors as $e): ?>
      <div><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post">
  <div class="form-group">
    <label for="nama">Nama</label>
    <input type="text" id="nama" name="nama" required maxlength="200"
           value="<?= htmlspecialchars((string) $row['nama']) ?>">
  </div>
  <div class="form-group">
    <label for="deskripsi">Deskripsi</label>
    <textarea id="deskripsi" name="deskripsi"><?= htmlspecialchars((string) ($row['deskripsi'] ?? '')) ?></textarea>
  </div>
  <div class="form-group">
    <label for="harga">Harga (Rp)</label>
    <input type="number" id="harga" name="harga" required min="0" step="0.01"
           value="<?= htmlspecialchars((string) $row['harga']) ?>">
  </div>
  <div class="form-group">
    <label for="stok">Stok</label>
    <input type="number" id="stok" name="stok" required min="0"
           value="<?= htmlspecialchars((string) $row['stok']) ?>">
  </div>
  <button type="submit" class="btn">Simpan</button>
  <a class="btn btn-ghost" href="<?= htmlspecialchars(url('admin/produk.php')) ?>">Batal</a>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
