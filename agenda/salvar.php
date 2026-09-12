<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$cliente_id  = $_POST['cliente_id'] ?? '';
$barbeiro_id = $_POST['barbeiro_id'] ?? '';
$servico_id  = $_POST['servico_id'] ?? '';
$data        = $_POST['data_agendamento'] ?? '';
$hora        = $_POST['hora_agendamento'] ?? '';

if ($cliente_id === '' || $barbeiro_id === '' || $servico_id === '' || $data === '' || $hora === '') {
    header('Location: form.php?erro=' . urlencode('Preencha todos os campos obrigatórios.'));
    exit;
}

try {
    $stmtPreco = $pdo->prepare("SELECT preco FROM servicos WHERE id = ?");
    $stmtPreco->execute([$servico_id]);
    $preco = $stmtPreco->fetchColumn();

    if ($preco === false) {
        header('Location: form.php?erro=' . urlencode('Serviço selecionado não foi encontrado.'));
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, valor_servico, data_agendamento, hora_agendamento, status)
        VALUES (?, ?, ?, ?, ?, ?, 'agendado')
    ");
    $stmt->execute([$cliente_id, $barbeiro_id, $servico_id, $preco, $data, $hora]);

    header('Location: listar.php?data=' . urlencode($data) . '&sucesso=' . urlencode('Agendamento realizado com sucesso!'));
    exit;
} catch (PDOException $e) {
    // Código 23000 cobre FK inválida ou horário já ocupado (UNIQUE KEY)
    // Código 45000 (via SIGNAL) cobre a regra da trigger: barbeiro inativo
    if ($e->getCode() == 23000) {
        header('Location: form.php?erro=' . urlencode('Este horário acabou de ser ocupado por outro agendamento. Escolha outro horário.'));
    } else {
        header('Location: form.php?erro=' . urlencode('Erro ao criar agendamento: ' . $e->getMessage()));
    }
    exit;
}
