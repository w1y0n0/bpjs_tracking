<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$pegawai_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pegawai_id) {
    header('Location: index.php');
    exit();
}

$page_title = 'Lihat Progress';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/script.js';

// Get pegawai data
try {
    $stmt = $db->prepare("
        SELECT p.*, ak.nama as nama_anggota, ak.nik as nik_anggota
        FROM pegawai p
        LEFT JOIN anggota_keluarga ak ON p.id = ak.pegawai_id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $pegawai_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header('Location: index.php');
        exit();
    }
    
    $pegawai_data = $result->fetch_assoc();
    
    // Get all anggota keluarga
    $stmt = $db->prepare("SELECT * FROM anggota_keluarga WHERE pegawai_id = ?");
    $stmt->bind_param("i", $pegawai_id);
    $stmt->execute();
    $anggota_result = $stmt->get_result();
    $anggota_keluarga = [];
    while ($row = $anggota_result->fetch_assoc()) {
        $anggota_keluarga[] = $row;
    }
    
} catch (Exception $e) {
    setFlashMessage('error', 'Terjadi kesalahan saat mengambil data pegawai');
    header('Location: index.php');
    exit();
}

// Get tahapan progress with current status
try {
    $query = "
        SELECT 
            tp.*,
            pp.status,
            pp.alasan_gagal,
            pp.tanggal_update
        FROM tahapan_progress tp
        LEFT JOIN progress_pegawai pp ON tp.id = pp.tahapan_id AND pp.pegawai_id = ?
        ORDER BY tp.urutan
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $pegawai_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $progress_data = [];
    while ($row = $result->fetch_assoc()) {
        $progress_data[] = $row;
    }
    
} catch (Exception $e) {
    $progress_data = [];
    setFlashMessage('error', 'Terjadi kesalahan saat mengambil data progress');
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Progress BPJS
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Pencarian
            </a>
        </div>
    </div>
</div>

<!-- Pegawai Information -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-1">Nama Pegawai</h6>
                <p class="h5 mb-3"><?php echo htmlspecialchars($pegawai_data['nama']); ?></p>
                
                <h6 class="text-muted mb-1">NIK</h6>
                <p class="mb-3"><code class="fs-6"><?php echo formatNIK($pegawai_data['nik']); ?></code></p>
            </div>
            <div class="col-md-6">
                <?php if (!empty($anggota_keluarga)): ?>
                <h6 class="text-muted mb-1">Nama Anggota Keluarga</h6>
                <?php foreach ($anggota_keluarga as $anggota): ?>
                <p class="mb-1"><?php echo htmlspecialchars($anggota['nama']); ?></p>
                <?php endforeach; ?>
                
                <h6 class="text-muted mb-1 mt-3">NIK Anggota</h6>
                <?php foreach ($anggota_keluarga as $anggota): ?>
                <p class="mb-1"><code><?php echo formatNIK($anggota['nik']); ?></code></p>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Progress Timeline -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-tasks me-2"></i>
            Timeline Progress
        </h5>
    </div>
    <div class="card-body">
        <div class="progress-timeline">
            <?php foreach ($progress_data as $index => $step): ?>
            <?php 
            $status = $step['status'] ?: 'pending';
            $step_class = '';
            $icon = '';
            
            switch ($status) {
                case 'berhasil':
                    $step_class = 'completed';
                    $icon = '<i class="fas fa-check"></i>';
                    break;
                case 'gagal':
                    $step_class = 'failed';
                    $icon = '<i class="fas fa-times"></i>';
                    break;
                case 'pending':
                default:
                    // Check if this is the current step (first pending after completed steps)
                    $is_current = false;
                    if ($index == 0) {
                        $is_current = true;
                    } else {
                        $prev_status = $progress_data[$index - 1]['status'] ?: 'pending';
                        if ($prev_status === 'berhasil') {
                            $is_current = true;
                        }
                    }
                    
                    $step_class = $is_current ? 'current' : 'pending';
                    $icon = $is_current ? '<i class="fas fa-clock"></i>' : '<i class="fas fa-circle"></i>';
                    break;
            }
            ?>
            
            <div class="progress-step">
                <div class="step-circle <?php echo $step_class; ?>">
                    <?php echo $icon; ?>
                </div>
                <div class="step-label">
                    <?php echo htmlspecialchars($step['nama_tahapan']); ?>
                </div>
                <?php if ($index < count($progress_data) - 1): ?>
                <div class="step-line <?php echo $status === 'berhasil' ? 'completed' : ''; ?>"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Progress Details -->
        <div class="mt-4">
            <h6 class="mb-3">Detail Status:</h6>
            <div class="row">
                <?php foreach ($progress_data as $step): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2">
                                <?php echo htmlspecialchars($step['nama_tahapan']); ?>
                            </h6>
                            <p class="card-text mb-2">
                                <?php 
                                $status = $step['status'] ?: 'pending';
                                $badge_class = getStatusBadgeClass($status);
                                $status_text = getStatusText($status);
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </p>
                            
                            <?php if ($step['alasan_gagal']): ?>
                            <div class="alert alert-danger alert-sm p-2 mb-0">
                                <small>
                                    <strong>Alasan:</strong> 
                                    <?php echo htmlspecialchars($step['alasan_gagal']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($step['tanggal_update']): ?>
                            <small class="text-muted">
                                Update: <?php echo date('d/m/Y H:i', strtotime($step['tanggal_update'])); ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Progress Summary -->
        <div class="mt-4 p-3 bg-light rounded">
            <?php 
            $completed_count = 0;
            $failed_count = 0;
            $total_count = count($progress_data);
            
            foreach ($progress_data as $step) {
                if ($step['status'] === 'berhasil') {
                    $completed_count++;
                } elseif ($step['status'] === 'gagal') {
                    $failed_count++;
                }
            }
            
            $progress_percentage = ($completed_count / $total_count) * 100;
            ?>
            
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-2">Ringkasan Progress</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $progress_percentage; ?>%">
                            <?php echo $completed_count; ?>/<?php echo $total_count; ?> Selesai
                        </div>
                    </div>
                    <small class="text-muted">
                        <?php echo round($progress_percentage); ?>% dari total tahapan telah diselesaikan
                        <?php if ($failed_count > 0): ?>
                        <br><span class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo $failed_count; ?> tahap mengalami kegagalan
                        </span>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if ($completed_count === $total_count): ?>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Selesai!</strong><br>
                        BPJS berhasil diaktifkan
                    </div>
                    <?php elseif ($failed_count > 0): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perlu Tindakan</strong><br>
                        Ada tahap yang gagal
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Dalam Proses</strong><br>
                        Sedang diproses
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

