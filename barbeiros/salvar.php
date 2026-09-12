<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = $_POST['id'] ?? '';
$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$especialidade = trim($_POST['especialidade'] ?? '');
$status = $_POST['status'] === 'inativo' ? 'inativo' : 'ativo';

if ($nome === '') {
    $destino = $id !== '' ? "form.php?id={$id}" : 'form.php';
    header('Location: ' . $destino . '&erro=' . urlencode('O campo nome é obrigatório.'));
    exit;
}

try {
    if ($id !== '') {
        $stmt = $pdo->prepare("UPDATE barbeiros SET nome = ?, telefone = ?, email = ?, especialidade = ?, status = ? WHERE id = ?");
        $stmt->execute([$nome, $telefone, $email, $especialidade, $status, $id]);
        $mensagem = 'Barbeiro atualizado com sucesso!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO barbeiros (nome, telefone, email, especialidade, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $telefone, $email, $especialidade, $status]);
        $mensagem = 'Barbeiro cadastrado com sucesso!';
    }
    header('Location: listar.php?sucesso=' . urlencode($mensagem));
    exit;
} catch (PDOException $e) {
    header('Location: listar.php?erro=' . urlencode('Erro ao salvar barbeiro: ' . $e->getMessage()));
    exit;
}
