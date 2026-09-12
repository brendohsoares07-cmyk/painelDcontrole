<?php
require_once __DIR__ . '/../includes/auth.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: listar.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT nome FROM barbeiros WHERE id = ?");
    $stmt->execute([$id]);
    $barbeiro = $stmt->fetch();

    if (!$barbeiro) {
        header('Location: listar.php?erro=' . urlencode('Barbeiro não encontrado.'));
        exit;
    }

    $stmtDel = $pdo->prepare("DELETE FROM barbeiros WHERE id = ?");
    $stmtDel->execute([$id]);

    header('Location: listar.php?sucesso=' . urlencode("Barbeiro \"{$barbeiro['nome']}\" excluído com sucesso!"));
    exit;
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        header('Location: listar.php?erro=' . urlencode(
            'Não é possível excluir este barbeiro pois existem agendamentos vinculados a ele. Considere marcá-lo como "Inativo" em vez de excluí-lo.'
        ));
    } else {
        header('Location: listar.php?erro=' . urlencode('Erro ao excluir barbeiro: ' . $e->getMessage()));
    }
    exit;
}
