<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireAdminLogin();

$page_title = 'Dashboard Admin';
$show_logout = true;
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/script.js';

// Get filter parameter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query based on filter
$where_clause = '';
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $where_clause = ' WHERE pp.status = ?';
    $params[] = $status_filter;
    $types = 's';
}

// Get pegawai data with progress information
$query = "
    SELECT DISTINCT
        p.id as pegawai_id,
        p.nama as nama_pegawai,
        p.nik as nik_pegawai,
        ak.nama as nama_anggota,
        ak.nik as nik_anggota,
        (SELECT COUNT(*) FROM progress_pegawai pp2 WHERE pp2.pegawai_id = p.id AND pp2.status = 'berhasil') as completed_steps,
        (SELECT COUNT(*) FROM progress_pegawai pp3 WHERE pp3.pegawai_id = p.id AND pp3.status = 'gagal') as failed_steps,
        (SELECT COUNT(*) FROM tahapan_progress) as total_steps
    FROM pegawai p
    LEFT JOIN anggota_keluarga ak ON p.id = ak.pegawai_id
    LEFT JOIN progress_pegawai pp ON p.id = pp.pegawai_id
    $where_clause
    ORDER BY p.nama, ak.nama
";

try {
    if (!empty($params)) {
        $stmt = $db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $db->query($query);
    }
    
    $pegawai_data = [];
    while ($row = $result->fetch_assoc()) {
        $pegawai_data[] = $row;
    }
} catch (Exception $e) {
    $pegawai_data = [];
    setFlashMessage('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard Admin
            </h1>
            <div>
                <span class="text-muted">Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_nama']); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Filter Status -->
<div class="status-filters">
    <h5 class="mb-3">
        <i class="fas fa-filter me-2"></i>
        Filter Status
    </h5>
    <div class="filter-buttons">
        <a href="?status=all" class="btn <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
            <i class="fas fa-list me-1"></i>Semua
        </a>
        <a href="?status=pending" class="btn <?php echo $status_filter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
            <i class="fas fa-clock me-1"></i>Diproses
        </a>
        <a href="?status=berhasil" class="btn <?php echo $status_filter === 'berhasil' ? 'btn-success' : 'btn-outline-success'; ?>">
            <i class="fas fa-check me-1"></i>Selesai
        </a>
        <a href="?status=gagal" class="btn <?php echo $status_filter === 'gagal' ? 'btn-danger' : 'btn-outline-danger'; ?>">
            <i class="fas fa-times me-1"></i>Gagal
        </a>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>
            Data Pegawai dan Anggota Keluarga
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($pegawai_data)): ?>
        <div class="text-center py-4">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">Tidak ada data yang ditemukan</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nama Pegawai</th>
                        <th>NIK Pegawai</th>
                        <th>Nama Anggota Keluarga</th>
                        <th>NIK Anggota</th>
                        <th>Progress</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pegawai_data as $data): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($data['nama_pegawai']); ?></strong>
                        </td>
                        <td>
                            <code><?php echo formatNIK($data['nik_pegawai']); ?></code>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($data['nama_anggota'] ?: '-'); ?>
                        </td>
                        <td>
                            <?php if ($data['nik_anggota']): ?>
                                <code><?php echo formatNIK($data['nik_anggota']); ?></code>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <?php 
                                $progress_percentage = ($data['completed_steps'] / $data['total_steps']) * 100;
                                $progress_class = 'bg-success';
                                if ($data['failed_steps'] > 0) {
                                    $progress_class = 'bg-danger';
                                } elseif ($progress_percentage < 100) {
                                    $progress_class = 'bg-warning';
                                }
                                ?>
                                <div class="progress-bar <?php echo $progress_class; ?>" 
                                     style="width: <?php echo $progress_percentage; ?>%">
                                    <?php echo $data['completed_steps']; ?>/<?php echo $data['total_steps']; ?>
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php if ($data['failed_steps'] > 0): ?>
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                    <?php echo $data['failed_steps']; ?> tahap gagal
                                <?php else: ?>
                                    <?php echo round($progress_percentage); ?>% selesai
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $data['pegawai_id']; ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>Edit Progress
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

