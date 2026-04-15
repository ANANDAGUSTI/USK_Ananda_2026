<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Login';

$sudahLogin = auth_user();
if ($sudahLogin !== null) {
    $role = auth_role();
    if ($role === 'admin') {
        redirect('admin/index.php');
    }
    if ($role === 'petugas') {
        redirect('petugas/index.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
    $passwordInput = isset($_POST['password']) ? (string) $_POST['password'] : '';

    $sqlUser = 'SELECT id, username, password, role FROM pengguna WHERE username = ?';
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$usernameInput]);
    $dataUser = $stmtUser->fetch();

    $passwordBenar = false;
    if ($dataUser && isset($dataUser['password'])) {
        $passwordBenar = password_verify($passwordInput, $dataUser['password']);
    }

    if ($passwordBenar) {
        $_SESSION['user'] = [
            'id' => (int) $dataUser['id'],
            'username' => $dataUser['username'],
            'role' => $dataUser['role'],
        ];
        if ($dataUser['role'] === 'admin') {
            redirect('admin/index.php');
        }
        redirect('petugas/index.php');
    }

    $error = 'Username atau password salah.';
}

require __DIR__ . '/includes/header.php';
?>

<h1>Login Admin / Petugas</h1>
<p class="muted">Pembeli tidak perlu login — halaman ini hanya untuk pengelola.</p>

<?php if ($error): ?>
  <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" style="max-width:360px;">
  <div class="form-group">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required autocomplete="username" autofocus>
  </div>
  <div class="form-group">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required autocomplete="current-password">
  </div>
  <button type="submit" class="btn">Masuk</button>
  <a class="btn btn-ghost" href="<?= htmlspecialchars(url('index.php')) ?>">Kembali ke toko</a>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
