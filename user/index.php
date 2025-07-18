<?php
require_once '../koneksi.php';

// Data wilayah Gunungkidul
$kabupaten = ['Gunungkidul'];
$kecamatan = [
    'Gedangsari', 'Girisubo', 'Karangmojo', 'Ngawen', 'Nglipar', 
    'Paliyan', 'Panggang', 'Patuk', 'Playen', 'Ponjong', 
    'Purwosari', 'Rongkop', 'Saptosari', 'Semanu', 'Semin', 
    'Tanjungsari', 'Tepus', 'Wonosari'
];
$desa = [
    'Gedangsari' => ['Banaran', 'Banyusoco', 'Dengok', 'Getas', 'Kemiri', 'Ngalang', 'Sawahan', 'Sodo', 'Watuagung'],
    'Girisubo' => ['Girijati', 'Girikarto', 'Girisuko', 'Jerukwudel', 'Jeringo', 'Mulo', 'Pucung', 'Pulutan', 'Selang', 'Tancep', 'Tambakromo'],
    // Add other kecamatan and desa here...
];

$genders = ['Laki-laki', 'Perempuan'];
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['nama'] ?? '';
    $instansi = $_POST['instansi'] ?? '';
    $address = $_POST['kabupaten'] . ', ' . $_POST['kecamatan'] . ', ' . $_POST['desa'];
    $phone = $_POST['no_telp'] ?? '';
    $gender = $_POST['jenis_kelamin'] ?? '';
    $purpose = $_POST['keperluan'] ?? '';
    $arrival_time = date('Y-m-d H:i:s');
    
    // Handle photo capture
    $photo_path = '';
    if (!empty($_POST['foto_data'])) {
        $imageData = $_POST['foto_data'];
        $filteredData = substr($imageData, strpos($imageData, ",")+1);
        $unencodedData = base64_decode($filteredData);
        $filename = 'uploads/foto_' . time() . '.png';
        file_put_contents($filename, $unencodedData);
        $photo_path = $filename;
    }
    
    // Validate form
    if (!empty($name) && !empty($address) && !empty($phone) && !empty($gender) && !empty($purpose)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO tamu (nama, instansi, jam_datang, alamat_asal, no_telp, jenis_kelamin, keperluan, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $name, $instansi, $arrival_time, $address, $phone, $gender, $purpose, $photo_path);
        
        if ($stmt->execute()) {
            $message = 'Data berhasil dikirim!';
            $message_type = 'success';
            
            // Clear form
            $_POST = [];
        } else {
            $message = 'Gagal menyimpan data: ' . $stmt->error;
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Mohon lengkapi semua data!';
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Buku Tamu Pemkab Gunungkidul</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Jaro:wght@400&family=ABeeZee:wght@400&family=ADLaM+Display:wght@400&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #00A700 0%, #193919 100%);
            font-family: 'ABeeZee', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .main-container {
            position: relative;
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .background-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .welcome-section {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }
        
        .welcome-title {
            font-family: 'ADLaM Display', serif;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin: 20px 0;
            line-height: 1.2;
        }
        
        .welcome-image {
            width: 200px;
            height: 190px;
            margin-bottom: 20px;
        }
        
        .government-logo {
            width: 70px;
            height: 90px;

        }
        
        .welcome-description {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* .elipse {
        background: #E8F5E8;
        border-radius: 100% 0% 100% 0%;
        padding: 30px;
        border: 1px solid rgba(255,255,255,0.3);
        top: 50%;
        left: 20%;
        position: absolute;
        position: relative;
        overflow: hidden;
    } */
        
        .form-container {
            background: #E8F5E8;
            border-radius: 100px 0 0 100px;
            transition: border-radius 0.3s ease;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 2000px; /* atau sesuaikan */
            height: auto; /* atau atur fixed height jika perlu */
        }


        
        .form-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-50%) translateY(-50%) rotate(0deg); }
            50% { transform: translateX(-50%) translateY(-50%) rotate(180deg); }
        }
        
        .form-label {
            font-weight: 600;
            color: #2E7D32;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        
        .form-control, .form-select {
            border-radius: 25px;
            border: 2px solid #E8F5E8;
            padding: 12px 20px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76,175,80,0.25);
            background: white;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 12px 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(46,125,50,0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(46,125,50,0.4);
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
        }
        
        .photo-section {
            background: #E8F5E8;
            border-radius: 20px;
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .photo-preview {
            width: 200px;
            height: 200px;
            border-radius: 15px;
            border: 3px solid #E8F5E8;
            object-fit: cover;
            background: rgba(255,255,255,0.9);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        
        .btn-capture {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E8E 100%);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            padding: 10px 25px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255,107,107,0.3);
        }
        
        .btn-capture:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,107,107,0.4);
            background: linear-gradient(135deg, #FF5252 0%, #FF6B6B 100%);
        }
        
        .photo-label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
            color: #2E7D32;
            font-size: 0.9rem;
        }
        
        .contact-section {
            text-align: center;
            padding: 30px 0;
            color: white;
            position: relative;
            z-index: 2;
        }
        
        .contact-item {
            display: inline-flex;
            align-items: center;
            margin: 0 20px;
            font-size: 0.9rem;
        }
        
        .contact-item i {
            margin-right: 8px;
            font-size: 1.2rem;
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            font-weight: 500;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            color: white;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #F44336 0%, #FF6B6B 100%);
            color: white;
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            color: white;
            border: none;
        }
        
        .camera-view {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .btn-camera {
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            padding: 10px 30px;
            transition: all 0.3s ease;
        }
        
        .btn-camera:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76,175,80,0.4);
        }
        
        @media (max-width: 768px) {
        .form-container {
            border-radius: 30px 30px 0 0; /* Atas kanan 30px, atas kiri 30px, bawah kanan 0, bawah kiri 0 */
            padding: 20px;
            max-width: 100%;
        }
        
        .welcome-title {
            font-size: 2rem;
        }
        
        .photo-preview {
            width: 150px;
            height: 150px;
        }
        
        .contact-item {
            margin: 10px 0;
            display: block;
        }
    }
    </style>
