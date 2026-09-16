<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
require_csrf();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$error = '';

// ============================================
// HANDLE POST ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // TAMBAH PELANGGAN
    if ($action === 'tambah') {
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $desa = trim($_POST['desa'] ?? '');
        $kecamatan = trim($_POST['kecamatan'] ?? '');
        $mac_address = strtoupper(trim($_POST['mac_address'] ?? ''));
        $username_pppoe = trim($_POST['username_pppoe'] ?? '');
        $password_pppoe = trim($_POST['password_pppoe'] ?? '');
        $paket_id = intval($_POST['paket_id'] ?? 0);
        $status = $_POST['status'] ?? 'aktif';
        $tgl_pasang = $_POST['tgl_pasang'] ?? date('Y-m-d');

        if ($nama === '' || $no_hp === '') {
            $error = 'Nama dan No HP wajib diisi.';
        } elseif ($mac_address !== '' && !validate_mac($mac_address)) {
            $error = 'Format MAC address tidak valid. Gunakan format: AA:BB:CC:DD:EE:FF';
        } elseif (!validate_phone($no_hp)) {
            $error = 'Format nomor HP tidak valid.';
        } else {
            $no_hp = format_phone($no_hp);
            $kode = 'PLG-' . strtoupper(substr(md5(uniqid()), 0, 6));

            $stmt = $pdo->prepare('INSERT INTO pelanggan (kode_pelanggan, nama, no_hp, alamat, desa, kecamatan, mac_address, username_pppoe, password_pppoe, paket_id, status, tgl_pasang) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$kode, $nama, $no_hp, $alamat, $desa, $kecamatan, $mac_address, $username_pppoe, $password_pppoe, $paket_id, $status, $tgl_pasang]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => "Pelanggan $nama berhasil ditambahkan dengan kode $kode!"];
            header('Location: pelanggan.php');
            exit;
        }
    }
    
    // EDIT PELANGGAN
    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $desa = trim($_POST['desa'] ?? '');
        $kecamatan = trim($_POST['kecamatan'] ?? '');
        $mac_address = strtoupper(trim($_POST['mac_address'] ?? ''));
        $username_pppoe = trim($_POST['username_pppoe'] ?? '');
        $password_pppoe = trim($_POST['password_pppoe'] ?? '');
        $paket_id = intval($_POST['paket_id'] ?? 0);
        $status = $_POST['status'] ?? 'aktif';
        $tgl_pasang = $_POST['tgl_pasang'] ?? date('Y-m-d');

        if ($id > 0 && $nama !== '' && $no_hp !== '') {
            if ($mac_address !== '' && !validate_mac($mac_address)) {
                $error = 'Format MAC address tidak valid.';
            } else {
                $no_hp = format_phone($no_hp);
                
                $stmt = $pdo->prepare('UPDATE pelanggan SET nama = ?, no_hp = ?, alamat = ?, desa = ?, kecamatan = ?, mac_address = ?, username_pppoe = ?, password_pppoe = ?, paket_id = ?, status = ?, tgl_pasang = ? WHERE id = ?');
                $stmt->execute([$nama, $no_hp, $alamat, $desa, $kecamatan, $mac_address, $username_pppoe, $password_pppoe, $paket_id, $status, $tgl_pasang, $id]);

                $_SESSION['flash'] = ['type' => 'success', 'message' => "Data pelanggan $nama berhasil diupdate!"];
                header('Location: pelanggan.php');
                exit;
            }
        } else {
            $error = 'Nama dan No HP wajib diisi.';
        }
    }
}

// ============================================
// HANDLE GET ACTIONS
// ============================================
if ($action === 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM pelanggan WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pelanggan berhasil dihapus!'];
    }
    header('Location: pelanggan.php');
    exit;
}

// ============================================
// GET DATA
// ============================================
$paket_list = $pdo->query("SELECT id, nama_paket, harga_bulanan FROM paket WHERE status = 1 ORDER BY harga_bulanan ASC")->fetchAll();

$pelanggan_edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare('SELECT * FROM pelanggan WHERE id = ?');
    $stmt->execute([$id]);
    $pelanggan_edit = $stmt->fetch();
    
    if (!$pelanggan_edit) {
        header('Location: pelanggan.php');
        exit;
    }
}

// Filter
$filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT p.*, pk.nama_paket FROM pelanggan p LEFT JOIN paket pk ON p.paket_id = pk.id WHERE 1=1";
$params = [];

if ($filter !== '') {
    $sql .= " AND p.status = ?";
    $params[] = $filter;
}

