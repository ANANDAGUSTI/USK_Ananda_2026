<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_role('petugas');

$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

if ($id <= 0) {
    $_SESSION['flash_err'] = 'ID pesanan tidak valid.';
    redirect('petugas/index.php');
}

$sqlCek = 'SELECT status FROM pesanan WHERE id = ?';
$stmtCek = $pdo->prepare($sqlCek);
$stmtCek->execute([$id]);
$row = $stmtCek->fetch();

if (!$row) {
    $_SESSION['flash_err'] = 'Pesanan tidak ditemukan.';
    redirect('petugas/index.php');
}

$statusSekarang = '';
if (isset($row['status'])) {
    $statusSekarang = $row['status'];
}

if ($statusSekarang !== 'selesai') {
    $_SESSION['flash_err'] = 'Pesanan hanya bisa dihapus jika statusnya Selesai.';
    redirect('petugas/index.php');
}

$sqlHapus = 'DELETE FROM pesanan WHERE id = ?';
$stmtHapus = $pdo->prepare($sqlHapus);
$stmtHapus->execute([$id]);
$_SESSION['flash_ok'] = 'Pesanan berhasil dihapus.';
redirect('petugas/index.php');