</head>
<body>
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="main-container">
        <div class="background-accent"></div>
        <div class="container">
            <div class="row">
                <!-- Left Column - Welcome Section -->  
                <div class="col-lg-4 col-md-12">
                    <img class="government-logo" src="../assets/logoGk.png" alt="Logo Kabupaten Gunungkidul" >
                    <div class="welcome-section">
                        <img class="welcome-image" src="../assets/iconGk.png" alt="Welcome illustration">
                        <h1 class="welcome-title">Selamat Datang di Portal Buku Tamu Pemkab Gunungkidul</h1>
                        <img >
                        <p class="welcome-description">Silakan lengkapi data kunjungan Anda untuk keperluan dokumentasi dan pelayanan.</p>
                    </div>
                </div>
                
                <!-- Right Column - Form and Photo Section -->
                <div class="col-lg-8 col-md-12">
                    <div class="elipse"></div>
                    <div class="form-container">
                        <div class="row">
                            <!-- Form Column -->
                            <div class="col-lg-8 col-md-7">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama :</label>
                                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh : Tisya" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="instansi" class="form-label">Dari :</label>
                                        <input type="text" class="form-control" id="instansi" name="instansi" placeholder="Contoh : PT. Handayani (Bapak Alex)">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="jam_datang" class="form-label">Jam Datang :</label>
                                        <input type="text" class="form-control" id="jam_datang" name="jam_datang" value="<?= date('H:i') ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Alamat/asal :</label>
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <select class="form-select" id="kabupaten" name="kabupaten" required>
                                                    <option value="" disabled selected>Pilih Kabupaten</option>
                                                    <?php foreach ($kabupaten as $kab): ?>
                                                        <option value="<?= $kab ?>"><?= $kab ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <select class="form-select" id="kecamatan" name="kecamatan" required disabled>
                                                    <option value="" disabled selected>Pilih Kecamatan</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <select class="form-select" id="desa" name="desa" required disabled>
                                                    <option value="" disabled selected>Pilih Desa</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="no_telp" class="form-label">No.telp :</label>
                                        <input type="text" class="form-control" id="no_telp" name="no_telp" placeholder="Contoh: 081390123163" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin :</label>
                                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                            <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="keperluan" class="form-label">Keperluan :</label>
                                        <input type="text" class="form-control" id="keperluan" name="keperluan" placeholder="Contoh : Mengirim surat undangan" required>
                                    </div>
                                    
                                    <input type="hidden" id="foto_data" name="foto_data">
                                    
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-submit">
                                            <i class="fas fa-paper-plane me-2"></i>KIRIM
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Photo Column -->
                            <div class="col-lg-4 col-md-5">
                                <div class="photo-section">
                                    <h5 class="text-center mb-3" style="color: #2E7D32;">Ambil Foto</h5>
                                    <img id="photo-preview" class="photo-preview" src="data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f8f9fa'/%3E%3Ccircle cx='100' cy='100' r='40' fill='%236c757d'/%3E%3Cpath d='M100 80 L100 120 M80 100 L120 100' stroke='white' stroke-width='3' stroke-linecap='round'/%3E%3C/svg%3E" alt="Photo preview">
                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-capture" id="open-camera">
                                            <i class="fas fa-camera me-2"></i>Capture
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Section -->
        <div class="contact-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <div class="contact-item">
                            <i class="fab fa-instagram"></i>
                            <span>kominfoGunungkidul</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>kominfo@Gunungkidulkab.go.id</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Camera Modal -->
    <div class="modal fade" id="camera-modal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cameraModalLabel">
                        <i class="fas fa-camera me-2"></i>Ambil Foto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <video id="camera-view" class="camera-view" autoplay playsinline></video>
                    <canvas id="camera-canvas" style="display:none;"></canvas>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-camera" id="capture-btn">
                        <i class="fas fa-camera me-2"></i>Ambil Foto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Update arrival time every second
        function updateTime() {
            const now = new Date();
            const options = { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false
            };
            document.getElementById('jam_datang').value = now.toLocaleString('id-ID', options);
        }
        
        setInterval(updateTime, 1000);
        updateTime();
        
        // Address selection logic
        document.getElementById('kabupaten').addEventListener('change', function() {
            const kabupaten = this.value;
            const kecamatanSelect = document.getElementById('kecamatan');
            
            if (kabupaten) {
                kecamatanSelect.innerHTML = '<option value="" disabled selected>Pilih Kecamatan</option>';
                
                const kecamatanList = <?= json_encode($kecamatan) ?>;
                
                kecamatanList.forEach(kec => {
                    const option = document.createElement('option');
                    option.value = kec;
                    option.textContent = kec;
                    kecamatanSelect.appendChild(option);
                });
                
                kecamatanSelect.disabled = false;
            } else {
                kecamatanSelect.disabled = true;
                document.getElementById('desa').disabled = true;
            }
        });
        
        document.getElementById('kecamatan').addEventListener('change', function() {
            const kecamatan = this.value;
            const desaSelect = document.getElementById('desa');
            
            if (kecamatan) {
                desaSelect.innerHTML = '<option value="" disabled selected>Pilih Desa</option>';
                
                const desaList = <?= json_encode($desa) ?>;
                const selectedDesa = desaList[kecamatan] || [];
                
                selectedDesa.forEach(desa => {
                    const option = document.createElement('option');
                    option.value = desa;
                    option.textContent = desa;
                    desaSelect.appendChild(option);
                });
                
                desaSelect.disabled = false;
            } else {
                desaSelect.disabled = true;
            }
        });

        // Camera functionality
        const cameraModal = new bootstrap.Modal(document.getElementById('camera-modal'));
        const openCameraBtn = document.getElementById('open-camera');
        const cameraView = document.getElementById('camera-view');
        const captureBtn = document.getElementById('capture-btn');
        const cameraCanvas = document.getElementById('camera-canvas');
        const photoPreview = document.getElementById('photo-preview');
        const fotoDataInput = document.getElementById('foto_data');
        
        let stream = null;
        
        openCameraBtn.addEventListener('click', async () => {
            try {
                cameraModal.show();
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }, 
                    audio: false 
                });
                cameraView.srcObject = stream;
            } catch (err) {
                console.error("Error accessing camera: ", err);
                alert("Tidak dapat mengakses kamera. Pastikan Anda memberikan izin.");
            }
        });
        
        document.getElementById('camera-modal').addEventListener('hidden.bs.modal', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
        
        captureBtn.addEventListener('click', () => {
            // Set canvas size to match video frame
            cameraCanvas.width = cameraView.videoWidth;
            cameraCanvas.height = cameraView.videoHeight;
            
            // Draw current video frame to canvas
            const ctx = cameraCanvas.getContext('2d');
            ctx.drawImage(cameraView, 0, 0, cameraCanvas.width, cameraCanvas.height);
            
            // Convert canvas to data URL and display as preview
            const imageData = cameraCanvas.toDataURL('image/png');
            photoPreview.src = imageData;
            fotoDataInput.value = imageData;
            
            // Close camera
            cameraModal.hide();
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
        
        // Auto-close alert after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>