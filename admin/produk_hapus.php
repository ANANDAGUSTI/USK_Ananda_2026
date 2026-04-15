<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

if ($id > 0) {
    $sqlCek = 'SELECT COUNT(*) FROM detail_pesanan WHERE produk_id = ?';
    $stmtCek = $pdo->prepare($sqlCek);
    $stmtCek->execute([$id]);
    $jumlahDetail = (int) $stmtCek->fetchColumn();

    if ($jumlahDetail > 0) {
        $_SESSION['flash_err'] = 'Produk tidak bisa dihapus karena pernah masuk pesanan.';
    } else {
        $sqlHapus = 'DELETE FROM produk WHERE id = ?';
        $stmtHapus = $pdo->prepare($sqlHapus);
        $stmtHapus->execute([$id]);
    }
}

redirect('admin/produk.php');
