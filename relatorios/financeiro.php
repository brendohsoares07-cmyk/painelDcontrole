<?php
require_once __DIR__ . '/../includes/auth.php';

$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim    = $_GET['data_fim'] ?? date('Y-m-d');

// Usa a view vw_dashboard, já filtrando pelo status e período
$sql = "
    SELECT data_agendamento, hora_agendamento, cliente, barbeiro, servico, preco
    FROM vw_dashboard
    WHERE status = 'concluido' AND data_agendamento BETWEEN ? AND ?
    ORDER BY data_agendamento, hora_agendamento
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$dataInicio, $dataFim]);
$registros = $stmt->fetchAll();

// Resumo do período (faturamento total, atendimentos e ticket médio)
// vindo da stored procedure sp_relatorio_periodo
$stmtResumo = $pdo->prepare("CALL sp_relatorio_periodo(?, ?)");
$stmtResumo->execute([$dataInicio, $dataFim]);
$resumo = $stmtResumo->fetch();
$stmtResumo->closeCursor();

$totalGeral = (float) ($resumo['faturamento_total'] ?? 0);
$totalAtendimentos = (int) ($resumo['total_atendimentos'] ?? 0);
$ticketMedio = (float) ($resumo['ticket_medio'] ?? 0);

// Faturamento por barbeiro no período selecionado (usa o valor
// histórico valor_servico, na mesma lógica da view vw_faturamento)
$sqlBarbeiro = "
    SELECT b.nome, COUNT(*) AS qtd, SUM(a.valor_servico) AS total
    FROM agendamentos a
    JOIN barbeiros b ON b.id = a.barbeiro_id
    WHERE a.status = 'concluido' AND a.data_agendamento BETWEEN ? AND ?
    GROUP BY b.id, b.nome
    ORDER BY total DESC
";
$stmtBarbeiro = $pdo->prepare($sqlBarbeiro);
$stmtBarbeiro->execute([$dataInicio, $dataFim]);
$porBarbeiro = $stmtBarbeiro->fetchAll();

// Ranking geral dos serviços mais realizados (view vw_ranking_servicos,
// considera o histórico completo, não é filtrado pelo período)
$porServico = $pdo->query("SELECT * FROM vw_ranking_servicos")->fetchAll();

$pageTitle = 'Relatório Financeiro';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="content">
    <h2 class="mb-3">Relatório Financeiro</h2>

    <form class="row g-2 mb-4 align-items-end" method="get">
        <div class="col-auto">
            <label class="form-label mb-0">De</label>
            <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($dataInicio) ?>">
        </div>
        <div class="col-auto">
            <label class="form-label mb-0">Até</label>
            <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($dataFim) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-dark"><i class="bi bi-funnel"></i> Filtrar</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card card-stat bg-gradient-green">
                <div class="card-body">
                    <div class="small">Faturamento total</div>
                    <div class="fs-4 fw-bold">R$ <?= number_format($totalGeral, 2, ',', '.') ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-stat bg-gradient-blue">
                <div class="card-body">
                    <div class="small">Atendimentos concluídos</div>
                    <div class="fs-4 fw-bold"><?= $totalAtendimentos ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-stat bg-gradient-gold">
                <div class="card-body">
                    <div class="small">Ticket médio</div>
                    <div class="fs-4 fw-bold">R$ <?= number_format($ticketMedio, 2, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Faturamento por barbeiro</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Barbeiro</th><th>Atendimentos</th><th>Total faturado</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($porBarbeiro)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sem dados no período selecionado.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($porBarbeiro as $pb): ?>
                            <tr>
                                <td><?= htmlspecialchars($pb['nome']) ?></td>
                                <td><?= (int) $pb['qtd'] ?></td>
                                <td>R$ <?= number_format($pb['total'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Serviços mais realizados (histórico geral)</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Serviço</th><th>Vezes realizado</th><th>Faturamento gerado</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($porServico)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Ainda não há atendimentos concluídos.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($porServico as $i => $ps): ?>
                            <tr>
                                <td><?= $i + 1 ?>º</td>
                                <td><?= htmlspecialchars($ps['servico']) ?></td>
                                <td><?= (int) $ps['total_realizados'] ?></td>
                                <td>R$ <?= number_format($ps['faturamento_gerado'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Atendimentos concluídos no período</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th><th>Hora</th><th>Cliente</th><th>Barbeiro</th><th>Serviço</th><th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">Nenhum atendimento concluído neste período.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($registros as $r): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($r['data_agendamento'])) ?></td>
                                <td><?= substr($r['hora_agendamento'], 0, 5) ?></td>
                                <td><?= htmlspecialchars($r['cliente']) ?></td>
                                <td><?= htmlspecialchars($r['barbeiro']) ?></td>
                                <td><?= htmlspecialchars($r['servico']) ?></td>
                                <td>R$ <?= number_format($r['preco'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if (!empty($registros)): ?>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="text-end">Total</td>
                            <td>R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
