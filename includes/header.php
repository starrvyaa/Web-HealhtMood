<?php
$active = $active ?? '';
$title = $title ?? 'HealthMood';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - HealthMood</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=game-assets-folder-1">
</head>
<body class="<?= $active ? 'page-' . e($active) : 'page-default' ?>">
<header class="site-header">
    <a class="brand" href="index.php" aria-label="HealthMood Home">
        <img src="assets/images/game/WhatsApp-Image-2026-04-29-at-21-03-52-removebg-preview-1@2x.png" alt="Logo HealthMood">
        <span>HEALTHMOOD</span>
    </a>
    <nav class="main-nav" aria-label="Menu utama">
        <a class="<?= $active === 'mood' ? 'active' : '' ?>" href="<?= is_logged_in() ? 'mood.php' : 'login.php' ?>">MOOD</a>
        <a class="<?= $active === 'tidur' ? 'active' : '' ?>" href="<?= is_logged_in() ? 'tidur.php' : 'login.php' ?>">TIDUR</a>
        <a class="<?= $active === 'game' ? 'active' : '' ?>" href="<?= is_logged_in() ? 'game.php' : 'login.php' ?>">GAME</a>
    </nav>
    <div class="header-actions">
        <form class="search-box" role="search">
            <input type="search" placeholder="SEARCH" aria-label="Search">
            <img class="search-icon-img" src="assets/images/game/ic-baseline-search.svg" alt="" aria-hidden="true">
        </form>
        <button class="bell" type="button" title="Notifikasi" aria-label="Notifikasi">
            <img src="assets/images/game/ion-notifcations.svg" alt="" aria-hidden="true">
        </button>
        <?php if (is_logged_in()): ?>
            <button class="logout-trigger" type="button" title="Logout" aria-label="Logout">
                <img src="assets/images/game/mdi-logout.svg" alt="" aria-hidden="true">
            </button>
        <?php else: ?>
            <a class="login-link" href="login.php">LOGIN</a>
        <?php endif; ?>
    </div>
</header>
<main class="page <?= $active === 'game' ? 'game-page' : '' ?>">

<div class="confirm-modal" id="logoutModal" aria-hidden="true">
    <div class="confirm-card">
        <h2>Yakin mau logout?</h2>
        <p>Data yang sudah disimpan tetap aman di akunmu.</p>
        <div class="actions">
            <button class="btn light" type="button" data-close-logout>Batal</button>
            <a class="btn danger" href="logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="toast-modal" id="toastModal" aria-hidden="true">
    <div class="toast-card">
        <h2 id="toastTitle">Berhasil</h2>
        <p id="toastMessage">Data berhasil disimpan.</p>
        <button class="btn" type="button" data-close-toast>OK</button>
    </div>
</div>
