<?php
require_once __DIR__ . '/includes/auth.php';

// Concentra todos os indicadores do dashboard em uma única chamada
// à stored procedure sp_dashboard, em vez de disparar 5 consultas separadas.
$stmtIndicadores = $pdo->prepare("CALL sp_dashboard()");
$stmtIndicadores->execute();
$indicadores = $stmtIndicadores->fetch();
$stmtIndicadores->closeCursor();

$totalClientes    = (int) $indicadores['total_clientes'];
$totalBarbeiros   = (int) $indicadores['total_barbeiros'];
$totalServicos    = (int) $indicadores['total_servicos'];
$agendamentosHoje = (int) $indicadores['agendamentos_hoje'];
$faturamentoMes   = (float) $indicadores['faturamento_mes'];

// Usa a view vw_dashboard (já une clientes, barbeiros e serviços)
$stmtProximos = $pdo->prepare("
    SELECT *
    FROM vw_dashboard
    WHERE data_agendamento >= ? AND status = 'agendado'
    ORDER BY data_agendamento ASC, hora_agendamento ASC
    LIMIT 8
");
$stmtProximos->execute([date('Y-m-d')]);
$proximos = $stmtProximos->fetchAll();

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<div class="content">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-stat bg-gradient-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Clientes</div>
                        <div class="fs-3 fw-bold"><?= $totalClientes ?></div>
                    </div>
                    <i class="bi bi-people icon"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-stat bg-gradient-gold">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Barbeiros ativos</div>
                        <div class="fs-3 fw-bold"><?= $totalBarbeiros ?></div>
                    </div>
                    <i class="bi bi-person-badge icon"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-stat bg-gradient-blue">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Agendamentos hoje</div>
                        <div class="fs-3 fw-bold"><?= $agendamentosHoje ?></div>
                    </div>
                    <i class="bi bi-calendar-check icon"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-stat bg-gradient-green">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Faturamento do mês</div>
                        <div class="fs-4 fw-bold">R$ <?= number_format($faturamentoMes, 2, ',', '.') ?></div>
                    </div>
                    <i class="bi bi-cash-coin icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-calendar-week"></i> Próximos agendamentos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Barbeiro</th>
                            <th>Serviço</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($proximos)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Nenhum agendamento futuro encontrado.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($proximos as $p): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($p['data_agendamento'])) ?></td>
                                <td><?= substr($p['hora_agendamento'], 0, 5) ?></td>
                                <td><?= htmlspecialchars($p['cliente']) ?></td>
                                <td><?= htmlspecialchars($p['barbeiro']) ?></td>
                                <td><?= htmlspecialchars($p['servico']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
