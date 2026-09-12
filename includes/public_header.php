<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Barbearia System - cortes, barba, agendamento online e atendimento de qualidade.">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Barbearia System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="public-body">
<nav class="navbar navbar-expand-lg public-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-gold" href="<?= BASE_URL ?>index.php">
            <i class="bi bi-scissors"></i> Barbearia System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-label="Abrir menu">
            <i class="bi bi-list text-white fs-3"></i>
        </button>
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>index.php#inicio">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>galeria.php">Galeria</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>index.php#servicos">Serviços</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>avaliacoes.php">Avaliações</a></li>
                <li class="nav-item"><a class="btn btn-gold ms-lg-2" href="<?= BASE_URL ?>agendar.php"><i class="bi bi-calendar-check"></i> Agendar</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>login/login.php"><i class="bi bi-person"></i> Área administrativa</a></li>
            </ul>
        </div>
    </div>
</nav>
