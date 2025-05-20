<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nafahat POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            background: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            color: #f8f9fa;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Top Navbar with toggle button -->
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <button class="navbar-toggler me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <span class="navbar-toggler-icon"></span>
            </button>
            <span class="navbar-brand mb-0 h1">Nafahat POS</span>
            <span class="text-white d-none d-md-inline">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </nav>
    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Nafahat POS</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="/nafahat pos/admin/dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/nafahat pos/admin/products.php">
                        <i class="bi bi-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/nafahat pos/admin/categories.php">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/nafahat pos/admin/expenses.php">
                        <i class="bi bi-cash-stack"></i> Expenses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/nafahat pos/admin/reports.php">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/nafahat pos/admin/users.php">
                        <i class="bi bi-people"></i> Users
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/nafahat pos/pos.php">
                        <i class="bi bi-cart"></i> POS
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/nafahat pos/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <main class="col-12 px-md-4 content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0 d-md-none">
                        <div class="btn-group me-2">
                            <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 