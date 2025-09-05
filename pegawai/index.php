<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$page_title = 'Portal Pegawai';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/script.js';

// Handle NIK search
$search_results = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nik'])) {
    $nik = sanitizeInput($_POST['nik']);
    $nik = str_replace(' ', '', $nik); // Remove spaces
    
    if (strlen($nik) >= 16) {
        try {
            $stmt = $db->prepare("
                SELECT 
                    p.id as pegawai_id,
                    p.nama as nama_pegawai,
                    ak.nama as nama_anggota
                FROM pegawai p
                LEFT JOIN anggota_keluarga ak ON p.id = ak.pegawai_id
                WHERE p.nik = ? OR ak.nik = ?
                ORDER BY p.nama, ak.nama
            ");
            $stmt->bind_param("ss", $nik, $nik);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $search_results[] = $row;
            }
            
            $search_performed = true;
            
        } catch (Exception $e) {
            setFlashMessage('error', 'Terjadi kesalahan saat mencari data');
        }
    } else {
        setFlashMessage('error', 'NIK harus terdiri dari 16 digit');
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="text-center mb-5">
            <h1 class="display-4 text-primary mb-3">
                <i class="fas fa-search me-3"></i>
                Portal Pegawai
            </h1>
            <p class="lead text-muted">
                Cari progress BPJS Anda dengan memasukkan NIK
            </p>
        </div>
    </div>
</div>

<!-- Search Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="search-box">
            <form method="POST" action="">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="nik" class="form-label fw-bold">
                            <i class="fas fa-id-card me-2"></i>
                            NIK (Nomor Induk Kependudukan)
                        </label>
                        <input type="text" class="form-control form-control-lg" 
                               id="nik" name="nik" 
                               placeholder="Masukkan NIK 16 digit..."
                               value="<?php echo isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : ''; ?>"
                               maxlength="19" required>
                        <small class="form-text text-muted">
                            Format: 1234 5678 9012 3456
                        </small>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Search Results -->
<?php if ($search_performed): ?>
<div class="row justify-content-center mt-4">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Hasil Pencarian
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($search_results)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                    <h5>Data Tidak Ditemukan</h5>
                    <p class="text-muted">
                        NIK yang Anda masukkan tidak ditemukan dalam sistem.<br>
                        Pastikan NIK yang dimasukkan benar dan sudah terdaftar.
                    </p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nama Pegawai</th>
                                <th>Nama Anggota Keluarga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $processed_pegawai = [];
                            foreach ($search_results as $result): 
                                $pegawai_key = $result['pegawai_id'] . '_' . $result['nama_pegawai'];
                                if (!in_array($pegawai_key, $processed_pegawai)):
                                    $processed_pegawai[] = $pegawai_key;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($result['nama_pegawai']); ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    // Get all anggota keluarga for this pegawai
                                    $anggota_list = [];
                                    foreach ($search_results as $sr) {
                                        if ($sr['pegawai_id'] == $result['pegawai_id'] && $sr['nama_anggota']) {
                                            $anggota_list[] = $sr['nama_anggota'];
                                        }
                                    }
                                    
                                    if (!empty($anggota_list)) {
                                        echo htmlspecialchars(implode(', ', array_unique($anggota_list)));
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="lihat_progress.php?id=<?php echo $result['pegawai_id']; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-eye me-1"></i>Lihat Progress
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Information Section -->
<div class="row justify-content-center mt-5">
    <div class="col-lg-10">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Informasi Penting
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Cara Menggunakan:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Masukkan NIK 16 digit Anda</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Klik tombol "Cari"</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Pilih "Lihat Progress" untuk melihat status</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-clock text-warning me-2"></i>Tahapan Progress:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-circle text-success me-2"></i>Berkas Masuk</li>
                            <li><i class="fas fa-circle text-success me-2"></i>Verifikasi Berkas</li>
                            <li><i class="fas fa-circle text-warning me-2"></i>Persetujuan</li>
                            <li><i class="fas fa-circle text-secondary me-2"></i>Bagian Keuangan</li>
                            <li><i class="fas fa-circle text-secondary me-2"></i>DPP untuk BPJS</li>
                            <li><i class="fas fa-circle text-secondary me-2"></i>BPJS berhasil diaktifkan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Format NIK input
    $('#nik').on('input', function() {
        var value = $(this).val().replace(/\s/g, '');
        var formatted = value.replace(/(.{4})/g, '$1 ').trim();
        $(this).val(formatted);
    });
    
    // Auto search when NIK is complete
    $('#nik').on('input', function() {
        var nik = $(this).val().replace(/\s/g, '');
        if (nik.length === 16) {
            // Optional: Auto-submit form when NIK is complete
            // $(this).closest('form').submit();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>

