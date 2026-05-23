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

$userId = $_SESSION['user_id'];
$perPage = 5;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

/* ── AJAX GET ── */
if (isset($_GET['ajax_get']) && is_numeric($_GET['ajax_get'])) {
    header('Content-Type: application/json');
    $s = $pdo->prepare('SELECT * FROM moods WHERE id = ? AND user_id = ?');
    $s->execute([(int)$_GET['ajax_get'], $userId]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    if (!$r) { echo json_encode(['error' => 'not found']); exit; }
    $firstLine     = strtok($r['note'] ?? '', "\n");
    $r['activity'] = trim(str_replace('Aktivitas:', '', $firstLine)) ?: '-';
    $noteLines     = explode("\n", $r['note'] ?? '');
    array_shift($noteLines);
    $r['catatan']  = trim(implode("\n", $noteLines)) ?: '-';
    echo json_encode($r);
    exit;
}

/* ── AJAX POST EDIT ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_edit'])) {
    header('Content-Type: application/json');
    $id       = (int)$_POST['id'];
    $label    = trim($_POST['mood_label'] ?? '');
    $date     = $_POST['mood_date'] ?? '';
    $activity = trim($_POST['activity'] ?? '');
    $catatan  = trim($_POST['catatan'] ?? '');
    $note     = 'Aktivitas: ' . $activity . ($catatan ? "\n" . $catatan : '');
    $u  = $pdo->prepare('UPDATE moods SET mood_label=?, mood_date=?, note=? WHERE id=? AND user_id=?');
    $ok = $u->execute([$label, $date, $note, $id, $userId]);
    echo json_encode(['ok' => $ok]);
    exit;
}

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM moods WHERE user_id = ?');
$countStmt->execute([$userId]);
$totalRows  = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$stmt = $pdo->prepare('SELECT * FROM moods WHERE user_id = ? ORDER BY mood_date DESC, id DESC LIMIT ? OFFSET ?');
$stmt->bindValue(1, $userId, PDO::PARAM_INT);
$stmt->bindValue(2, $perPage, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$title  = 'Laporan Mood';
$active = 'mood';
require 'includes/header.php';
?>

<!-- ══════════════ STYLE ══════════════ -->
<style>
/* ── Shared modal override ── */
.modal { z-index: 9999; }
.modal-card {
    border-radius: 16px;
    overflow: hidden;
    padding: 0;
    max-width: 500px;
    width: 94%;
    box-shadow: 0 24px 64px rgba(0,0,0,.32);
}

/* header strip — sama persis dengan tidur */
.mc-header {
    background: var(--navy, #0f172a);
    color: #fff;
    padding: 22px 26px 18px;
    position: relative;
}
.mc-header h2 {
    margin: 0 0 4px;
    font-size: 18px;
    font-weight: 800;
    color: #fff;
}
.mc-header p {
    margin: 0;
    font-size: 13px;
    color: rgba(255,255,255,.6);
}
.mc-close {
    position: absolute;
    top: 18px; right: 20px;
    background: rgba(255,255,255,.15);
    border: none;
    color: #fff;
    width: 30px; height: 30px;
    border-radius: 50%;
    font-size: 16px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .18s;
}
.mc-close:hover { background: rgba(255,255,255,.3); }

/* body */
.mc-body { padding: 24px 26px 26px; background: #fff; }

/* grid — sama dengan .form-grid tidur */
.mc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.mc-grid.col1 { grid-template-columns: 1fr; }
.mc-field { display: flex; flex-direction: column; gap: 7px; }
.mc-field label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.mc-field input,
.mc-field select,
.mc-field textarea {
    height: 48px;
    padding: 0 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    background: #f8fafc;
    box-sizing: border-box;
    transition: border-color .18s;
    font-family: inherit;
}
.mc-field textarea {
    height: 80px;
    padding: 12px 14px;
    resize: none;
}
.mc-field input:focus,
.mc-field select:focus,
.mc-field textarea:focus {
    outline: none;
    border-color: #7c3aed;
    background: #fff;
}
.mc-field input[readonly],
.mc-field select[disabled],
.mc-field textarea[readonly] {
    background: #f1f5f9;
    color: #475569;
    cursor: default;
}

/* mood chip (edit) */
.mc-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.mc-chip {
    padding: 8px 16px;
    border-radius: 30px;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all .18s;
    color: #475569;
}
.mc-chip:hover  { border-color: #7c3aed; }
.mc-chip.active { border-color: #7c3aed; background: #ede9fe; color: #5b21b6; }

/* mood badge (view) */
.mc-mood-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 16px;
    font-weight: 800;
}
.mc-mood-badge.senang  { background:#dcfce7; color:#166534; }
.mc-mood-badge.sedih   { background:#dbeafe; color:#1e40af; }
.mc-mood-badge.marah   { background:#fee2e2; color:#991b1b; }
.mc-mood-badge.cemas   { background:#fef9c3; color:#854d0e; }
.mc-mood-badge.biasa   { background:#f1f5f9; color:#475569; }

/* kualitas badge */
.mc-kualitas {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 13px;
}
.mc-kualitas.baik   { background:#dcfce7; color:#166534; }
.mc-kualitas.cukup  { background:#fef9c3; color:#854d0e; }
.mc-kualitas.kurang { background:#fee2e2; color:#991b1b; }

/* tombol bawah */
.mc-btn-row {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.mc-btn {
    flex: 1;
    height: 46px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: filter .18s;
    text-align: center;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.mc-btn:hover { filter: brightness(.92); }
.mc-btn-cancel { background: #e2e8f0; color: #475569; }
.mc-btn-save   { background: #7c3aed; color: #fff; }
.mc-btn-close  { background: #0f172a; color: #fff; }
.mc-btn-hapus  { background: #e74c3c; color: #fff; }

/* hapus info badge */
.mc-del-info {
    display: block;
    margin: 12px auto 20px;
    padding: 8px 20px;
    background: #fef2f2;
    color: #b91c1c;
    border-radius: 30px;
    font-weight: 700;
    font-size: 14px;
    text-align: center;
}
.mc-del-icon { font-size: 52px; display:block; text-align:center; margin-bottom:8px; }
.mc-del-text { text-align:center; font-size:14px; color:#64748b; line-height:1.7; }

/* msg */
#edit-msg { font-size:13px; text-align:center; margin-top:10px; display:none; }
</style>

<!-- ══════════════ HALAMAN ══════════════ -->
<section class="section">
    <div class="toolbar">
        <div>
            <h1>Berikan Ruang untuk Dirimu Bercerita.</h1>
            <p>Langkah pertama menuju kesehatan mental yang kuat adalah dengan memberikan validasi pada setiap perasaan yang hadir.</p>
        </div>
        <a class="btn green" href="mood.php?add=1">+ Tambah Data Mood</a>
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
                <?php
                    $firstLine = strtok($row['note'] ?? '', "\n");
                    $activity  = trim(str_replace('Aktivitas:', '', $firstLine)) ?: '-';
                    $rowId     = (int)$row['id'];
                    $moodLabel = htmlspecialchars($row['mood_label'], ENT_QUOTES, 'UTF-8');
                    $moodDate  = htmlspecialchars(date('d/m/Y', strtotime($row['mood_date'])), ENT_QUOTES, 'UTF-8');
                    $deleteUrl = 'mood.php?delete=' . $rowId;
                ?>
                <tr id="row-<?= $rowId ?>">
                    <td class="col-label"><?= e($row['mood_label']) ?></td>
                    <td class="col-date"><?= e(date('d/m/Y', strtotime($row['mood_date']))) ?></td>
                    <td class="col-act"><?= e($activity) ?></td>
                    <td class="actions">
                        <a class="btn icon icon-view" href="#"
                           onclick="openLihat(<?= $rowId ?>); return false;" title="Lihat"><span></span></a>
                        <a class="btn icon icon-edit yellow" href="#"
                           onclick="openEdit(<?= $rowId ?>); return false;" title="Edit"><span></span></a>
                        <a class="btn icon icon-trash danger" href="#"
                           onclick="openHapus('<?= $deleteUrl ?>', '<?= $moodLabel ?>', '<?= $moodDate ?>'); return false;" title="Hapus"><span></span></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="4">Belum ada data mood.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="actions pagination">
            <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">&lt;</a><?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>">&gt;</a><?php endif; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     MODAL 1 ── LIHAT DETAIL MOOD
═══════════════════════════════════════════ -->
<div class="modal" id="modalLihat">
  <div class="modal-card">
    <div class="mc-header">
      <h2>Detail Data Mood</h2>
      <p>Informasi lengkap catatan mood harianmu</p>
      <button class="mc-close" onclick="closeModal('modalLihat')">✕</button>
    </div>
    <div class="mc-body">

      <!-- badge mood besar di tengah -->
      <div style="text-align:center; margin-bottom:20px;">
        <span class="mc-mood-badge" id="lihat-badge">😊 Senang</span>
      </div>

      <div class="mc-grid">
        <div class="mc-field">
          <label>📅 Tanggal Mood</label>
          <input type="text" id="lihat-tanggal" readonly/>
        </div>
        <div class="mc-field">
          <label>💼 Aktivitas Hari Ini</label>
          <input type="text" id="lihat-activity" readonly/>
        </div>
      </div>

      <div class="mc-grid col1">
        <div class="mc-field">
          <label>📝 Catatan</label>
          <textarea id="lihat-catatan" readonly></textarea>
        </div>
      </div>

      <div class="mc-btn-row">
        <button class="mc-btn mc-btn-close" onclick="closeModal('modalLihat')">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL 2 ── EDIT DATA MOOD
═══════════════════════════════════════════ -->
<div class="modal" id="modalEdit">
  <div class="modal-card">
    <div class="mc-header">
      <h2>Edit Data Mood</h2>
      <p>Perbarui catatan perasaan dan aktivitasmu</p>
      <button class="mc-close" onclick="closeModal('modalEdit')">✕</button>
    </div>
    <div class="mc-body">
      <input type="hidden" id="edit-id"/>

      <div class="mc-grid col1" style="margin-bottom:16px;">
        <div class="mc-field">
          <label>Perasaan Saat Ini</label>
          <div class="mc-chips" id="edit-chips">
            <div class="mc-chip" data-val="Senang">😊 Senang</div>
            <div class="mc-chip" data-val="Sedih">😢 Sedih</div>
            <div class="mc-chip" data-val="Marah">😠 Marah</div>
            <div class="mc-chip" data-val="Cemas">😰 Cemas</div>
            <div class="mc-chip" data-val="Biasa aja">😐 Biasa aja</div>
          </div>
        </div>
      </div>

      <div class="mc-grid">
        <div class="mc-field">
          <label>📅 Tanggal Mood</label>
          <input type="date" id="edit-date"/>
        </div>
        <div class="mc-field">
          <label>💼 Aktivitas Hari Ini</label>
          <input type="text" id="edit-activity" placeholder="Kuliah, Bekerja..."/>
        </div>
      </div>

      <div class="mc-grid col1">
        <div class="mc-field">
          <label>📝 Catatan (opsional)</label>
          <textarea id="edit-catatan" placeholder="Ceritakan harimu..."></textarea>
        </div>
      </div>

      <p id="edit-msg"></p>

      <div class="mc-btn-row">
        <button class="mc-btn mc-btn-cancel" onclick="closeModal('modalEdit')">Batal</button>
        <button class="mc-btn mc-btn-save"   onclick="saveEdit()">💾 Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL 3 ── HAPUS KONFIRMASI
═══════════════════════════════════════════ -->
<div class="modal" id="modalHapus">
  <div class="modal-card" style="max-width:400px;">
    <div class="mc-header">
      <h2>Hapus Data Mood</h2>
      <p>Tindakan ini tidak dapat dibatalkan</p>
      <button class="mc-close" onclick="closeModal('modalHapus')">✕</button>
    </div>
    <div class="mc-body">
      <span class="mc-del-icon">🗑️</span>
      <p class="mc-del-text">
        Kamu akan menghapus data mood berikut.<br>
        <strong>Tindakan ini tidak dapat dibatalkan.</strong>
      </p>
      <span class="mc-del-info" id="hapus-info">—</span>
      <div class="mc-btn-row">
        <button class="mc-btn mc-btn-cancel" onclick="closeModal('modalHapus')">Batal</button>
        <a class="mc-btn mc-btn-hapus" id="hapus-confirm-btn" href="#">Ya, Hapus</a>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════ JAVASCRIPT ══════════════ -->
<script>
/* ── emoji & badge class ── */
const EMOJI_MAP = {
    'senang':'😊','sedih':'😢','marah':'😠','cemas':'😰','biasa aja':'😐','biasa':'😐'
};
const BADGE_CLASS = {
    'senang':'senang','sedih':'sedih','marah':'marah','cemas':'cemas','biasa aja':'biasa','biasa':'biasa'
};
function getEmoji(label){
    return EMOJI_MAP[label.toLowerCase()] || '😐';
}
function getBadgeClass(label){
    return BADGE_CLASS[label.toLowerCase()] || 'biasa';
}

/* ── open / close ── */
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id){ document.getElementById(id).classList.remove('show'); }

/* klik backdrop tutup */
document.querySelectorAll('.modal').forEach(function(el){
    el.addEventListener('click', function(e){
        if (e.target === this) closeModal(this.id);
    });
});
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape'){
        document.querySelectorAll('.modal.show').forEach(function(el){ closeModal(el.id); });
    }
});

/* ── LIHAT ── */
function openLihat(id){
    fetch('laporan_mood.php?ajax_get=' + id)
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.error){ alert('Data tidak ditemukan.'); return; }

            /* badge */
            var badge = document.getElementById('lihat-badge');
            badge.textContent = getEmoji(d.mood_label) + ' ' + d.mood_label;
            badge.className   = 'mc-mood-badge ' + getBadgeClass(d.mood_label);

            /* tanggal yyyy-mm-dd → dd/mm/yyyy */
            var p = (d.mood_date || '').split('-');
            document.getElementById('lihat-tanggal').value  = p.length===3 ? p[2]+'/'+p[1]+'/'+p[0] : d.mood_date;
            document.getElementById('lihat-activity').value = d.activity || '-';
            document.getElementById('lihat-catatan').value  = d.catatan  || '-';

            openModal('modalLihat');
        })
        .catch(function(){ alert('Gagal mengambil data.'); });
}

/* ── EDIT ── */
function openEdit(id){
    fetch('laporan_mood.php?ajax_get=' + id)
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.error){ alert('Data tidak ditemukan.'); return; }

            document.getElementById('edit-id').value       = d.id;
            document.getElementById('edit-date').value     = d.mood_date;
            document.getElementById('edit-activity').value = d.activity || '';
            document.getElementById('edit-catatan').value  = d.catatan  || '';

            /* set chip aktif */
            document.querySelectorAll('#edit-chips .mc-chip').forEach(function(c){
                c.classList.toggle('active', c.dataset.val.toLowerCase() === d.mood_label.toLowerCase());
            });

            var msg = document.getElementById('edit-msg');
            msg.style.display = 'none';
            msg.textContent   = '';

            openModal('modalEdit');
        })
        .catch(function(){ alert('Gagal mengambil data.'); });
}

/* pilih chip */
document.querySelectorAll('#edit-chips .mc-chip').forEach(function(c){
    c.addEventListener('click', function(){
        document.querySelectorAll('#edit-chips .mc-chip').forEach(function(x){ x.classList.remove('active'); });
        this.classList.add('active');
    });
});

function saveEdit(){
    var id       = document.getElementById('edit-id').value;
    var chip     = document.querySelector('#edit-chips .mc-chip.active');
    var label    = chip ? chip.dataset.val : '';
    var date     = document.getElementById('edit-date').value;
    var activity = document.getElementById('edit-activity').value.trim();
    var catatan  = document.getElementById('edit-catatan').value.trim();
    var msg      = document.getElementById('edit-msg');

    if (!label){
        msg.style.display='block'; msg.style.color='#e74c3c';
        msg.textContent = '⚠ Pilih perasaan terlebih dahulu.'; return;
    }
    if (!date){
        msg.style.display='block'; msg.style.color='#e74c3c';
        msg.textContent = '⚠ Tanggal wajib diisi.'; return;
    }

    var fd = new FormData();
    fd.append('ajax_edit', '1');
    fd.append('id',         id);
    fd.append('mood_label', label);
    fd.append('mood_date',  date);
    fd.append('activity',   activity);
    fd.append('catatan',    catatan);

    fetch('laporan_mood.php', { method:'POST', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.ok){
                /* update tabel langsung tanpa reload */
                var tr = document.getElementById('row-' + id);
                if (tr){
                    tr.querySelector('.col-label').textContent = label;
                    var p = date.split('-');
                    tr.querySelector('.col-date').textContent  = p.length===3 ? p[2]+'/'+p[1]+'/'+p[0] : date;
                    tr.querySelector('.col-act').textContent   = activity || '-';
                }
                msg.style.display='block'; msg.style.color='#16a34a';
                msg.textContent = '✓ Data berhasil disimpan!';
                setTimeout(function(){ closeModal('modalEdit'); }, 900);
            } else {
                msg.style.display='block'; msg.style.color='#e74c3c';
                msg.textContent = '✗ Gagal menyimpan. Coba lagi.';
            }
        })
        .catch(function(){
            msg.style.display='block'; msg.style.color='#e74c3c';
            msg.textContent = '✗ Error jaringan.';
        });
}

/* ── HAPUS ── */
function openHapus(url, mood, tanggal){
    document.getElementById('hapus-info').textContent    = mood + ' · ' + tanggal;
    document.getElementById('hapus-confirm-btn').href   = url;
    openModal('modalHapus');
}
</script>

<?php require 'includes/footer.php'; ?>
