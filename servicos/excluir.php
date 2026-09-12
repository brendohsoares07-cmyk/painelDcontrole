<?php
require_once __DIR__ . '/../includes/auth.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: listar.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT nome FROM servicos WHERE id = ?");
    $stmt->execute([$id]);
    $servico = $stmt->fetch();

    if (!$servico) {
        header('Location: listar.php?erro=' . urlencode('Serviço não encontrado.'));
        exit;
    }

    $stmtDel = $pdo->prepare("DELETE FROM servicos WHERE id = ?");
    $stmtDel->execute([$id]);

    header('Location: listar.php?sucesso=' . urlencode("Serviço \"{$servico['nome']}\" excluído com sucesso!"));
    exit;
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        header('Location: listar.php?erro=' . urlencode(
            'Não é possível excluir este serviço pois existem agendamentos vinculados a ele.'
        ));
    } else {
        header('Location: listar.php?erro=' . urlencode('Erro ao excluir serviço: ' . $e->getMessage()));
    }
    exit;
}
