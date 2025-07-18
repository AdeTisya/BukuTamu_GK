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
    <link href="https://fonts.googleapis.com/css2?family=Jaro:wght@400&family=ABeeZee:wght@400&family=ADLaM+Display:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="mystyle.css">
</head>
<body>
    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <main class="main-container">
        <div class="background-accent"></div>
        <div class="form-background-ellipse"></div>
        <section class="visitor-form-container">
            <form class="visitor-form" method="POST" enctype="multipart/form-data">
                <!-- Name and Instansi fields side by side -->
                <div class="form-field name-field">
                    <label class="field-label name-label">Nama :</label>
                    <input type="text" id="nama" name="nama" placeholder="Contoh : Tisya" required>
                </div>
                
                <div class="form-field instansi-field">
                    <label class="field-label instansi-label"> Dari  :</label>
                    <input type="text" id="instansi" name="instansi" placeholder="Contoh : PT. Handayani (Bapak Alex)">
                </div>
                
                <!-- Time field -->
                <div class="form-field time-field">
                    <label class="field-label time-label">Jam Datang :</label>
                    <input type="text" id="jam_datang" name="jam_datang" value="<?= date('H:i') ?>" readonly>
                </div>
                
                <!-- Address fields -->
                <div class="form-field address-field">
                    <label class="field-label address-label">Alamat/asal :</label>
                    <select id="kabupaten" name="kabupaten" required>
                        <option value="" disabled selected>Kabupaten</option>
                        <?php foreach ($kabupaten as $kab): ?>
                            <option value="<?= $kab ?>"><?= $kab ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select id="kecamatan" name="kecamatan" required disabled>
                        <option value="" disabled selected>Kecamatan</option>
                    </select>
                    
                    <select id="desa" name="desa" required disabled>
                        <option value="" disabled selected>Desa</option>
                    </select>
                </div>
                
                <!-- Phone field -->
                <div class="form-field phone-field">
                    <label class="field-label phone-label">No.telp :</label>
                    <input type="text" id="no_telp" name="no_telp" placeholder="Contoh: 081390123163" required>
                </div>
                
                <!-- Gender field -->
                <div class="form-field gender-field">
                    <label class="field-label gender-label">Jenis Kelamin :</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                
                <!-- Purpose field -->
                <div class="form-field purpose-field">
                    <label class="field-label purpose-label">Keperluan : </label>
                    <input type="text" id="keperluan" name="keperluan" placeholder="Contoh : Mengirim surat undangan" required>
                </div>
                
                <input type="hidden" id="foto_data" name="foto_data">
                
                <button type="submit" class="submit-button">KIRIM</button>
            </form>
        </section>

        <section class="photo-capture-section">
            <img id="photo-preview" class="photo-preview" src="data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='317' height='316' viewBox='0 0 317 316'%3E%3Crect width='315' height='314' x='1' y='1' fill='%23ddd' stroke='%23000' stroke-width='2'/%3E%3Ctext x='50%' y='50%' font-family='Arial' font-size='16' text-anchor='middle' dominant-baseline='middle' fill='%23666'%3EFoto Preview%3C/text%3E%3C/svg%3E" alt="Photo preview">

            <button type="button" class="capture-button" id="open-camera">Capture</button>
            <label class="photo-label">Ambil Foto</label>
        </section>

        <header class="welcome-section">
            <img class="welcome-image" src="../assets/iconGk.png" alt="Welcome illustration">
            <h1 class="welcome-title">Selamat Datang di Portal Buku Tamu Pemkab Gunungkidul</h1>
            <img class="government-logo" src="../assets/logoGk.png" alt="Logo Kabupaten Gunungkidul">
            <p class="welcome-description">Silakan lengkapi data kunjungan Anda untuk keperluan dokumentasi dan pelayanan.</p>
        </header>

        <footer class="contact-section">
            <img class="instagram-icon" src="../assets/IG.webp" alt="Welcome illustration" width="30" height="30">
            <img class="email-icon" width="30" height="30" viewBox="0 0 30 30" fill="none" src="../assets/email.png">
            <span class="contact-email">kominfo@Gunungkidulkab.go.id</span>
            <span class="social-handle">kominfoGunungkidul</span>
        </footer>
    </main>

    <!-- Camera Modal -->
    <div id="camera-modal" class="modal">
        <span id="close-camera">&times;</span>
        <div class="modal-content">
            <video id="camera-view" autoplay playsinline></video>
            <button id="capture-btn">Ambil Foto</button>
            <canvas id="camera-canvas" style="display:none;"></canvas>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
                kecamatanSelect.innerHTML = '<option value="" disabled selected>Kecamatan</option>';
                
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
                desaSelect.innerHTML = '<option value="" disabled selected>Desa</option>';
                
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
        const cameraModal = document.getElementById('camera-modal');
        const openCameraBtn = document.getElementById('open-camera');
        const closeCameraBtn = document.getElementById('close-camera');
        const cameraView = document.getElementById('camera-view');
        const captureBtn = document.getElementById('capture-btn');
        const cameraCanvas = document.getElementById('camera-canvas');
        const photoPreview = document.getElementById('photo-preview');
        const fotoDataInput = document.getElementById('foto_data');
        
        let stream = null;
        
        openCameraBtn.addEventListener('click', async () => {
            try {
                cameraModal.style.display = 'block';
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
        
        closeCameraBtn.addEventListener('click', () => {
            cameraModal.style.display = 'none';
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
            cameraModal.style.display = 'none';
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
        
        // Auto-close success/error message after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.message');
            messages.forEach(msg => msg.style.display = 'none');
        }, 5000);
    </script>
</body>
</html>