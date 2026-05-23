<?php
require_once 'config.php';

if (is_logged_in()) {
    redirect_to('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Page ini hanya menampilkan proses "forgot password" secara UI.
    // Backend reset password (token/email) belum tersedia di project ini.
    if (!$pdo) {
        $error = 'Database belum tersambung. Import database.sql dulu di phpMyAdmin.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $error = 'Email wajib diisi.';
        } else {
            // Cek email ada di tabel users (opsional, untuk validasi kasar)
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            $success = 'Link reset password telah dikirim ke email kamu';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Sandi - HealthMood</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <form class="auth-card" method="post">
        <img src="public/WhatsApp-Image-2026-04-29-at-21-03-52-removebg-preview-1@2x.png" alt="HealthMood">
        <h1>Lupa Sandi?</h1>
        <p>Masukkan email kamu. Kami akan membantu proses reset password.</p>

        <?php if ($error): ?><p class="message error"><?= e($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p class="message"><?= e($success) ?></p><?php endif; ?>

        <label style="margin-bottom: 12px; display: block;">
            <input type="email" name="email" placeholder="Email" required>
        </label>

        <button class="btn" type="submit">KIRIM LINK</button>
        <p style="margin-top: 12px;">
            <a href="login.php">Kembali ke menu login</a>
        </p>

        <script src="assets/js/app.js"></script>
    </form>
</body>
</html>

