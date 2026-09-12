<?php
require_once __DIR__ . '/../includes/auth.php';

$busca = trim($_GET['busca'] ?? '');

if ($busca !== '') {
    $termo = "%{$busca}%";
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE nome LIKE ? ORDER BY nome");
    $stmt->execute([$termo]);
} else {
    $stmt = $pdo->query("SELECT * FROM servicos ORDER BY nome");
}
$servicos = $stmt->fetchAll();

$pageTitle = 'Serviços';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">Serviços</h2>
        <a href="form.php" class="btn btn-dark">
            <i class="bi bi-plus-circle"></i> Novo Serviço
        </a>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_GET['sucesso']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_GET['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form class="row g-2 mb-3" method="get">
        <div class="col-auto">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome do serviço"
                   value="<?= htmlspecialchars($busca) ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Buscar</button>
        </div>
        <?php if ($busca !== ''): ?>
        <div class="col-auto">
            <a href="listar.php" class="btn btn-outline-dark">Limpar</a>
        </div>
        <?php endif; ?>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Preço</th>
                            <th>Duração</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($servicos)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Nenhum serviço encontrado.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($servicos as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['nome']) ?></td>
                                <td><?= htmlspecialchars($s['descricao'] ?: '-') ?></td>
                                <td>R$ <?= number_format($s['preco'], 2, ',', '.') ?></td>
                                <td><?= (int) $s['duracao_minutos'] ?> min</td>
                                <td class="text-end">
                                    <a href="form.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <a href="excluir.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Deseja realmente excluir o serviço \'<?= htmlspecialchars(addslashes($s['nome'])) ?>\'? Esta ação não pode ser desfeita.');">
                                        <i class="bi bi-trash"></i> Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
