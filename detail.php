<?php
session_start();
require_once '../config/koneksi.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: kontestan.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM kontestan WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    echo "Kontestan tidak ditemukan!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail - <?= htmlspecialchars($row['nama']) ?></title>
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
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .text-custom-muted { color: #94a3b8; }
    </style>
</head>
<body>

    <!-- Navbar Modern Terintegrasi Langsung -->
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
                    <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light bg-primary bg-opacity-10 text-primary fw-semibold" href="kontestan.php">Kontestan</a></li>
                    <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light" href="voting.php">Bilik Suara</a></li>
                    <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light" href="klasemen.php">Klasemen</a></li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <?php if (isset($_SESSION['user_id'])): ?>
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
                        <?php else: ?>
                            <a href="../auth/login.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none;">Masuk <i class="bi bi-arrow-right ms-1"></i></a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div style="margin-top: 85px;"></div>

    <div class="container my-5">
        <a href="kontestan.php" class="btn btn-outline-light rounded-pill px-4 mb-4 fw-semibold border-opacity-25">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kontestan
        </a>

        <div class="modern-card p-4 p-md-5">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 text-center">
                    <img src="../uploads/<?= htmlspecialchars($row['foto']) ?>" class="img-fluid rounded-4 shadow-lg object-fit-cover w-100 border border-secondary border-opacity-25" style="max-height: 420px;" alt="">
                </div>
                <div class="col-md-8">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold mb-3">
                        Nomor Peserta: <?= htmlspecialchars($row['nomor_peserta']) ?>
                    </span>
                    <h2 class="fw-bold text-white mb-2 display-6"><?= htmlspecialchars($row['nama']) ?></h2>
                    <h5 class="text-custom-muted mb-4 fs-6">
                        <i class="bi bi-tag-fill me-1 text-primary"></i> <?= htmlspecialchars($row['kategori']) ?> &bull; 
                        <i class="bi bi-building me-1 ms-2 text-primary"></i> <?= htmlspecialchars($row['asal_instansi']) ?>
                    </h5>
                    
                    <hr class="border-secondary opacity-25 my-4">
                    
                    <div class="mb-4">
                        <h6 class="fw-bold text-white mb-2"><i class="bi bi-file-text-fill text-primary me-2"></i>Profil / Deskripsi:</h6>
                        <p class="text-custom-muted lh-base mb-0"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-4 bg-dark bg-opacity-50 border border-secondary border-opacity-10 h-100">
                                <h6 class="fw-bold text-white mb-2"><i class="bi bi-eye-fill text-info me-2"></i>Visi:</h6>
                                <p class="text-custom-muted small mb-0"><?= nl2br(htmlspecialchars($row['visi'])) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-4 bg-dark bg-opacity-50 border border-secondary border-opacity-10 h-100">
                                <h6 class="fw-bold text-white mb-2"><i class="bi bi-rocket-takeoff-fill text-success me-2"></i>Misi:</h6>
                                <p class="text-custom-muted small mb-0"><?= nl2br(htmlspecialchars($row['misi'])) ?></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>