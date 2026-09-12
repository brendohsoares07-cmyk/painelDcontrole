<?php
require_once __DIR__ . '/../includes/auth.php';

$cliente = ['id' => '', 'nome' => '', 'telefone' => '', 'email' => '', 'data_nascimento' => ''];
$editando = false;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $encontrado = $stmt->fetch();
    if ($encontrado) {
        $cliente = $encontrado;
        $editando = true;
    }
}

$pageTitle = $editando ? 'Editar Cliente' : 'Novo Cliente';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="content">
    <h2 class="mb-3"><?= $pageTitle ?></h2>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="salvar.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id']) ?>">

                <div class="mb-3">
                    <label class="form-label">Nome completo *</label>
                    <input type="text" name="nome" class="form-control" required maxlength="100"
                           value="<?= htmlspecialchars($cliente['nome']) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control" maxlength="20"
                               placeholder="(00) 00000-0000" value="<?= htmlspecialchars($cliente['telefone']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" maxlength="100"
                               value="<?= htmlspecialchars($cliente['email']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data de nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control"
                           value="<?= htmlspecialchars($cliente['data_nascimento']) ?>">
                </div>

                <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
