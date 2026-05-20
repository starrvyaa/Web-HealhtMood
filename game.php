<?php
require_once 'config.php';
require_login();
$title = 'Game';
$active = 'game';
require 'includes/header.php';

$games = [
    [
        'title' => 'Subway Surfers',
        'desc' => 'Game endless runner seru. Hindari kejaran petugas dengan melompati kereta dan mengumpulkan koin.',
        'rating' => '4.7',
        'image' => 'public/image-13@2x.png',
        'url' => 'https://poki.com/en/g/subway-surfers',
    ],
    [
        'title' => 'Anycolor',
        'desc' => 'Game mewarnai kasual. Warnai gambar dengan memilih warna dari spidol di bagian bawah layar.',
        'rating' => '4.8',
        'image' => 'public/image-14@2x.png',
        'url' => 'https://poki.com/en/g/anycolor',
    ],
    [
        'title' => 'Poxel.io',
        'desc' => 'FPS online cepat yang dirancang untuk keahlian murni. Ikuti pertandingan singkat dan intens.',
        'rating' => '4.5',
        'image' => 'public/Screenshot-2026-04-30-111247-1@2x.png',
        'url' => 'https://www.crazygames.com/',
    ],
    [
        'title' => 'Block Blaster',
        'desc' => 'Susun blok, bersihkan baris, dan nikmati permainan puzzle ringan untuk istirahat sebentar.',
        'rating' => '4.6',
        'image' => 'public/Screenshot-2026-04-30-111717-11@2x.png',
        'url' => 'https://poki.com/',
    ],
    [
        'title' => 'Merge Fruits',
        'desc' => 'Gabungkan buah yang sama untuk mendapat skor lebih tinggi dan suasana bermain yang santai.',
        'rating' => '4.7',
        'image' => 'public/Screenshot-2026-04-30-112015-1@2x.png',
        'url' => 'https://www.crazygames.com/',
    ],
    [
        'title' => 'Sudoku',
        'desc' => 'Latih fokus dengan puzzle angka klasik yang cocok untuk jeda singkat.',
        'rating' => '4.4',
        'image' => 'public/Screenshot-2026-04-30-111717-21@2x.png',
        'url' => 'https://poki.com/en/sudoku',
    ],
];
?>
<section class="section">
    <div class="game-grid">
        <?php foreach ($games as $game): ?>
            <article class="game-card">
                <img src="<?= e($game['image']) ?>" alt="<?= e($game['title']) ?>">
                <div class="game-body">
                    <h2><?= e($game['title']) ?></h2>
                    <p><?= e($game['desc']) ?></p>
                    <div class="rating">
                        <span><span class="star">★</span> <?= e($game['rating']) ?></span>
                        <a class="btn" href="<?= e($game['url']) ?>" target="_blank" rel="noopener">Play</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require 'includes/footer.php'; ?>
