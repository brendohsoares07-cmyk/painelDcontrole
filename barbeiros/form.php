<?php
require_once __DIR__ . '/../includes/auth.php';

$barbeiro = ['id' => '', 'nome' => '', 'telefone' => '', 'email' => '', 'especialidade' => '', 'status' => 'ativo'];
$editando = false;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM barbeiros WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $encontrado = $stmt->fetch();
    if ($encontrado) {
        $barbeiro = $encontrado;
        $editando = true;
    }
}

$pageTitle = $editando ? 'Editar Barbeiro' : 'Novo Barbeiro';
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
                <input type="hidden" name="id" value="<?= htmlspecialchars($barbeiro['id']) ?>">

                <div class="mb-3">
                    <label class="form-label">Nome completo *</label>
                    <input type="text" name="nome" class="form-control" required maxlength="100"
                           value="<?= htmlspecialchars($barbeiro['nome']) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control" maxlength="20"
                               placeholder="(00) 00000-0000" value="<?= htmlspecialchars($barbeiro['telefone']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" maxlength="100"
                               value="<?= htmlspecialchars($barbeiro['email']) ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Especialidade</label>
                        <input type="text" name="especialidade" class="form-control" maxlength="100"
                               placeholder="Ex: corte degradê, barba, coloração"
                               value="<?= htmlspecialchars($barbeiro['especialidade']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="ativo" <?= $barbeiro['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inativo" <?= $barbeiro['status'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
