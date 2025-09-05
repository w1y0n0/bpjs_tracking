<?php
require_once 'includes/config.php';

$page_title = 'BPJS Tracking';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <i class="fas fa-users-cog fa-5x text-primary mb-4"></i>
                        <h1 class="display-4 text-white mb-3"><?php echo APP_NAME; ?></h1>
                        <p class="lead text-white-50">
                            Sistem manajemen progress BPJS untuk pegawai dan administrator
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                                    <h4 class="card-title">Portal Admin</h4>
                                    <p class="card-text text-muted">
                                        Kelola data pegawai dan update progress BPJS
                                    </p>
                                    <a href="admin/login.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Login Admin
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-users fa-3x text-success mb-3"></i>
                                    <h4 class="card-title">Portal Pegawai</h4>
                                    <p class="card-text text-muted">
                                        Cek progress BPJS Anda dengan NIK
                                    </p>
                                    <a href="pegawai/index.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-search me-2"></i>
                                        Cek Progress
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-white-50">
                            <i class="fas fa-info-circle me-1"></i>
                            Demo Login Admin - Username: <strong>admin</strong>, Password: <strong>admin123</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>