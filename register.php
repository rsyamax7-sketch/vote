<?php
session_start();
require_once '../config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../pages/voting.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $no_hp    = trim($_POST['no_hp']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];

    if ($password !== $konfirmasi) {
        $error = "Konfirmasi kata sandi tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Kata sandi minimal harus terdiri dari 6 karakter!";
    } else {
        // Cek apakah email sudah terdaftar
        $cekEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $cekEmail->execute([$email]);

        if ($cekEmail->fetch()) {
            $error = "Alamat email sudah terdaftar! Silakan gunakan email lain atau masuk.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, no_hp, password, role) VALUES (?, ?, ?, ?, 'pemilih')");
            
            if ($stmt->execute([$nama, $email, $no_hp, $hashedPassword])) {
                header("Location: login.php?pesan=sukses_daftar");
                exit;
            } else {
                $error = "Terjadi kesalahan saat mendaftarkan akun. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Pemilih - E-Voting Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top left, #1e293b, #0f172a 70%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            padding: 2rem 1.5rem;
            position: relative;
            overflow-x: hidden;
        }
        .glow-orb-1 {
            position: absolute;
            width: 380px;
            height: 380px;
            background: rgba(37, 99, 235, 0.22);
            filter: blur(110px);
            border-radius: 50%;
            top: -100px;
            right: -80px;
            z-index: 0;
        }
        .glow-orb-2 {
            position: absolute;
            width: 320px;
            height: 320px;
            background: rgba(16, 185, 129, 0.14);
            filter: blur(100px);
            border-radius: 50%;
            bottom: -80px;
            left: -60px;
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
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }
        .brand-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: white;
            box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.6);
            margin-bottom: 1rem;
        }
        .form-label {
            color: #cbd5e1;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
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
            padding: 0.7rem 1rem 0.7rem 0.5rem;
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
                <i class="bi bi-person-plus-fill"></i>
            </a>
            <h1 class="h4 fw-bold text-white mb-1">Buat Akun Pemilih</h1>
            <p class="text-secondary small mb-0">Lengkapi data diri untuk berpartisipasi dalam pemilihan</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger rounded-3 py-2 px-3 small d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2 fs-6"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group input-group-custom">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap Anda" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat Email</label>
                <div class="input-group input-group-custom">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nomor WhatsApp / HP</label>
                <div class="input-group input-group-custom">
                    <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                    <input type="tel" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" value="<?= isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : '' ?>" required>
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Kata Sandi</label>
                    <div class="input-group input-group-custom">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="regPass" class="form-control" placeholder="Min. 6 karakter" required>
                        <button type="button" class="btn-toggle-pass" onclick="togglePassword('regPass', 'iconReg1')">
                            <i class="bi bi-eye" id="iconReg1"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ulangi Sandi</label>
                    <div class="input-group input-group-custom">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                        <input type="password" name="konfirmasi_password" id="regConfirmPass" class="form-control" placeholder="Ulangi sandi" required>
                        <button type="button" class="btn-toggle-pass" onclick="togglePassword('regConfirmPass', 'iconReg2')">
                            <i class="bi bi-eye" id="iconReg2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-modern w-100 mb-3">
                Daftar Akun Sekarang <i class="bi bi-check2-circle ms-1"></i>
            </button>
        </form>

        <div class="text-center mt-3 pt-3 border-top border-secondary border-opacity-25">
            <p class="text-secondary small mb-2">
                Sudah memiliki akun? <a href="login.php" class="auth-link">Masuk di sini</a>
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