<?php
// Identifica a página atual para destacar o item ativo no menu
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
$pastaAtual = basename(dirname($_SERVER['SCRIPT_NAME']));

function menuAtivo($pasta, $pastaAlvo) {
    return $pasta === $pastaAlvo ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= $paginaAtual === 'dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= menuAtivo($pastaAtual, 'clientes') ?>" href="<?= BASE_URL ?>clientes/listar.php">
                <i class="bi bi-people"></i> Clientes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= menuAtivo($pastaAtual, 'barbeiros') ?>" href="<?= BASE_URL ?>barbeiros/listar.php">
                <i class="bi bi-person-badge"></i> Barbeiros
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= menuAtivo($pastaAtual, 'servicos') ?>" href="<?= BASE_URL ?>servicos/listar.php">
                <i class="bi bi-scissors"></i> Serviços
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= menuAtivo($pastaAtual, 'agenda') ?>" href="<?= BASE_URL ?>agenda/listar.php">
                <i class="bi bi-calendar-week"></i> Agenda
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= menuAtivo($pastaAtual, 'relatorios') ?>" href="<?= BASE_URL ?>relatorios/financeiro.php">
                <i class="bi bi-graph-up-arrow"></i> Relatório Financeiro
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>index.php" target="_blank" rel="noopener">
                <i class="bi bi-globe2"></i> Site público
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>avaliacoes.php" target="_blank" rel="noopener">
                <i class="bi bi-star"></i> Avaliações
            </a>
        </li>
    </ul>
</aside>

<main class="main-content">
