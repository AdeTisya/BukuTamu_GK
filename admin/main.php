<?php 
include '../koneksi.php';

// Proses tambah data
if(isset($_POST['tambah'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
    $no_telp = $conn->real_escape_string($_POST['no_telp']);
    $alamat_asal = $conn->real_escape_string($_POST['alamat_asal']);
    $keperluan = $conn->real_escape_string($_POST['keperluan']);
    $jam_datang = date('Y-m-d H:i:s');
    
    // Handle file upload
    $foto = '';
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../user/'.$foto);
    }
    
    $stmt = $conn->prepare("INSERT INTO tamu (nama, jenis_kelamin, no_telp, alamat_asal, keperluan, jam_datang, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nama, $jenis_kelamin, $no_telp, $alamat_asal, $keperluan, $jam_datang, $foto);
    $stmt->execute();
    $stmt->close();
    
    header("Location: ".$_SERVER['PHP_SELF']."?success=tambah");
    exit();
}

// Proses edit data
if(isset($_POST['edit'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $nama = $conn->real_escape_string($_POST['nama']);
    $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
    $no_telp = $conn->real_escape_string($_POST['no_telp']);
    $alamat_asal = $conn->real_escape_string($_POST['alamat_asal']);
    $keperluan = $conn->real_escape_string($_POST['keperluan']);
    $jam_datang = $conn->real_escape_string($_POST['jam_datang']);
    
    // Get current foto
    $current_foto = $conn->query("SELECT foto FROM tamu WHERE id='$id'")->fetch_assoc()['foto'];
    $foto = $current_foto;
    
    // Handle file upload if new foto is provided
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // Delete old foto if exists
        if(!empty($current_foto) && file_exists('../user/'.$current_foto)) {
            unlink('../user/'.$current_foto);
        }
        
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../user/'.$foto);
    }
    
    $stmt = $conn->prepare("UPDATE tamu SET nama=?, jenis_kelamin=?, no_telp=?, alamat_asal=?, keperluan=?, jam_datang=?, foto=? WHERE id=?");
    $stmt->bind_param("sssssssi", $nama, $jenis_kelamin, $no_telp, $alamat_asal, $keperluan, $jam_datang, $foto, $id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: ".$_SERVER['PHP_SELF']."?success=edit");
    exit();
}

// Proses hapus data
if(isset($_POST['hapus'])) {
    $id = $conn->real_escape_string($_POST['id']);
    
    // Get foto to delete
    $foto = $conn->query("SELECT foto FROM tamu WHERE id='$id'")->fetch_assoc()['foto'];
    
    // Delete record
    $conn->query("DELETE FROM tamu WHERE id='$id'");
    
    // Delete foto if exists
    if(!empty($foto) && file_exists('../user/'.$foto)) {
        unlink('../user/'.$foto);
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."?success=hapus");
    exit();
}

// Pagination logic
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of records
$total_records = $conn->query("SELECT COUNT(*) as total FROM tamu")->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get records for current page
$result = $conn->query("SELECT * FROM tamu ORDER BY id DESC LIMIT $offset, $records_per_page");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Buku Tamu | Pemkab Gunungkidul</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #2e6d30;
      --secondary-color: #71d899;
      --accent-color: #f8f9fa;
      --text-dark: #343a40;
      --text-light: #6c757d;
      --danger-color: #dc3545;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fa;
      color: var(--text-dark);
    }
    
    .navbar-brand {
      font-weight: 600;
      color: var(--primary-color);
    }
    
    .main-content {
      padding: 30px;
    }
    
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .card-header {
      background-color: white;
      border-bottom: 1px solid rgba(0,0,0,0.05);
      font-weight: 600;
      padding: 15px 20px;
      border-radius: 10px 10px 0 0 !important;
    }
    
    .table-responsive {
      border-radius: 10px;
      overflow: hidden;
    }
    
    .table {
      margin-bottom: 0;
    }
    
    .table th {
      background-color: var(--primary-color);
      color: white;
      font-weight: 500;
      padding: 15px;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
    }
    
    .table td {
      padding: 12px 15px;
      vertical-align: middle;
      border-top: 1px solid rgba(0,0,0,0.03);
    }
    
    .table tr:hover {
      background-color: rgba(113, 216, 153, 0.05);
    }
    
    .btn-action {
      border-radius: 50px;
      padding: 8px 15px;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.3s;
    }
    
    .btn-edit {
      background-color: #ffc107;
      color: var(--text-dark);
      border: none;
    }
    
    .btn-edit:hover {
      background-color: #e0a800;
      transform: translateY(-2px);
    }
    
    .btn-delete {
      background-color: var(--danger-color);
      color: white;
      border: none;
    }
    
    .btn-delete:hover {
      background-color: #c82333;
      transform: translateY(-2px);
    }
    
    .badge-status {
      padding: 6px 10px;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .modal-content {
      border: none;
      border-radius: 10px;
      overflow: hidden;
    }
    
    .modal-header {
      background-color: var(--primary-color);
      color: white;
      padding: 15px 20px;
    }
    
    .modal-body {
      padding: 25px;
    }
    
    .form-control, .form-select {
      border-radius: 5px;
      padding: 10px 15px;
      border: 1px solid rgba(0,0,0,0.1);
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 0.25rem rgba(113, 216, 153, 0.25);
    }
    
    .profile-img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid white;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .search-box {
      position: relative;
      max-width: 300px;
    }
    
    .search-box input {
      padding-left: 40px;
      border-radius: 50px;
    }
    
    .search-box i {
      position: absolute;
      left: 15px;
      top: 12px;
      color: var(--text-light);
    }
    
    .stats-card {
      border-left: 4px solid var(--primary-color);
    }
    
    .stats-card .card-body {
      padding: 15px;
    }
    
    .stats-card h5 {
      font-size: 0.9rem;
      color: var(--text-light);
      margin-bottom: 5px;
    }
    
    .stats-card h3 {
      font-weight: 600;
      color: var(--primary-color);
    }
    
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }
    
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade {
      animation: fadeIn 0.5s ease-out forwards;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .main-content {
        padding: 15px;
      }
      
      .table-responsive {
        overflow-x: auto;
      }
    }
    
    .pagination .page-item.active .page-link {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .pagination .page-link {
      color: var(--primary-color);
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Main Content -->
    <main class="col-12 px-md-4 main-content animate-fade">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h4 fw-bold" style="color: var(--primary-color);">
          <i class="bi bi-people me-2"></i>Data Buku Tamu
        </h1>
        
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExport">
              <i class="bi bi-download"></i> Export
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPrint">
              <i class="bi bi-printer"></i> Print
            </button>
          </div>
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari tamu...">
          </div>
        </div>
      </div>
      
      <!-- Notifikasi -->
      <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-<?php echo $_GET['success'] === 'hapus' ? 'danger' : 'success'; ?> alert-dismissible fade show">
          <i class="bi bi-check-circle me-2"></i>
          <?php 
          switch($_GET['success']) {
            case 'tambah': echo 'Data tamu berhasil ditambahkan'; break;
            case 'edit': echo 'Data tamu berhasil diperbarui'; break;
            case 'hapus': echo 'Data tamu berhasil dihapus'; break;
          }
          ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card stats-card">
            <div class="card-body">
              <h5>Total Tamu</h5>
              <h3>
                <?php 
                echo number_format($total_records);
                ?>
              </h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card">
            <div class="card-body">
              <h5>Hari Ini</h5>
              <h3>
                <?php 
                $today = $conn->query("SELECT COUNT(*) as total FROM tamu WHERE DATE(jam_datang) = CURDATE()")->fetch_assoc()['total'];
                echo number_format($today);
                ?>
              </h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card">
            <div class="card-body">
              <h5>Bulan Ini</h5>
              <h3>
                <?php 
                $month = $conn->query("SELECT COUNT(*) as total FROM tamu WHERE MONTH(jam_datang) = MONTH(CURDATE()) AND YEAR(jam_datang) = YEAR(CURDATE())")->fetch_assoc()['total'];
                echo number_format($month);
                ?>
              </h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card">
            <div class="card-body">
              <h5>Tahun Ini</h5>
              <h3>
                <?php 
                $year = $conn->query("SELECT COUNT(*) as total FROM tamu WHERE YEAR(jam_datang) = YEAR(CURDATE())")->fetch_assoc()['total'];
                echo number_format($year);
                ?>
              </h3>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Main Card -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <i class="bi bi-table me-2"></i>Daftar Tamu
          </div>
          <div>
            <button class="btn btn-sm" style="background-color: var(--primary-color); color: white;" data-bs-toggle="modal" data-bs-target="#tambahModal">
              <i class="bi bi-plus-circle"></i> Tambah Data
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="tamuTable">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama</th>
                  <th>Waktu</th>
                  <th>Kontak</th>
                  <th>Jenis Kelamin</th>
                  <th>Keperluan</th>
                  <th>Foto</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = ($page - 1) * $records_per_page + 1;
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>
                    <td>{$no}</td>
                    <td>
                      <div class='d-flex align-items-center'>
                        ".(!empty($row['foto']) ? "<img src='../user/{$row['foto']}' class='user-avatar me-2'>" : "<div class='user-avatar bg-light me-2 d-flex align-items-center justify-content-center'><i class='bi bi-person text-muted'></i></div>")."
                        <span>{$row['nama']}</span>
                      </div>
                    </td>
                    <td>".date('d M Y H:i', strtotime($row['jam_datang']))."</td>
                    <td>{$row['no_telp']}</td>
                    <td>{$row['jenis_kelamin']}</td>
                    <td>".substr($row['keperluan'], 0, 20).(strlen($row['keperluan']) > 20 ? '...' : '')."</td>
                    <td>";
                  
                  if (!empty($row['foto'])) {
                    echo "<img src='../user/{$row['foto']}' width='40' class='rounded-circle'>";
                  } else {
                    echo "<span class='badge bg-light text-dark'>No Photo</span>";
                  }
                  
                  echo "</td>
                    <td>
                      <button class='btn btn-sm btn-edit btn-action' data-id='{$row['id']}' data-bs-toggle='modal' data-bs-target='#editModal'>
                        <i class='bi bi-pencil-square'></i>
                      </button>
                      <button class='btn btn-sm btn-delete btn-action' data-id='{$row['id']}' data-bs-toggle='modal' data-bs-target='#deleteModal'>
                        <i class='bi bi-trash'></i>
                      </button>
                    </td>
                  </tr>";
                  $no++;
                }
                ?>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <nav class="mt-4">
            <ul class="pagination justify-content-center">
              <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1" aria-disabled="true">Previous</a>
              </li>
              <?php
              // Show page numbers
              $start_page = max(1, $page - 2);
              $end_page = min($total_pages, $page + 2);
              
              if($start_page > 1) {
                  echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                  if($start_page > 2) {
                      echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                  }
              }
              
              for($i = $start_page; $i <= $end_page; $i++) {
                  echo '<li class="page-item '.($i == $page ? 'active' : '').'"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
              }
              
              if($end_page < $total_pages) {
                  if($end_page < $total_pages - 1) {
                      echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                  }
                  echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
              }
              ?>
              <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal Tambah Data -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tambahModalLabel">Tambah Data Tamu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="tambah" value="1">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
              <select name="jenis_kelamin" class="form-select" required>
                <option value="">-- Pilih --</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
              <input type="text" name="no_telp" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Foto Profil</label>
              <input type="file" name="foto" class="form-control" accept="image/*">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Alamat/Asal <span class="text-danger">*</span></label>
            <input type="text" name="alamat_asal" class="form-control" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Keperluan <span class="text-danger">*</span></label>
            <textarea name="keperluan" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Data</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit Data -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Data Tamu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="edit" value="1">
        <input type="hidden" name="id" id="editId">
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama" id="editNama" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
              <select name="jenis_kelamin" id="editJenisKelamin" class="form-select" required>
                <option value="">-- Pilih --</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Waktu Kedatangan <span class="text-danger">*</span></label>
              <input type="datetime-local" name="jam_datang" id="editJamDatang" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
              <input type="text" name="no_telp" id="editNoTelp" class="form-control" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Alamat/Asal <span class="text-danger">*</span></label>
            <input type="text" name="alamat_asal" id="editAlamat" class="form-control" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Keperluan <span class="text-danger">*</span></label>
            <textarea name="keperluan" id="editKeperluan" class="form-control" rows="3" required></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Foto Profil</label>
            <input type="file" name="foto" class="form-control" accept="image/*">
            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto</small>
            <div id="currentFoto" class="mt-3 text-center"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Penghapusan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="hapus" value="1">
        <input type="hidden" name="id" id="deleteId">
        <div class="modal-body">
          <div class="text-center mb-4">
            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
            <h5 class="mt-3">Anda yakin ingin menghapus data ini?</h5>
            <p class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Ya, Hapus Data</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
  // Format datetime-local untuk edit
  function formatDateTimeLocal(dateTimeStr) {
    if (!dateTimeStr) return '';
    const dt = new Date(dateTimeStr);
    const year = dt.getFullYear();
    const month = String(dt.getMonth() + 1).padStart(2, '0');
    const day = String(dt.getDate()).padStart(2, '0');
    const hours = String(dt.getHours()).padStart(2, '0');
    const minutes = String(dt.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }

  // Edit Data - Tampilkan data di modal
  $(document).on('click', '.btn-edit', function() {
    const id = $(this).data('id');
    
    $.ajax({
      url: 'get_tamu.php',
      type: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function(data) {
        $('#editId').val(data.id);
        $('#editNama').val(data.nama);
        $('#editJamDatang').val(formatDateTimeLocal(data.jam_datang));
        $('#editAlamat').val(data.alamat_asal);
        $('#editNoTelp').val(data.no_telp);
        $('#editJenisKelamin').val(data.jenis_kelamin);
        $('#editKeperluan').val(data.keperluan);
        
        // Tampilkan foto saat ini jika ada
        if (data.foto) {
          $('#currentFoto').html(`
            <div class="text-center">
              <img src="../user/${data.foto}" class="profile-img mb-2">
              <p class="text-muted small">Foto saat ini</p>
            </div>
          `);
        } else {
          $('#currentFoto').html(`
            <div class="text-center">
              <div class="profile-img mb-2 bg-light d-flex align-items-center justify-content-center mx-auto">
                <i class="bi bi-person text-muted" style="font-size: 2rem;"></i>
              </div>
              <p class="text-muted small">Tidak ada foto</p>
            </div>
          `);
        }
      },
      error: function() {
        alert('Gagal mengambil data tamu');
      }
    });
  });

  // Delete Data - Tampilkan konfirmasi
  $(document).on('click', '.btn-delete', function() {
    const id = $(this).data('id');
    $('#deleteId').val(id);
  });

  // Fungsi pencarian
  $('#searchInput').on('keyup', function() {
    const value = $(this).val().toLowerCase();
    $('#tamuTable tbody tr').filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

  // Fungsi export ke Excel
  $('#btnExport').click(function() {
    // Buat array untuk data
    const data = [];
    
    // Tambahkan header
    data.push(['No', 'Nama', 'Waktu', 'Kontak', 'Jenis Kelamin', 'Keperluan']);
    
    // Ambil data dari tabel
    $('#tamuTable tbody tr').each(function() {
      const row = [];
      $(this).find('td').each(function(index) {
        // Skip kolom foto dan aksi
        if(index !== 6 && index !== 7) {
          row.push($(this).text().trim());
        }
      });
      data.push(row);
    });
    
    // Buat workbook
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);
    XLSX.utils.book_append_sheet(wb, ws, "Data Tamu");
    
    // Export ke file
    XLSX.writeFile(wb, 'data_tamu.xlsx');
  });

  // Fungsi print
  $('#btnPrint').click(function() {
    window.print();
  });
  
  // Animasi untuk tabel
  $('tbody tr').each(function(i) {
    $(this).delay(i * 100).animate({ opacity: 1 }, 200);
  });
});
</script>
</body>
</html>