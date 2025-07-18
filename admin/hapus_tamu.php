<?php
include '../koneksi.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak diketahui'];

try {
    // Validasi ID
    if (!isset($_POST['id'])) {
        throw new Exception("ID tidak valid");
    }
    
    $id = (int)$_POST['id'];
    if ($id <= 0) {
        throw new Exception("ID harus berupa angka positif");
    }

    // 1. Ambil informasi foto sebelum menghapus
    $stmt = $conn->prepare("SELECT foto FROM tamu WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement gagal: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $foto_path = null;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['foto'])) {
            $foto_path = '../user/' . $row['foto'];
        }
    }
    $stmt->close();

    // 2. Hapus data dari database
    $stmt = $conn->prepare("DELETE FROM tamu WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement gagal: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if (!$success || $affected_rows === 0) {
        throw new Exception("Gagal menghapus data atau data tidak ditemukan");
    }

    // 3. Hapus file foto jika ada
    if ($foto_path && file_exists($foto_path)) {
        if (!unlink($foto_path)) {
            $response['status'] = 'warning';
            $response['message'] = 'Data berhasil dihapus tetapi gagal menghapus file foto';
            echo json_encode($response);
            exit;
        }
    }

    $response['status'] = 'success';
    $response['message'] = 'Data berhasil dihapus';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Error in hapus_tamu.php: " . $e->getMessage());
}

echo json_encode($response);
?>