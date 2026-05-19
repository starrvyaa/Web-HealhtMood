<?php
require_once 'config.php';
require_login();

if (!$pdo) {
    $title = 'Laporan Tidur';
    $active = 'tidur';
    require 'includes/header.php';
    echo '<section class="section"><p class="message error">Database belum tersambung. Import database.sql dulu di phpMyAdmin.</p></section>';
    require 'includes/footer.php';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM sleeps WHERE user_id = ? ORDER BY sleep_date DESC, id DESC');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

$title = 'Laporan Tidur';
$active = 'tidur';
require 'includes/header.php';
?>
<section class="section">
    <div class="toolbar">
        <div>
            <h1>Ringkasan Data Kesehatanmu Tidur mu.</h1>
            <p>Pantau kembali catatan tidur dan riwayat mood harianmu untuk melihat perkembangan kesehatan mental secara menyeluruh.</p>
        </div>
        <button class="btn green" type="button" onclick="location.href='tidur.php'">Tambah Data Tidur</button>
    </div>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Tanggal Tidur</th>
                    <th>Jam Mulai Tidur</th>
                    <th>Jam Mulai Bangun</th>
                    <th>Kualitas Tidur</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e(date('n/j/Y', strtotime($row['sleep_date']))) ?></td>
                        <td><?= e(date('h.i A', strtotime($row['sleep_start'] ?? '21:25'))) ?></td>
                        <td><?= e(date('h.i A', strtotime($row['sleep_end'] ?? '06:30'))) ?></td>
                        <td><?= ((int) $row['quality'] >= 4) ? 'Baik' : 'Cukup' ?></td>
                        <td class="actions">
                            <a class="btn icon" href="tidur.php?edit=<?= e($row['id']) ?>">👁</a>
                            <a class="btn icon yellow" href="tidur.php?edit=<?= e($row['id']) ?>">✎</a>
                            <a class="btn icon danger" href="tidur.php?delete=<?= e($row['id']) ?>" onclick="return confirm('Hapus data tidur ini?')">■</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?><tr><td colspan="5">Belum ada data tidur.</td></tr><?php endif; ?>
            </tbody>
        </table>
        <div class="actions pagination"><span>‹</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>›</span></div>
    </div>
</section>
<?php require 'includes/footer.php'; ?>
