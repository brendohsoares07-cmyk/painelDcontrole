<?php
require_once __DIR__ . '/../includes/auth.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: listar.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT nome FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        header('Location: listar.php?erro=' . urlencode('Cliente não encontrado.'));
        exit;
    }

    $stmtDel = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
    $stmtDel->execute([$id]);

    header('Location: listar.php?sucesso=' . urlencode("Cliente \"{$cliente['nome']}\" excluído com sucesso!"));
    exit;
} catch (PDOException $e) {
    // Código 23000 = violação de integridade referencial (FK)
    if ($e->getCode() == 23000) {
        header('Location: listar.php?erro=' . urlencode(
            'Não é possível excluir este cliente pois existem agendamentos vinculados a ele. Cancele ou remova os agendamentos primeiro.'
        ));
    } else {
        header('Location: listar.php?erro=' . urlencode('Erro ao excluir cliente: ' . $e->getMessage()));
    }
    exit;
}
