<?php
session_start();
require_once '../config/koneksi.php';

// Cek hak akses pemilih
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemilih') {
    header("Location: ../auth/login.php?pesan=belum_login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek periode voting aktif
$stmtPeriode = $pdo->query("SELECT * FROM periode_voting WHERE status = 'Aktif' AND NOW() BETWEEN tanggal_mulai AND tanggal_selesai");
$periodeAktif = $stmtPeriode->fetch();

// Ambil daftar kontestan
$kontestanList = $pdo->query("SELECT * FROM kontestan ORDER BY kategori, nomor_peserta ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilik Suara - E-Voting Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top center, #1e293b, #0f172a 70%);
            min-height: 100vh;
            color: #f8fafc;
        }
        .modern-card {
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
        }
        .modern-card:hover {
            transform: translateY(-4px);
            border-color: rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body>

    <!-- Navbar Terintegrasi Langsung -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding: 0.9rem 0; z-index: 1030;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="index.php">
                <div style="width: 38px; height: 38px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);">
                    <i class="bi bi-check2-square"></i>
                </div>
                <span class="fw-bold fs-5 text-white tracking-tight">E-Voting<span class="text-primary">.</span></span>
            </a>
            <button class="navbar-toggler border-0 shadow-none text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-3"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light" href="kontestan.php">Kontestan</a></li>
                    <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light" href="klasemen.php">Klasemen</a></li>
                    <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light bg-primary bg-opacity-10 text-primary fw-semibold" href="voting.php">Bilik Suara</a></li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle rounded-pill px-3 py-2 border-opacity-25 small fw-semibold" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1 text-primary"></i> <?= htmlspecialchars($_SESSION['nama']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border border-secondary border-opacity-25 rounded-3 mt-2 py-2">
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item py-2 small" href="../admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Panel Admin</a></li>
                                    <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item py-2 small text-danger" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div style="margin-top: 85px;"></div>

    <div class="container my-5">
        <div class="text-center mb-5">
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold mb-2"><i class="bi bi-shield-lock-fill me-1"></i> Bilik Suara Rahasia</span>
            <h2 class="fw-bold text-white display-6">Beri Suara Pilihanmu</h2>
            <p class="text-secondary small">Gunakan hak suara Anda dengan bijak. Pilihan Anda bersifat rahasia dan aman.</p>
        </div>

        <?php if (isset($_GET['pesan'])): ?>
            <?php if ($_GET['pesan'] == 'sudah_vote'): ?>
                <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger rounded-4 py-3 px-4 shadow-sm mb-4 d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                    <div>Anda sudah melakukan voting untuk kategori ini! Setiap pemilih hanya berhak 1 suara per kategori.</div>
                </div>
            <?php elseif ($_GET['pesan'] == 'periode_tutup'): ?>
                <div class="alert alert-warning bg-warning bg-opacity-10 border border-warning border-opacity-25 text-warning rounded-4 py-3 px-4 shadow-sm mb-4 d-flex align-items-center" role="alert">
                    <i class="bi bi-clock-history fs-5 me-3"></i>
                    <div>Periode voting sedang tidak aktif atau sudah ditutup.</div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$periodeAktif): ?>
            <div class="modern-card text-center p-5 mx-auto" style="max-width: 600px;">
                <div class="mb-3 text-warning display-4"><i class="bi bi-calendar-x"></i></div>
                <h4 class="fw-bold text-white mb-2">Mohon Maaf, Voting Belum Dibuka / Sudah Ditutup</h4>
                <p class="text-secondary mb-0">Silakan pantau jadwal melalui pengumuman administrator atau cek halaman klasemen.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($kontestanList as $row): ?>
                <div class="col-md-4">
                    <div class="card modern-card h-100 border-0 overflow-hidden d-flex flex-column">
                        <img src="../uploads/<?= htmlspecialchars($row['foto']) ?>" class="card-img-top object-fit-cover" style="height: 250px;">
                        <div class="card-body d-flex flex-column p-4">
                            <span class="badge bg-info bg-opacity-20 text-info mb-2 align-self-start px-3 py-2 rounded-pill">
                                <?= htmlspecialchars($row['kategori']) ?> &bull; No. <?= htmlspecialchars($row['nomor_peserta']) ?>
                            </span>
                            <h5 class="card-title fw-bold text-white fs-5 mb-1"><?= htmlspecialchars($row['nama']) ?></h5>
                            <p class="text-secondary small mb-3"><i class="bi bi-building me-1"></i> <?= htmlspecialchars($row['asal_instansi']) ?></p>
                            
                            <!-- Form khusus untuk tiap kontestan agar data terkirim dengan akurat -->
                            <div class="mt-auto pt-3 border-top border-secondary border-opacity-10">
                                <form action="../proses/simpan-voting.php" method="POST">
                                    <input type="hidden" name="kontestan_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-success w-100 py-2 rounded-3 fw-semibold shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;" onclick="return confirm('Yakin ingin memilih kontestan ini? Suara tidak dapat diubah setelah dikonfirmasi.')">
                                        <i class="bi bi-check-circle-fill me-1"></i> Pilih Kandidat Ini
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>