<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pegawai_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pegawai_id) {
    header('Location: index.php');
    exit();
}

$page_title = 'Edit Progress';
$show_logout = true;
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/script.js';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
        try {
            $db->getConnection()->autocommit(FALSE);
            
            foreach ($_POST['status'] as $tahapan_id => $status_data) {
                $tahapan_id = (int)$tahapan_id;
                $status = $status_data['status'];
                $alasan_gagal = isset($status_data['alasan']) ? trim($status_data['alasan']) : '';
                
                // Validate status
                if (!in_array($status, ['pending', 'berhasil', 'gagal'])) {
                    continue;
                }
                
                // If status is gagal, alasan_gagal is required
                if ($status === 'gagal' && empty($alasan_gagal)) {
                    throw new Exception('Alasan gagal harus diisi untuk tahap yang gagal');
                }
                
                // Update or insert progress
                $stmt = $db->prepare("
                    INSERT INTO progress_pegawai (pegawai_id, tahapan_id, status, alasan_gagal) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    status = VALUES(status), 
                    alasan_gagal = VALUES(alasan_gagal),
                    tanggal_update = CURRENT_TIMESTAMP
                ");
                
                $stmt->bind_param("iiss", $pegawai_id, $tahapan_id, $status, $alasan_gagal);
                $stmt->execute();
            }
            
            $db->getConnection()->commit();
            setFlashMessage('success', 'Progress berhasil diupdate');
            header('Location: edit.php?id=' . $pegawai_id);
            exit();
            
        } catch (Exception $e) {
            $db->getConnection()->rollback();
            setFlashMessage('error', 'Gagal mengupdate progress: ' . $e->getMessage());
        }
    } else {
        setFlashMessage('error', 'Token CSRF tidak valid');
    }
}

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
    
    // Get anggota keluarga data
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

// Get tahapan progress
try {
    $tahapan_result = $db->query("SELECT * FROM tahapan_progress ORDER BY urutan");
    $tahapan_list = [];
    while ($row = $tahapan_result->fetch_assoc()) {
        $tahapan_list[] = $row;
    }
} catch (Exception $e) {
    $tahapan_list = [];
}

// Get current progress
try {
    $stmt = $db->prepare("SELECT * FROM progress_pegawai WHERE pegawai_id = ?");
    $stmt->bind_param("i", $pegawai_id);
    $stmt->execute();
    $progress_result = $stmt->get_result();
    $current_progress = [];
    while ($row = $progress_result->fetch_assoc()) {
        $current_progress[$row['tahapan_id']] = $row;
    }
} catch (Exception $e) {
    $current_progress = [];
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>
                Edit Progress Pegawai
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
</div>

<!-- Pegawai Information -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-user me-2"></i>
            Informasi Pegawai
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama Pegawai:</strong> <?php echo htmlspecialchars($pegawai_data['nama']); ?></p>
                <p><strong>NIK:</strong> <code><?php echo formatNIK($pegawai_data['nik']); ?></code></p>
            </div>
            <div class="col-md-6">
                <?php if (!empty($anggota_keluarga)): ?>
                <p><strong>Anggota Keluarga:</strong></p>
                <ul class="list-unstyled">
                    <?php foreach ($anggota_keluarga as $anggota): ?>
                    <li>
                        <?php echo htmlspecialchars($anggota['nama']); ?> 
                        (<code><?php echo formatNIK($anggota['nik']); ?></code>)
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Progress Form -->
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-tasks me-2"></i>
                Progress Tahapan
            </h5>
        </div>
        <div class="card-body">
            <?php foreach ($tahapan_list as $tahapan): ?>
            <?php 
            $current_status = isset($current_progress[$tahapan['id']]) ? $current_progress[$tahapan['id']]['status'] : 'pending';
            $current_alasan = isset($current_progress[$tahapan['id']]) ? $current_progress[$tahapan['id']]['alasan_gagal'] : '';
            ?>
            <div class="progress-step-form mb-4 p-3 border rounded">
                <h6 class="fw-bold mb-3">
                    <?php echo $tahapan['urutan']; ?>. <?php echo htmlspecialchars($tahapan['nama_tahapan']); ?>
                </h6>
                
                <div class="checkbox-group">
                    <div class="form-check">
                        <input class="form-check-input status-checkbox" type="radio" 
                               name="status[<?php echo $tahapan['id']; ?>][status]" 
                               value="berhasil" id="berhasil_<?php echo $tahapan['id']; ?>"
                               data-tahapan="<?php echo $tahapan['id']; ?>"
                               <?php echo $current_status === 'berhasil' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="berhasil_<?php echo $tahapan['id']; ?>">
                            <i class="fas fa-check text-success me-1"></i>Berhasil
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input status-checkbox" type="radio" 
                               name="status[<?php echo $tahapan['id']; ?>][status]" 
                               value="gagal" id="gagal_<?php echo $tahapan['id']; ?>"
                               data-tahapan="<?php echo $tahapan['id']; ?>"
                               <?php echo $current_status === 'gagal' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="gagal_<?php echo $tahapan['id']; ?>">
                            <i class="fas fa-times text-danger me-1"></i>Gagal
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input status-checkbox" type="radio" 
                               name="status[<?php echo $tahapan['id']; ?>][status]" 
                               value="pending" id="pending_<?php echo $tahapan['id']; ?>"
                               data-tahapan="<?php echo $tahapan['id']; ?>"
                               <?php echo $current_status === 'pending' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="pending_<?php echo $tahapan['id']; ?>">
                            <i class="fas fa-clock text-warning me-1"></i>Pending
                        </label>
                    </div>
                </div>
                
                <div class="reason-input mt-3" id="reason-<?php echo $tahapan['id']; ?>" 
                     style="<?php echo $current_status === 'gagal' ? '' : 'display: none;'; ?>">
                    <label for="alasan_<?php echo $tahapan['id']; ?>" class="form-label">
                        <i class="fas fa-comment me-1"></i>Alasan Gagal
                    </label>
                    <input type="text" class="form-control" 
                           name="status[<?php echo $tahapan['id']; ?>][alasan]" 
                           id="alasan_<?php echo $tahapan['id']; ?>"
                           placeholder="Masukkan alasan kegagalan..."
                           value="<?php echo htmlspecialchars($current_alasan); ?>">
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-save me-2"></i>Simpan Progress
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="location.reload()">
                    <i class="fas fa-undo me-2"></i>Ulangi Proses
                </button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Handle status checkbox changes
    $('.status-checkbox').change(function() {
        var tahapanId = $(this).data('tahapan');
        var status = $(this).val();
        var reasonContainer = $('#reason-' + tahapanId);
        var reasonInput = reasonContainer.find('input');
        
        if (status === 'gagal') {
            reasonContainer.show();
            reasonInput.prop('required', true);
        } else {
            reasonContainer.hide();
            reasonInput.prop('required', false).val('');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>

