<?php
include '../koneksi.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => ''];

try {
    // Validasi input
    if (empty($_POST['id'])) {
        throw new Exception("ID tidak valid");
    }
    
    $id = (int)$_POST['id'];
    if ($id <= 0) {
        throw new Exception("ID harus berupa angka positif");
    }

    $required_fields = ['nama', 'jam_datang', 'alamat_asal', 'no_telp', 'jenis_kelamin', 'keperluan'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field $field harus diisi");
        }
    }

    $nama = $conn->real_escape_string($_POST['nama']);
    $jam_datang = $conn->real_escape_string($_POST['jam_datang']);
    $alamat_asal = $conn->real_escape_string($_POST['alamat_asal']);
    $no_telp = $conn->real_escape_string($_POST['no_telp']);
    $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
    $keperluan = $conn->real_escape_string($_POST['keperluan']);

    // 1. Dapatkan foto lama
    $stmt = $conn->prepare("SELECT foto FROM tamu WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_foto = $result->fetch_assoc()['foto'];
    $stmt->close();

    $foto = $old_foto;

    // 2. Handle upload foto baru jika ada
    if (!empty($_FILES['foto']['name'])) {
        $upload_dir = '../user/uploads/';
        $file_name = $_FILES['foto']['name'];
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_ext)) {
            throw new Exception("Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF");
        }

        $new_name = 'foto_' . time() . '.' . $file_ext;
        $destination = $upload_dir . $new_name;
        
        if (move_uploaded_file($file_tmp, $destination)) {
            $foto = 'uploads/' . $new_name;
            
            // Hapus foto lama jika ada
            if ($old_foto && file_exists('../user/' . $old_foto)) {
                unlink('../user/' . $old_foto);
            }
        }
    }

    // 3. Update data
    $stmt = $conn->prepare("UPDATE tamu SET nama=?, jam_datang=?, alamat_asal=?, no_telp=?, jenis_kelamin=?, keperluan=?, foto=? WHERE id=?");
    $stmt->bind_param("sssssssi", $nama, $jam_datang, $alamat_asal, $no_telp, $jenis_kelamin, $keperluan, $foto, $id);
    
    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Data berhasil diupdate';
    } else {
        throw new Exception("Gagal mengupdate data: " . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>