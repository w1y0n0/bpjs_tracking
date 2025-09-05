<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo isset($css_path) ? $css_path : '../assets/css/style.css'; ?>" rel="stylesheet">
    
    <style>
        .progress-step {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            color: white;
        }
        
        .step-circle.completed {
            background-color: #28a745;
        }
        
        .step-circle.current {
            background-color: #007bff;
        }
        
        .step-circle.pending {
            background-color: #6c757d;
        }
        
        .step-circle.failed {
            background-color: #dc3545;
        }
        
        .step-line {
            flex: 1;
            height: 2px;
            background-color: #dee2e6;
            margin: 0 15px;
        }
        
        .step-line.completed {
            background-color: #28a745;
        }
        
        .badge-success {
            background-color: #28a745;
        }
        
        .badge-danger {
            background-color: #dc3545;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-primary {
            background-color: #2c5f7c;
            border-color: #2c5f7c;
        }
        
        .btn-primary:hover {
            background-color: #1e4a63;
            border-color: #1e4a63;
        }
        
        .table th {
            background-color: #2c5f7c;
            color: white;
        }
        
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-users-cog me-2"></i>
                <?php echo APP_NAME; ?>
            </a>
            
            <?php if (isset($show_logout) && $show_logout): ?>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container mt-4">
        <?php
        // Display flash messages
        $flash = getFlashMessage();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

