<?php
require_once __DIR__ . '/../includes/auth.php'; 

$id = $_GET['id'] ?? '';
$data = $_GET['data'] ?? date('Y-m-d');

if ($id === '') {
    header('Location: listar.php?data=' . urlencode($data));
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?data=' . urlencode($data) . '&sucesso=' . urlencode('Agendamento excluído com sucesso!'));
    exit;
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        header('Location: listar.php?data=' . urlencode($data) . '&erro=' . urlencode(
            'Não é possível excluir este agendamento pois já existe um pagamento registrado para ele.'
        ));
    } else {
        header('Location: listar.php?data=' . urlencode($data) . '&erro=' . urlencode('Erro ao excluir agendamento: ' . $e->getMessage()));
    }
    exit;
}