if ($search !== '') {
    $sql .= " AND (p.nama LIKE ? OR p.no_hp LIKE ? OR p.kode_pelanggan LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pelanggan_list = $stmt->fetchAll();

$page_title = 'Data Pelanggan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Pelanggan - Billing RT/RW Net</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="main">
            <?php include __DIR__ . '/includes/header.php'; ?>

            <div class="content">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                        <i class="bi bi-check-circle"></i>
                        <?= $_SESSION['flash']['message'] ?>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <!-- FORM TAMBAH -->
                <?php if ($action === 'tambah'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Tambah Pelanggan Baru</h2>
                            <a href="pelanggan.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Kembali
                            </a>
                        </div>

                        <form method="post">
                            <?= csrf_field() ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" name="nama" class="form-input" placeholder="Nama pelanggan" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">No HP / WhatsApp *</label>
                                    <input type="text" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-input" rows="2" placeholder="Alamat lengkap"></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Desa / Kelurahan</label>
                                    <input type="text" name="desa" class="form-input" placeholder="Nama desa">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" name="kecamatan" class="form-input" placeholder="Nama kecamatan">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">MAC Address</label>
                                    <input type="text" name="mac_address" class="form-input" placeholder="AA:BB:CC:DD:EE:FF" style="text-transform: uppercase;">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tanggal Pasang</label>
                                    <input type="date" name="tgl_pasang" class="form-input" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Username PPPoE</label>
                                    <input type="text" name="username_pppoe" class="form-input" placeholder="Username PPPoE">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Password PPPoE</label>
                                    <input type="text" name="password_pppoe" class="form-input" placeholder="Password PPPoE">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Paket Internet</label>
                                    <select name="paket_id" class="form-input">
                                        <option value="0">-- Pilih Paket --</option>
                                        <?php foreach ($paket_list as $pk): ?>
                                            <option value="<?= $pk['id'] ?>"><?= $pk['nama_paket'] ?> - Rp <?= number_format($pk['harga_bulanan'], 0, ',', '.') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-input">
                                        <option value="aktif">Aktif</option>
                                        <option value="isolir">Isolir</option>
                                        <option value="berhenti">Berhenti</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Simpan Pelanggan
                            </button>
                        </form>
                    </div>

                <!-- FORM EDIT -->
<?php elseif ($action === 'edit' && $pelanggan_edit): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Edit Pelanggan</h2>
            <a href="pelanggan.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>

        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $pelanggan_edit['id'] ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kode Pelanggan</label>
                    <input type="text" class="form-input" value="<?= e($pelanggan_edit['kode_pelanggan']) ?>" disabled style="opacity: 0.6;">
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Pasang</label>
                    <input type="date" name="tgl_pasang" class="form-input" value="<?= $pelanggan_edit['tgl_pasang'] ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-input" value="<?= e($pelanggan_edit['nama']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">No HP / WhatsApp *</label>
                    <input type="text" name="no_hp" class="form-input" value="<?= e($pelanggan_edit['no_hp']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-input" rows="2"><?= e($pelanggan_edit['alamat']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Desa / Kelurahan</label>
                    <input type="text" name="desa" class="form-input" value="<?= e($pelanggan_edit['desa']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-input" value="<?= e($pelanggan_edit['kecamatan']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">MAC Address</label>
                    <input type="text" name="mac_address" class="form-input" value="<?= e($pelanggan_edit['mac_address']) ?>" placeholder="AA:BB:CC:DD:EE:FF" style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label class="form-label">Paket Internet</label>
                    <select name="paket_id" class="form-input">
                        <option value="0">-- Tanpa Paket --</option>
                        <?php foreach ($paket_list as $pk): ?>
                            <option value="<?= $pk['id'] ?>" <?= $pelanggan_edit['paket_id'] == $pk['id'] ? 'selected' : '' ?>>
                                <?= $pk['nama_paket'] ?> - Rp <?= number_format($pk['harga_bulanan'], 0, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username PPPoE</label>
                    <input type="text" name="username_pppoe" class="form-input" value="<?= e($pelanggan_edit['username_pppoe']) ?>" placeholder="Username PPPoE">
                </div>
                <div class="form-group">
                    <label class="form-label">Password PPPoE</label>
                    <input type="text" name="password_pppoe" class="form-input" value="<?= e($pelanggan_edit['password_pppoe']) ?>" placeholder="Password PPPoE">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="aktif" <?= $pelanggan_edit['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="isolir" <?= $pelanggan_edit['status'] == 'isolir' ? 'selected' : '' ?>>Isolir</option>
                    <option value="berhenti" <?= $pelanggan_edit['status'] == 'berhenti' ? 'selected' : '' ?>>Berhenti</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i>
                Update Pelanggan
            </button>
        </form>
    </div>
                <!-- LIST PELANGGAN -->
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Data Pelanggan</h2>
                            <a href="pelanggan.php?action=tambah" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                Tambah Pelanggan
                            </a>
                        </div>

                        <form method="get" style="margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
                            <input type="text" name="search" class="form-input" style="max-width: 300px;" 
                                   placeholder="Cari nama, no HP, atau kode..." value="<?= e($search) ?>">
                            <select name="status" class="form-input" style="max-width: 180px;">
                                <option value="">Semua Status</option>
                                <option value="aktif" <?= $filter == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="isolir" <?= $filter == 'isolir' ? 'selected' : '' ?>>Isolir</option>
                                <option value="berhenti" <?= $filter == 'berhenti' ? 'selected' : '' ?>>Berhenti</option>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                                Filter
                            </button>
                            <?php if ($filter !== '' || $search !== ''): ?>
                                <a href="pelanggan.php" class="btn btn-secondary">Reset</a>
                            <?php endif; ?>
                        </form>

                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>No HP</th>
                                        <th>Paket</th>
                                        <th>MAC Address</th>
                                        <th>Desa</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pelanggan_list)): ?>
                                        <tr>
                                            <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                                                <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 12px;"></i>
                                                Belum ada data pelanggan.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pelanggan_list as $i => $p): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><code><?= e($p['kode_pelanggan']) ?></code></td>
                                                <td><strong><?= e($p['nama']) ?></strong></td>
                                                <td><?= e($p['no_hp']) ?></td>
                                                <td><?= e($p['nama_paket'] ?? '-') ?></td>
                                                <td><code><?= e($p['mac_address'] ?? '-') ?></code></td>
                                                <td><?= e($p['desa'] ?? '-') ?></td>
                                                <td>
                                                    <?php if ($p['status'] == 'aktif'): ?>
                                                        <span class="badge badge-success">Aktif</span>
                                                    <?php elseif ($p['status'] == 'isolir'): ?>
                                                        <span class="badge badge-danger">Isolir</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Berhenti</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="pelanggan.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="pelanggan.php?action=hapus&id=<?= $p['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       title="Hapus" 
                                                       onclick="return confirm('Yakin mau hapus pelanggan ini?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>