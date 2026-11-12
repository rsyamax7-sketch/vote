<?php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan perannya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?pesan=akses_ditolak");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if ($id == $_SESSION['user_id']) {
        header("Location: pemilih.php?pesan=gagal_hapus_diri");
        exit;
    }
    $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $del->execute([$id]);
    header("Location: pemilih.php?pesan=sukses_hapus");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'pemilih' ORDER BY id DESC");
$stmt->execute();
$pemilihList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pemilih - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            overflow-x: hidden;
        }
        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 1.5rem;
            text-decoration: none;
        }
        .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }
        .sidebar-heading {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 700;
            padding: 0 0.75rem;
            margin-bottom: 0.5rem;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.92rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.25s ease;
        }
        .sidebar .nav-link i { font-size: 1.1rem; }
        .sidebar .nav-link:hover {
            color: #f8fafc;
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(4px);
        }
        .sidebar .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 6px 16px -4px rgba(37, 99, 235, 0.5);
            font-weight: 600;
        }
        .sidebar-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .btn-logout {
            color: #f87171;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 0.75rem;
            padding: 0.65rem 1rem;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-logout:hover { background: #ef4444; color: white; }
        .main-wrapper {
            margin-left: 260px;
            padding: 2rem 2.5rem;
            min-height: 100vh;
        }
        .top-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.25rem 1.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }
        .modern-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        }
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
            <li class="nav-item"><a href="pemilih.php" class="nav-link active"><i class="bi bi-person-badge-fill"></i> Kelola Pemilih</a></li>
            <li class="nav-item"><a href="periode.php" class="nav-link"><i class="bi bi-calendar2-week-fill"></i> Periode Voting</a></li>
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
                    <h1 class="h4 fw-bold mb-1">Kelola Akun Pemilih</h1>
                    <p class="text-muted small mb-0">Daftar akun masyarakat/peserta yang terdaftar sebagai pemilih sah.</p>
                </div>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">
                Total: <?= count($pemilihList) ?> Pemilih
            </span>
        </div>

        <?php if (isset($_GET['pesan'])): ?>
            <?php if ($_GET['pesan'] == 'sukses_hapus'): ?>
                <div class="alert alert-success border-0 rounded-4 shadow-sm alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>Akun pemilih berhasil dihapus.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['pesan'] == 'gagal_hapus_diri'): ?>
                <div class="alert alert-danger border-0 rounded-4 shadow-sm alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal! Anda tidak dapat menghapus akun yang sedang aktif.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="modern-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Nomor HP</th>
                            <th>Tanggal Daftar</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pemilihList) > 0): ?>
                            <?php $no = 1; foreach ($pemilihList as$row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($row['created_at']) ?></td>
                                <td class="text-end">
                                    <a href="pemilih.php?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-light text-danger border rounded-3" onclick="return confirm('Yakin ingin menghapus akun pemilih ini?')">
                                        <i class="bi bi-trash me-1"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">Belum ada akun pemilih yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>