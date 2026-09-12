<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Barbearia System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- CSS próprio -->
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Navbar superior -->
<nav class="navbar navbar-dark topbar px-3">
    <button class="btn btn-link text-white d-lg-none" type="button" id="btnToggleSidebar">
        <i class="bi bi-list fs-3"></i>
    </button>

    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>dashboard.php">
        <i class="bi bi-scissors"></i> Barbearia System
    </a>

    <div class="ms-auto dropdown">
        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
        </ul>
    </div>
</nav>

<div class="app-wrapper">
