<?php
require_once __DIR__ . '/../includes/auth.php';

$busca = trim($_GET['busca'] ?? '');

$camposExtras = "
    fn_calcular_idade(data_nascimento) AS idade,
    fn_valor_total_cliente(id) AS total_gasto
";

if ($busca !== '') {
    $termo = "%{$busca}%";
    $stmt = $pdo->prepare("SELECT *, {$camposExtras} FROM clientes WHERE nome LIKE ? OR email LIKE ? ORDER BY nome");
    $stmt->execute([$termo, $termo]);
} else {
    $stmt = $pdo->query("SELECT *, {$camposExtras} FROM clientes ORDER BY nome");
}
$clientes = $stmt->fetchAll();

$pageTitle = 'Clientes';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">Clientes</h2>
        <a href="form.php" class="btn btn-dark">
            <i class="bi bi-plus-circle"></i> Novo Cliente
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
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou e-mail"
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
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Idade</th>
                            <th>Total gasto</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientes)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">Nenhum cliente encontrado.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['nome']) ?></td>
                                <td><?= htmlspecialchars($c['telefone'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($c['email'] ?: '-') ?></td>
                                <td><?= $c['idade'] !== null ? $c['idade'] . ' anos' : '-' ?></td>
                                <td>R$ <?= number_format($c['total_gasto'], 2, ',', '.') ?></td>
                                <td class="text-end">
                                    <a href="form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <a href="excluir.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Deseja realmente excluir o cliente \'<?= htmlspecialchars(addslashes($c['nome'])) ?>\'? Esta ação não pode ser desfeita.');">
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
