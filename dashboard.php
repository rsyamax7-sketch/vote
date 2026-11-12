<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?pesan=akses_ditolak");
    exit;
}

// Ambil statistik utama
$totalKontestan = $pdo->query("SELECT COUNT(*) FROM kontestan")->fetchColumn();
$totalPemilih   = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pemilih'")->fetchColumn();
$totalSuara     = $pdo->query("SELECT COUNT(*) FROM voting")->fetchColumn();

// Ambil status periode aktif
$stmtPeriode  = $pdo->query("SELECT * FROM periode_voting WHERE status = 'Aktif' LIMIT 1");
$periodeAktif = $stmtPeriode->fetch();

// Ambil data untuk Grafik Perolehan Suara
$stmtChart = $pdo->query("
    SELECT k.nama, COUNT(v.id) AS jumlah_suara 
    FROM kontestan k 
    LEFT JOIN voting v ON k.id = v.kontestan_id 
    GROUP BY k.id 
    ORDER BY jumlah_suara DESC
");
$dataChart = $stmtChart->fetchAll();

$labelsChart = [];
$valuesChart = [];
foreach ($dataChart as $row) {
    $labelsChart[] = $row['nama'];
    $valuesChart[] = (int)$row['jumlah_suara'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Aplikasi Voting</title>
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Modern Sidebar */
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

        .sidebar .nav-link i {
            font-size: 1.1rem;
            transition: transform 0.2s ease;
        }

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

        .btn-logout:hover {
            background: #ef4444;
            color: white;
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-left: 260px;
            padding: 2rem 2.5rem;
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.25rem 1.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        /* Modern Stat Cards */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Content Card */
        .modern-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        }

        /* Status Pulse Indicator */
        .pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .pulse-active {
            background-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        }
        .pulse-inactive {
            background-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>

    <!-- Modern Sidebar -->
    <aside class="sidebar" id="adminSidebar">
        <a href="dashboard.php" class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-check2-square"></i>
            </div>
            <div>
                <span class="text-white fw-bold fs-6 d-block lh-1">E-Voting</span>
                <small class="text-secondary" style="font-size: 0.75rem;">Administrator</small>
            </div>
        </a>

        <div class="sidebar-heading">Menu Utama</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="kontestan.php" class="nav-link">
                    <i class="bi bi-people-fill"></i> Kelola Kontestan
                </a>
            </li>
            <li class="nav-item">
                <a href="pemilih.php" class="nav-link">
                    <i class="bi bi-person-badge-fill"></i> Kelola Pemilih
                </a>
            </li>
            <li class="nav-item">
                <a href="periode.php" class="nav-link">
                    <i class="bi bi-calendar2-week-fill"></i> Periode Voting
                </a>
            </li>
        </ul>

        <div class="sidebar-heading mt-3">Pintasan Publik</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="../pages/klasemen.php" target="_blank" class="nav-link">
                    <i class="bi bi-trophy-fill text-warning"></i> Lihat Klasemen
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-left"></i> Keluar Sistem
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <div class="top-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none border" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div>
                    <h1 class="h4 fw-bold mb-1">Dashboard Statistik</h1>
                    <p class="text-muted small mb-0">Pantau perolehan suara, kandidat, dan status pemilihan secara real-time.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none sm-block">
                    <span class="d-block fw-semibold small"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill" style="font-size: 0.7rem;">Super Admin</span>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 42px; height: 42px;">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                </div>
            </div>
        </div>

        <!-- Stat Cards Grid -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Total Kontestan</span>
                            <h2 class="fw-extrabold mb-0" style="font-weight: 800;"><?= $totalKontestan ?></h2>
                            <small class="text-primary fw-medium mt-2 d-inline-block">Kandidat terdaftar</small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Pemilih Terdaftar</span>
                            <h2 class="fw-extrabold mb-0" style="font-weight: 800;"><?= $totalPemilih ?></h2>
                            <small class="text-success fw-medium mt-2 d-inline-block">Akun terverifikasi</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Total Suara Masuk</span>
                            <h2 class="fw-extrabold mb-0" style="font-weight: 800;"><?= $totalSuara ?></h2>
                            <small class="text-warning fw-medium mt-2 d-inline-block">Partisipasi voting</small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik & Status Periode -->
        <div class="row g-4">
            <!-- Grafik Perolehan Suara -->
            <div class="col-lg-8">
                <div class="modern-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-0">Grafik Perolehan Suara</h5>
                            <small class="text-muted">Perbandingan suara sementara antar kontestan</small>
                        </div>
                        <a href="../pages/klasemen.php" class="btn btn-sm btn-light border rounded-pill px-3">Detail Klasemen &rarr;</a>
                    </div>
                    <?php if (count($labelsChart) > 0): ?>
                        <div style="height: 280px;">
                            <canvas id="votingChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart fs-1 d-block mb-2 opacity-50"></i>
                            Belum ada data kontestan atau suara untuk ditampilkan pada grafik.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kartu Status Periode -->
            <div class="col-lg-4">
                <div class="modern-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1">Status Periode Voting</h5>
                        <p class="text-muted small mb-4">Kontrol jadwal akses pemilih</p>

                        <?php if ($periodeAktif): ?>
                            <div class="p-3 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="pulse-dot pulse-active"></span>
                                    <span class="fw-bold text-success small text-uppercase">Sesi Voting Aktif</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-2"><?= htmlspecialchars($periodeAktif['nama_periode']) ?></h6>
                                <div class="small text-muted">
                                    <div class="mb-1"><i class="bi bi-play-circle me-1 text-success"></i> Mulai: <strong><?= date('d M Y, H:i', strtotime($periodeAktif['tanggal_mulai'])) ?></strong></div>
                                    <div><i class="bi bi-stop-circle me-1 text-danger"></i> Selesai: <strong><?= date('d M Y, H:i', strtotime($periodeAktif['tanggal_selesai'])) ?></strong></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="p-3 rounded-4 bg-danger bg-opacity-10 border border-danger border-opacity-25 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="pulse-dot pulse-inactive"></span>
                                    <span class="fw-bold text-danger small text-uppercase">Voting Ditutup / Nonaktif</span>
                                </div>
                                <p class="small text-muted mb-0">Saat ini pemilih tidak dapat memberikan suara karena tidak ada jadwal aktif.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <a href="periode.php" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold shadow-sm mt-3">
                        <i class="bi bi-gear-fill me-1"></i> Kelola Jadwal Voting
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php if (count($labelsChart) > 0): ?>
    <script>
        const ctx = document.getElementById('votingChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelsChart) ?>,
                datasets: [{
                    label: 'Jumlah Suara',
                    data: <?= json_encode($valuesChart) ?>,
                    backgroundColor: 'rgba(37, 99, 235, 0.85)',
                    borderColor: '#1d4ed8',
                    borderWidth: 1,
                    borderRadius: 8,
                    barThickness: 38
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Plus Jakarta Sans' } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { family: 'Plus Jakarta Sans', weight: '600' } },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>