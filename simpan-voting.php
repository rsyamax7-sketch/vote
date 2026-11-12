<?php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login sebagai pemilih
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemilih') {
    header("Location: ../auth/login.php?pesan=belum_login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id      = $_SESSION['user_id'];
    $kontestan_id = $_POST['kontestan_id'] ?? null;

    if (!$kontestan_id) {
        header("Location: ../pages/voting.php?pesan=gagal");
        exit;
    }

    // 1. Cek apakah periode voting sedang aktif
    $stmtPeriode = $pdo->query("SELECT * FROM periode_voting WHERE status = 'Aktif' AND NOW() BETWEEN tanggal_mulai AND tanggal_selesai");
    $periodeAktif = $stmtPeriode->fetch();

    if (!$periodeAktif) {
        header("Location: ../pages/voting.php?pesan=periode_tutup");
        exit;
    }

    // 2. Ambil kategori kontestan yang dipilih dari database
    $stmtKontestan = $pdo->prepare("SELECT kategori FROM kontestan WHERE id = ?");
    $stmtKontestan->execute([$kontestan_id]);
    $dataKontestan = $stmtKontestan->fetch();

    if (!$dataKontestan) {
        header("Location: ../pages/voting.php?pesan=gagal");
        exit;
    }

    $kategoriPilihan = $dataKontestan['kategori'];

    // 3. Cek apakah pemilih sudah pernah memilih pada kategori yang sama
    $stmtCek = $pdo->prepare("
        SELECT v.id FROM voting v 
        JOIN kontestan k ON v.kontestan_id = k.id 
        WHERE v.user_id = ? AND k.kategori = ?
    ");
    $stmtCek->execute([$user_id, $kategoriPilihan]);

    if ($stmtCek->fetch()) {
        header("Location: ../pages/voting.php?pesan=sudah_vote");
        exit;
    }

    // 4. Simpan suara ke database
    try {
        $stmtInsert = $pdo->prepare("INSERT INTO voting (user_id, kontestan_id) VALUES (?, ?)");
        $stmtInsert->execute([$user_id, $kontestan_id]);

        // Alihkan ke halaman klasemen dengan pesan sukses
        header("Location: ../pages/klasemen.php?pesan=sukses_vote");
        exit;

    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
} else {
    header("Location: ../pages/voting.php");
    exit;
}
?>