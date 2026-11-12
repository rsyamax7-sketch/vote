<?php
session_start();
require_once '../config/koneksi.php';

// Jika sudah login, langsung arahkan sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../pages/voting.php");
    }
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../pages/index.php");
        }
        exit;
    } else {
        $error = "Email atau kata sandi yang Anda masukkan salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Sistem - E-Voting Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e293b, #0f172a 70%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }
        .glow-orb-1 {
            position: absolute;
            width: 350px;
            height: 350px;
            background: rgba(37, 99, 235, 0.25);
            filter: blur(100px);
            border-radius: 50%;
            top: -80px;
            left: -80px;
            z-index: 0;
        }
        .glow-orb-2 {
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(56, 189, 248, 0.15);
            filter: blur(90px);
            border-radius: 50%;
            bottom: -60px;
            right: -60px;
            z-index: 0;
        }
        .auth-card {
            background: rgba(30, 41, 59, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }
        .brand-logo {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.6);
            margin-bottom: 1.25rem;
        }
        .form-label {
            color: #cbd5e1;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }
        .input-group-custom {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.85rem;
            transition: all 0.25s ease;
        }
        .input-group-custom:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            background: rgba(15, 23, 42, 0.85);
        }
        .input-group-custom .input-group-text {
            background: transparent;
            border: none;
            color: #64748b;
            padding-left: 1rem;
        }
        .input-group-custom .form-control {
            background: transparent;
            border: none;
            color: #ffffff;
            padding: 0.75rem 1rem 0.75rem 0.5rem;
            font-size: 0.95rem;
            box-shadow: none;
        }
        .input-group-custom .form-control::placeholder {
            color: #475569;
        }
        .btn-toggle-pass {
            background: transparent;
            border: none;
            color: #64748b;
            padding-right: 1rem;
        }
        .btn-toggle-pass:hover {
            color: #cbd5e1;
        }
        .btn-primary-modern {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 0.85rem;
            padding: 0.8rem;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.02em;
            color: white;
            box-shadow: 0 10px 20px -6px rgba(37, 99, 235, 0.6);
            transition: all 0.25s ease;
        }
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 24px -6px rgba(37, 99, 235, 0.75);
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }
        .auth-link {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .auth-link:hover {
            color: #93c5fd;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="glow-orb-1"></div>
    <div class="glow-orb-2"></div>

    <div class="auth-card">
        <div class="text-center mb-4">
            <a href="../pages/index.php" class="brand-logo text-decoration-none">
                <i class="bi bi-check2-square"></i>
            </a>
            <h1 class="h4 fw-bold text-white mb-1">Selamat Datang Kembali</h1>
            <p class="text-secondary small mb-0">Masuk untuk menyalurkan hak suara Anda</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger rounded-3 py-2 px-3 small d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2 fs-6"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['pesan'])): ?>
            <?php if ($_GET['pesan'] === 'sukses_daftar'): ?>
                <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 text-success rounded-3 py-2 px-3 small d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                    <div>Pendaftaran berhasil! Silakan masuk menggunakan akun Anda.</div>
                </div>
            <?php elseif ($_GET['pesan'] === 'akses_ditolak' || $_GET['pesan'] === 'belum_login'): ?>
                <div class="alert alert-warning bg-warning bg-opacity-10 border border-warning border-opacity-25 text-warning rounded-3 py-2 px-3 small d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-shield-exclamation me-2 fs-6"></i>
                    <div>Silakan masuk terlebih dahulu untuk mengakses halaman tersebut.</div>
                </div>
            <?php elseif ($_GET['pesan'] === 'logout'): ?>
                <div class="alert alert-info bg-info bg-opacity-10 border border-info border-opacity-25 text-info rounded-3 py-2 px-3 small d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-6"></i>
                    <div>Anda telah berhasil keluar dari sistem.</div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Alamat Email</label>
                <div class="input-group input-group-custom">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Kata Sandi</label>
                <div class="input-group input-group-custom">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Masukkan kata sandi" required>
                    <button type="button" class="btn-toggle-pass" onclick="togglePassword('passwordInput', 'toggleIcon')">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-modern w-100 mb-3">
                Masuk Sekarang <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="text-center mt-3 pt-3 border-top border-secondary border-opacity-25">
            <p class="text-secondary small mb-2">
                Belum memiliki akun pemilih? <a href="register.php" class="auth-link">Daftar di sini</a>
            </p>
            <a href="../pages/index.php" class="text-secondary small text-decoration-none d-inline-flex align-items-center gap-1 mt-1">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("bi-eye-slash", "bi-eye");
            }
        }
    </script>
</body>
</html>