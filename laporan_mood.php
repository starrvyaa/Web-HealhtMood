<?php
require_once 'config.php';
require_login();

if (!$pdo) {
    $title = 'Laporan Mood';
    $active = 'mood';
    require 'includes/header.php';
    echo '<section class="section"><p class="message error">Database belum tersambung. Import database.sql dulu di phpMyAdmin.</p></section>';
    require 'includes/footer.php';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM moods WHERE user_id = ? ORDER BY mood_date DESC, id DESC');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

$title = 'Laporan Mood';
$active = 'mood';
require 'includes/header.php';
?>
<section class="section">
    <div class="toolbar">
        <div>
            <h1>Berikan Ruang untuk Dirimu Bercerita.</h1>
            <p>Langkah pertama menuju kesehatan mental yang kuat adalah dengan memberikan validasi pada setiap perasaan yang hadir.</p>
        </div>
        <button class="btn green" type="button" onclick="location.href='mood.php'">Tambah Data Mood</button>
    </div>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Perasaan Saat Ini</th>
                    <th>Tanggal</th>
                    <th>Aktivitas Hari Ini</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['mood_label']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($row['mood_date']))) ?></td>
                        <td><?= e(trim(str_replace('Aktivitas:', '', strtok($row['note'] ?? '', "\n"))) ?: '-') ?></td>
                        <td class="actions">
                            <a class="btn icon" href="mood.php?edit=<?= e($row['id']) ?>">👁</a>
                            <a class="btn icon yellow" href="mood.php?edit=<?= e($row['id']) ?>">✎</a>
                            <a class="btn icon danger" href="mood.php?delete=<?= e($row['id']) ?>" onclick="return confirm('Hapus data mood ini?')">■</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?><tr><td colspan="4">Belum ada data mood.</td></tr><?php endif; ?>
            </tbody>
        </table>
        <div class="actions pagination"><span>‹</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>›</span></div>
    </div>
</section>
<?php require 'includes/footer.php'; ?>
