<?php
require_once 'config.php';
require_login();

if (!$pdo) {
    $title = 'Mood';
    $active = 'mood';
    require 'includes/header.php';
    echo '<section class="section"><p class="message error">Database belum tersambung. Import database.sql dulu di phpMyAdmin.</p></section>';
    require 'includes/footer.php';
    exit;
}

$userId = $_SESSION['user_id'];
$edit = null;

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM moods WHERE id = ? AND user_id = ?');
    $stmt->execute([(int) $_GET['delete'], $userId]);
    redirect_to('mood.php?msg=deleted');
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM moods WHERE id = ? AND user_id = ?');
    $stmt->execute([(int) $_GET['edit'], $userId]);
    $edit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $date = $_POST['mood_date'] ?? date('Y-m-d');
    $label = $_POST['mood_label'] ?? 'Biasa aja';
    $activity = trim($_POST['activity'] ?? '');
    $scoreMap = ['Senang' => 5, 'Cemas' => 2, 'Biasa aja' => 3, 'Sedih' => 1, 'Marah' => 1];
    $score = $scoreMap[$label] ?? 3;
    $note = trim($_POST['note'] ?? '');

    if ($activity !== '') {
        $note = trim("Aktivitas: {$activity}\n{$note}");
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE moods SET mood_date = ?, mood_label = ?, mood_score = ?, note = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$date, $label, $score, $note, $id, $userId]);
        redirect_to('mood.php?msg=updated');
    }

    $stmt = $pdo->prepare('INSERT INTO moods (user_id, mood_date, mood_label, mood_score, note) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $date, $label, $score, $note]);
    redirect_to('mood.php?msg=created');
}

$rows = $pdo->prepare('SELECT * FROM moods WHERE user_id = ? ORDER BY mood_date DESC, id DESC');
$rows->execute([$userId]);
$moods = $rows->fetchAll();

$messages = [
    'created' => 'Data mood berhasil ditambahkan.',
    'updated' => 'Data mood berhasil diperbarui.',
    'deleted' => 'Data mood berhasil dihapus.',
];
$message = $messages[$_GET['msg'] ?? ''] ?? '';

$title = 'Mood';
$active = 'mood';
require 'includes/header.php';
?>
<section class="feature-page reverse">
    <div class="feature-art">
        <div class="fake-bars">
            <p>Grafik Mood Minggu Ini</p>
            <small>Skor harian (1-5)</small>
            <?php foreach ([70, 42, 56, 38, 64, 52, 86] as $i => $w): ?>
                <div class="bar-line"><span><?= ['Sen','Sel','Rab','Kam','Jum','Sab','Min'][$i] ?></span><span style="--w:<?= $w ?>%"></span><span><?= round($w / 20, 1) ?></span></div>
            <?php endforeach; ?>
        </div>
        <img src="public/Rectangle-38@2x.png" alt="Ilustrasi mood">
    </div>
    <div class="feature-copy">
        <h1 class="section-title">Kenali Dirimu Lebih Dalam<br>Satu Hari di Satu Waktu</h1>
        <p>Tidak apa-apa jika merasa tidak baik-baik saja hari ini. Langkah pertama untuk merasa lebih baik adalah dengan mengakui perasaanmu dan memahami penyebabnya.</p>
        <div class="actions" style="justify-content:flex-end">
            <button class="btn" type="button" data-open-modal="#moodModal">Tambah Data Mood</button>
            <a class="btn" href="laporan_mood.php">Laporan Data Mood</a>
        </div>
    </div>
</section>

<section class="section">
    <?php if ($message): ?><p class="message"><?= e($message) ?></p><?php endif; ?>
    <?php if ($edit): ?><script>document.addEventListener('DOMContentLoaded', function(){document.getElementById('moodModal').classList.add('show');});</script><?php endif; ?>
</section>

<div class="modal <?= $edit ? 'show' : '' ?>" id="moodModal">
    <form class="modal-card" method="post">
        <button class="modal-close" type="button" data-close-modal>&times;</button>
        <h2>Bagaimana perasaanmu sekarang?</h2>
        <p>Pilih emoji yang paling menggambarkan kondisimu saat ini</p>
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="mood-options">
            <?php foreach (['Senang' => '☺', 'Cemas' => '☹', 'Biasa aja' => '☻', 'Sedih' => '☹', 'Marah' => '☹'] as $label => $icon): ?>
                <label>
                    <input type="radio" name="mood_label" value="<?= e($label) ?>" <?= (($edit['mood_label'] ?? 'Biasa aja') === $label) ? 'checked' : '' ?>>
                    <span><?= e($icon) ?></span>
                    <?= e($label) ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="form-grid">
            <label>Tanggal
                <input type="date" name="mood_date" value="<?= e($edit['mood_date'] ?? date('Y-m-d')) ?>" required>
            </label>
            <label>Aktivitas hari ini
                <input type="text" name="activity" value="" placeholder="Bekerja">
            </label>
            <label class="full">Catatan (opsional)
                <textarea name="note" placeholder="Ceritakan sedikit tentang harimu hari ini..."><?= e($edit['note'] ?? '') ?></textarea>
            </label>
        </div>
        <button class="btn" type="submit">Simpan Data Mood</button>
    </form>
</div>
<?php require 'includes/footer.php'; ?>
