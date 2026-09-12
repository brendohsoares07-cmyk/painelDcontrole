<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id     = $_POST['id'] ?? '';
$nome   = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email  = trim($_POST['email'] ?? '');
$data_nascimento = $_POST['data_nascimento'] !== '' ? $_POST['data_nascimento'] : null;

if ($nome === '') {
    $destino = $id !== '' ? "form.php?id={$id}" : 'form.php';
    header('Location: ' . $destino . '&erro=' . urlencode('O campo nome é obrigatório.'));
    exit;
}

try {
    if ($id !== '') {
        $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, telefone = ?, email = ?, data_nascimento = ? WHERE id = ?");
        $stmt->execute([$nome, $telefone, $email, $data_nascimento, $id]);
        $mensagem = 'Cliente atualizado com sucesso!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO clientes (nome, telefone, email, data_nascimento) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $telefone, $email, $data_nascimento]);
        $mensagem = 'Cliente cadastrado com sucesso!';
    }
    header('Location: listar.php?sucesso=' . urlencode($mensagem));
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?erro=' . urlencode('Erro ao salvar cliente: ' . $e->getMessage()));
    exit;
}
