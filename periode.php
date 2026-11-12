<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?pesan=akses_ditolak");
    exit;
}

$pesan = "";

// Proses Tambah Periode Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_periode'])) {
    $nama_periode    = $_POST['nama_periode'];
    $tanggal_mulai   = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $status          = $_POST['status'];

    if ($status === 'Aktif') {
        $pdo->query("UPDATE periode_voting SET status = 'Nonaktif'");
    }

    $stmt = $pdo->prepare("INSERT INTO periode_voting (nama_periode, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nama_periode, $tanggal_mulai, $tanggal_selesai, $status]);
    $pesan = "Periode voting berhasil ditambahkan!";
}

// Proses Ubah Status Aktif/Nonaktif Langsung dari Web
if (isset($_GET['ubah_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status_baru = $_GET['ubah_status'];

    // Jika diubah jadi Aktif, nonaktifkan periode lainnya terlebih dahulu
    if ($status_baru === 'Aktif') {
        $pdo->query("UPDATE periode_voting SET status = 'Nonaktif'");
    }

    $stmt = $pdo->prepare("UPDATE periode_voting SET status = ? WHERE id = ?");
    $stmt->execute([$status_baru, $id]);
    
    header("Location: periode.php?pesan=sukses_ubah");
    exit;
}

$periodeList = $pdo->query("SELECT * FROM periode_voting ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Periode Voting - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #0f172a; overflow-x: hidden; }
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; width: 260px; position: fixed; top: 0; left: 0; z-index: 1000; padding: 1.5rem 1rem; display: flex; flex-direction: column; box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; }
        .sidebar-brand { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.08); margin-bottom: 1.5rem; text-decoration: none; }
        .brand-icon { width: 38px; height: 38px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); }
        .sidebar-heading { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; font-weight: 700; padding: 0 0.75rem; margin-bottom: 0.5rem; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; font-size: 0.92rem; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.75rem; transition: all 0.25s ease; }
        .sidebar .nav-link i { font-size: 1.1rem; }
        .sidebar .nav-link:hover { color: #f8fafc; background: rgba(255, 255, 255, 0.06); transform: translateX(4px); }
        .sidebar .nav-link.active { color: #ffffff; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); box-shadow: 0 6px 16px -4px rgba(37, 99, 235, 0.5); font-weight: 600; }
        .sidebar-footer { margin-top: auto; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.08); }
        .btn-logout { color: #f87171; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 0.75rem; padding: 0.65rem 1rem; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.2s ease; }
        .btn-logout:hover { background: #ef4444; color: white; }
        .main-wrapper { margin-left: 260px; padding: 2rem 2.5rem; min-height: 100vh; }
        .top-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.25rem 1.75rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02); }
        .modern-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03); }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; padding: 1.25rem; }
        }
    </style>
</head>
<body>

    <!-- Modern Sidebar -->
    <aside class="sidebar" id="adminSidebar">
        <a href="dashboard.php" class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-check2-square"></i></div>
            <div>
                <span class="text-white fw-bold fs-6 d-block lh-1">E-Voting</span>
                <small class="text-secondary" style="font-size: 0.75rem;">Administrator</small>
            </div>
        </a>

        <div class="sidebar-heading">Menu Utama</div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
            <li class="nav-item"><a href="kontestan.php" class="nav-link"><i class="bi bi-people-fill"></i> Kelola Kontestan</a></li>
            <li class="nav-item"><a href="pemilih.php" class="nav-link"><i class="bi bi-person-badge-fill"></i> Kelola Pemilih</a></li>
            <li class="nav-item"><a href="periode.php" class="nav-link active"><i class="bi bi-calendar2-week-fill"></i> Periode Voting</a></li>
        </ul>

        <div class="sidebar-heading mt-3">Pintasan Publik</div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="../pages/klasemen.php" target="_blank" class="nav-link"><i class="bi bi-trophy-fill text-warning"></i> Lihat Klasemen</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout"><i class="bi bi-box-arrow-left"></i> Keluar Sistem</a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="top-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none border" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div>
                    <h1 class="h4 fw-bold mb-1">Pengaturan Periode Voting</h1>
                    <p class="text-muted small mb-0">Atur jadwal mulai dan selesai kegiatan voting.</p>
                </div>
            </div>
        </div>

        <?php if (!empty($pesan) || isset($_GET['pesan'])): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= !empty($pesan) ? $pesan : 'Status periode berhasil diperbarui!' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form Tambah Periode -->
            <div class="col-lg-4">
                <div class="modern-card">
                    <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle-fill me-2 text-primary"></i>Buat Periode Baru</h5>
                    <form action="" method="POST">
                        <input type="hidden" name="tambah_periode" value="1">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Kegiatan</label>
                            <input type="text" name="nama_periode" class="form-control rounded-3" placeholder="Contoh: Pemilihan Tahap 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Waktu Mulai</label>
                            <input type="datetime-local" name="tanggal_mulai" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Waktu Selesai</label>
                            <input type="datetime-local" name="tanggal_selesai" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold shadow-sm">Simpan Periode</button>
                    </form>
                </div>
            </div>

            <!-- Daftar Periode -->
            <div class="col-lg-8">
                <div class="modern-card">
                    <h5 class="fw-bold mb-3"><i class="bi bi-list-task me-2 text-primary"></i>Daftar Periode Voting</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Kegiatan</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($periodeList) > 0): ?>
                                    <?php foreach ($periodeList as $p): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($p['nama_periode']) ?></td>
                                        <td class="small text-muted"><?= date('d M Y, H:i', strtotime($p['tanggal_mulai'])) ?></td>
                                        <td class="small text-muted"><?= date('d M Y, H:i', strtotime($p['tanggal_selesai'])) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $p['status'] == 'Aktif' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $p['status'] == 'Aktif' ? 'success' : 'secondary' ?> px-3 py-2 rounded-pill fw-semibold">
                                                <?= $p['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($p['status'] === 'Aktif'): ?>
                                                <a href="periode.php?ubah_status=Nonaktif&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">
                                                    <i class="bi bi-stop-circle me-1"></i> Nonaktifkan
                                                </a>
                                            <?php else: ?>
                                                <a href="periode.php?ubah_status=Aktif&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                                    <i class="bi bi-play-circle me-1"></i> Aktifkan
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">Belum ada periode voting yang dibuat.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>