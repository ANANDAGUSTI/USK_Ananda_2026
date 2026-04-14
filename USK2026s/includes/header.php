<?php
declare(strict_types=1);
if (!isset($pageTitle)) {
    $pageTitle = 'Kasir';
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — Kasir</title>
  <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
  <div class="inner">
    <a class="brand" href="<?= htmlspecialchars(url('index.php')) ?>">Kasir</a>
    <nav class="nav">
      <a href="<?= htmlspecialchars(url('index.php')) ?>">Toko</a>
      <a href="<?= htmlspecialchars(url('keranjang.php')) ?>">Keranjang</a>
      <?php if (auth_user()): ?>
        <?php if (auth_role() === 'admin'): ?>
          <a href="<?= htmlspecialchars(url('admin/index.php')) ?>">Admin</a>
        <?php endif; ?>
        <?php if (auth_role() === 'petugas'): ?>
          <a href="<?= htmlspecialchars(url('petugas/index.php')) ?>">Transaksi</a>
        <?php endif; ?>
        <span class="muted"><?= htmlspecialchars(auth_user()['username'] ?? '') ?></span>
        <a href="<?= htmlspecialchars(url('logout.php')) ?>">Keluar</a>
      <?php else: ?>
        <a href="<?= htmlspecialchars(url('login.php')) ?>">Login Petugas/Admin</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="wrap">
