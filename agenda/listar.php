<?php
require_once __DIR__ . '/../includes/auth.php';

$dataFiltro = $_GET['data'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT a.*, c.nome AS cliente, b.nome AS barbeiro, s.nome AS servico
    FROM agendamentos a
    JOIN clientes c  ON c.id = a.cliente_id
    JOIN barbeiros b ON b.id = a.barbeiro_id
    JOIN servicos s  ON s.id = a.servico_id
    WHERE a.data_agendamento = ?
    ORDER BY a.hora_agendamento
");
$stmt->execute([$dataFiltro]);
$agendamentos = $stmt->fetchAll();

$pageTitle = 'Agenda';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">Agenda</h2>
        <a href="form.php" class="btn btn-dark">
            <i class="bi bi-plus-circle"></i> Novo Agendamento
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
            <label class="col-form-label">Data:</label>
        </div>
        <div class="col-auto">
            <input type="date" name="data" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>"
                   onchange="this.form.submit()">
        </div>
        <div class="col-auto">
            <a href="?data=<?= date('Y-m-d') ?>" class="btn btn-outline-dark">Hoje</a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            Agendamentos de <?= date('d/m/Y', strtotime($dataFiltro)) ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Barbeiro</th>
                            <th>Serviço</th>
                            <th>Preço</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($agendamentos)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">Nenhum agendamento para esta data.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($agendamentos as $a): ?>
                            <tr>
                                <td><?= substr($a['hora_agendamento'], 0, 5) ?></td>
                                <td><?= htmlspecialchars($a['cliente']) ?></td>
                                <td><?= htmlspecialchars($a['barbeiro']) ?></td>
                                <td><?= htmlspecialchars($a['servico']) ?></td>
                                <td>R$ <?= number_format($a['valor_servico'], 2, ',', '.') ?></td>
                                <td>
                                    <?php if ($a['status'] === 'agendado'): ?>
                                        <span class="badge badge-agendado">Agendado</span>
                                    <?php elseif ($a['status'] === 'concluido'): ?>
                                        <span class="badge badge-concluido">Concluído</span>
                                    <?php else: ?>
                                        <span class="badge badge-cancelado">Cancelado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($a['status'] === 'agendado'): ?>
                                        <form action="pagamento.php" method="post" class="d-inline-flex gap-1 align-items-center">
                                            <input type="hidden" name="agendamento_id" value="<?= $a['id'] ?>">
                                            <input type="hidden" name="data" value="<?= $dataFiltro ?>">
                                            <select name="forma_pagamento" class="form-select form-select-sm" style="width:auto" required>
                                                <option value="dinheiro">Dinheiro</option>
                                                <option value="pix">Pix</option>
                                                <option value="cartao">Cartão</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Registrar pagamento e concluir">
                                                <i class="bi bi-cash-coin"></i> Pagar
                                            </button>
                                        </form>
                                        <a href="status.php?id=<?= $a['id'] ?>&status=cancelado&data=<?= $dataFiltro ?>"
                                           class="btn btn-sm btn-outline-warning" title="Cancelar"
                                           onclick="return confirm('Deseja cancelar este agendamento?');">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="excluir.php?id=<?= $a['id'] ?>&data=<?= $dataFiltro ?>"
                                       class="btn btn-sm btn-outline-danger" title="Excluir"
                                       onclick="return confirm('Deseja realmente excluir este agendamento? Esta ação não pode ser desfeita.');">
                                        <i class="bi bi-trash"></i>
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
