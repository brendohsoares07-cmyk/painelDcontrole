<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = $_POST['id'] ?? '';
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$preco = $_POST['preco'] ?? '';
$duracao = $_POST['duracao_minutos'] ?? '';

if ($nome === '' || $preco === '' || !is_numeric($preco) || $duracao === '' || !is_numeric($duracao)) {
    $destino = $id !== '' ? "form.php?id={$id}" : 'form.php';
    header('Location: ' . $destino . '&erro=' . urlencode('Preencha corretamente nome, preço e duração.'));
    exit;
}

try {
    if ($id !== '') {
        $stmt = $pdo->prepare("UPDATE servicos SET nome = ?, descricao = ?, preco = ?, duracao_minutos = ? WHERE id = ?");
        $stmt->execute([$nome, $descricao, $preco, $duracao, $id]);
        $mensagem = 'Serviço atualizado com sucesso!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO servicos (nome, descricao, preco, duracao_minutos) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $preco, $duracao]);
        $mensagem = 'Serviço cadastrado com sucesso!';
    }
    header('Location: listar.php?sucesso=' . urlencode($mensagem));
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?erro=' . urlencode('Erro ao salvar serviço: ' . $e->getMessage()));
    exit;
}
