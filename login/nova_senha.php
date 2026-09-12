<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['recuperacao_usuario_id'])) {
    header('Location: ' . BASE_URL . 'esqueci_senha.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    if ($senha === '' || $confirmar === '') {

        $erro = 'Preencha os dois campos.';

    } elseif ($senha !== $confirmar) {

        $erro = 'As senhas não coincidem.';

    } elseif (strlen($senha) < 6) {

        $erro = 'A senha deve ter pelo menos 6 caracteres.';

    } else {

        $senhaHash = password_hash(
            $senha,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare(
            'UPDATE usuarios SET senha = ? WHERE id = ?'
        );

        $stmt->execute([
            $senhaHash,
            $_SESSION['recuperacao_usuario_id']
        ]);

        unset($_SESSION['recuperacao_usuario_id']);

        $sucesso = 'Senha alterada com sucesso!';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nova senha | Barbearia System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    >

    <link
        href="<?= BASE_URL ?>assets/css/style.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="login-wrapper">

    <div class="card login-card">

        <div class="card-header py-3">

            <i class="bi bi-scissors fs-3"></i>

            <div class="fs-5">
                Nova senha
            </div>

        </div>

        <div class="card-body p-4">

            <?php if ($erro): ?>

                <div class="alert alert-danger py-2">
                    <?= htmlspecialchars($erro) ?>
                </div>

            <?php endif; ?>

            <?php if ($sucesso): ?>

                <div class="alert alert-success">
                    <?= htmlspecialchars($sucesso) ?>
                </div>

                <a
                    href="<?= BASE_URL ?>login.php"
                    class="btn btn-dark w-100"
                >
                    Voltar para o login
                </a>

            <?php else: ?>

                <form method="post">

                    <div class="mb-3">

                        <label class="form-label">
                            Nova senha
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>

                            <input
                                type="password"
                                name="senha"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Confirmar nova senha
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>

                            <input
                                type="password"
                                name="confirmar_senha"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-dark w-100"
                    >
                        <i class="bi bi-check-lg"></i>
                        Alterar senha
                    </button>

                </form>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>