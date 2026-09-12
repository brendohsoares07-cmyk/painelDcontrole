<?php
require_once __DIR__ . '/../includes/auth.php';

$id = $_GET['id'] ?? '';
$novoStatus = $_GET['status'] ?? '';
$data = $_GET['data'] ?? date('Y-m-d');

if (!in_array($novoStatus, ['concluido', 'cancelado', 'agendado'], true)) {
    header('Location: listar.php?data=' . urlencode($data) . '&erro=' . urlencode('Status inválido.'));
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
    $stmt->execute([$novoStatus, $id]);

    $mensagens = [
        'concluido' => 'Agendamento marcado como concluído!',
        'cancelado' => 'Agendamento cancelado!',
        'agendado'  => 'Agendamento reaberto!',
    ];

    header('Location: listar.php?data=' . urlencode($data) . '&sucesso=' . urlencode($mensagens[$novoStatus]));
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?data=' . urlencode($data) . '&erro=' . urlencode('Erro ao atualizar status: ' . $e->getMessage()));
    exit;
}
