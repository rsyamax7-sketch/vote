<?php
session_start();
require_once '../config/koneksi.php';

// 1. Hitung total seluruh suara yang masuk di tabel voting
$totalSuaraGlobal = $pdo->query("SELECT COUNT(*) FROM voting")->fetchColumn();
$divider = ($totalSuaraGlobal > 0) ? $totalSuaraGlobal : 1; 

// 2. Ambil data kontestan dan hitung total suara masing-masing menggunakan LEFT JOIN yang benar
$stmtKlasemen = $pdo->prepare("
    SELECT k.*, 
           COUNT(v.id) AS total_suara,
           (COUNT(v.id) / ? * 100) AS persentase
    FROM kontestan k
    LEFT JOIN voting v ON k.id = v.kontestan_id
    GROUP BY k.id
    ORDER BY total_suara DESC, k.nomor_peserta ASC
");
$stmtKlasemen->execute([$divider]);
$klasemenList = $stmtKlasemen->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klasemen Voting - E-Voting Modern</title>
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
        .table-custom { color: #f8fafc; background: transparent; }
        .table-custom th { background: rgba(15, 23, 42, 0.8) !important; color: #cbd5e1; border-color: rgba(255, 255, 255, 0.08); font-weight: 600; padding: 1rem; }
        .table-custom td { background: transparent !important; color: #f8fafc; border-color: rgba(255, 255, 255, 0.06); padding: 1rem; vertical-align: middle; }
        .table-custom tbody tr:hover { background: rgba(255, 255, 255, 0.02); }
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
                                        <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light bg-primary bg-opacity-10 text-primary fw-semibold" href="klasemen.php">Klasemen</a></li>
                    <li class="nav-item"><a class="nav-link px-3 py-2 rounded-3 text-light" href="voting.php">Bilik Suara</a></li>
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
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold mb-2">
                <i class="bi bi-trophy-fill me-1"></i> Live Ranking
            </span>
            <h2 class="fw-bold text-white display-6">Klasemen Sementara Perolehan Suara</h2>
            <p class="text-secondary small">Pemantauan hasil pemungutan suara secara real-time dan transparan.</p>
        </div>
        
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'sukses_vote'): ?>
            <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 text-success rounded-4 py-3 px-4 shadow-sm mb-4 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-3"></i>
                <div>Terima kasih! Suara Anda berhasil disimpan ke dalam sistem[cite: 5].</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="modern-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="rounded-start-4">Ranking</th>
                            <th>Foto</th>
                            <th>Nomor & Nama Kontestan</th>
                            <th>Kategori</th>
                            <th>Total Suara</th>
                            <th class="rounded-end-4" style="width: 30%;">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($klasemenList as $row): ?>
                        <tr>
                            <td>
                                <span class="fw-bold fs-5 <?= $no == 1 ? 'text-warning' : ($no == 2 ? 'text-light' : ($no == 3 ? 'text-info' : 'text-secondary')) ?>">
                                    #<?= $no++ ?>
                                </span>
                            </td>
                            <td>
                                <img src="../uploads/<?= htmlspecialchars($row['foto']) ?>" width="52" height="52" class="rounded-circle object-fit-cover border border-secondary border-opacity-25 shadow-sm">
                            </td>
                            <td>
                                <strong class="text-white fs-6"><?= htmlspecialchars($row['nama']) ?></strong><br>
                                <small class="text-secondary">No. <?= htmlspecialchars($row['nomor_peserta']) ?> &bull; <?= htmlspecialchars($row['asal_instansi']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-20 text-light px-3 py-2 rounded-pill"><?= htmlspecialchars($row['kategori']) ?></span>
                            </td>
                            <td><strong class="text-primary fs-6"><?= $row['total_suara'] ?> Suara</strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="progress flex-grow-1 bg-dark bg-opacity-50" style="height: 10px; border-radius: 99px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $row['persentase'] ?>%; border-radius: 99px;" aria-valuenow="<?= $row['persentase'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="small fw-semibold text-secondary" style="min-width: 45px;"><?= number_format($row['persentase'], 1) ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>