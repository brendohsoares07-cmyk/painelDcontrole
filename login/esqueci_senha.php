<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $erro = 'Informe seu e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, nome FROM usuarios WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $_SESSION['recuperacao_usuario_id'] = $usuario['id'];

            header(
                'Location: ' . BASE_URL . 'nova_senha.php'
            );
            exit;
        } else {
            $erro = 'E-mail não encontrado.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Esqueci minha senha | Barbearia System</title>

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
            <div class="fs-5">Recuperar senha</div>
        </div>

        <div class="card-body p-4">

            <?php if ($erro): ?>
                <div class="alert alert-danger py-2">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <p class="text-muted">
                Informe o e-mail cadastrado para continuar.
            </p>

            <form method="post">

                <div class="mb-3">

                    <label class="form-label">
                        E-mail
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            required
                            autofocus
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        >

                    </div>

                </div>

                <button
                    type="submit"
                    class="btn btn-dark w-100"
                >
                    <i class="bi bi-arrow-right"></i>
                    Continuar
                </button>

            </form>

            <div class="text-center mt-3">

                <a
                    href="<?= BASE_URL ?>login.php"
                    class="text-decoration-none"
                >
                    <i class="bi bi-arrow-left"></i>
                    Voltar para o login
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>