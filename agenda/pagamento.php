<?php
/**
 * Registra o pagamento de um agendamento chamando a stored
 * procedure sp_registrar_pagamento. O trigger trg_pagamento_after_insert
 * (no banco de dados) marca automaticamente o agendamento como "concluído".
 */
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$agendamento_id = $_POST['agendamento_id'] ?? '';
$forma_pagamento = $_POST['forma_pagamento'] ?? '';
$data = $_POST['data'] ?? date('Y-m-d');

if ($agendamento_id === '' || !in_array($forma_pagamento, ['dinheiro', 'pix', 'cartao'], true)) {
    header('Location: listar.php?data=' . urlencode($data) . '&erro=' . urlencode('Selecione uma forma de pagamento válida.'));
    exit;
}

try {
    $stmt = $pdo->prepare('CALL sp_registrar_pagamento(?, ?)');
    $stmt->execute([$agendamento_id, $forma_pagamento]);

    header('Location: listar.php?data=' . urlencode($data) . '&sucesso=' . urlencode('Pagamento registrado e agendamento concluído!'));
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?data=' . urlencode($data) . '&erro=' . urlencode('Erro ao registrar pagamento: ' . $e->getMessage()));
    exit;
}
