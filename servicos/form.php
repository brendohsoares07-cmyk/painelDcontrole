<?php
require_once __DIR__ . '/../includes/auth.php';

$servico = ['id' => '', 'nome' => '', 'descricao' => '', 'preco' => '', 'duracao_minutos' => 30];
$editando = false;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $encontrado = $stmt->fetch();
    if ($encontrado) {
        $servico = $encontrado;
        $editando = true;
    }
}

$pageTitle = $editando ? 'Editar Serviço' : 'Novo Serviço';
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
                <input type="hidden" name="id" value="<?= htmlspecialchars($servico['id']) ?>">

                <div class="mb-3">
                    <label class="form-label">Nome do serviço *</label>
                    <input type="text" name="nome" class="form-control" required maxlength="100"
                           value="<?= htmlspecialchars($servico['nome']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3"><?= htmlspecialchars($servico['descricao']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Preço (R$) *</label>
                        <input type="number" step="0.01" min="0" name="preco" class="form-control" required
                               value="<?= htmlspecialchars($servico['preco']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duração (minutos) *</label>
                        <input type="number" step="5" min="5" name="duracao_minutos" class="form-control" required
                               value="<?= htmlspecialchars($servico['duracao_minutos']) ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
