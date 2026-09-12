<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$id = (int) ($_GET['id'] ?? $_POST['agendamento_id'] ?? 0);
if ($id <= 0) { header('Location: ' . BASE_URL . 'agendar.php'); exit; }

$stmt = $pdo->prepare("SELECT a.id, a.valor_servico, a.data_agendamento, a.hora_agendamento, a.status, c.nome AS cliente, s.nome AS servico, b.nome AS barbeiro FROM agendamentos a JOIN clientes c ON c.id = a.cliente_id JOIN servicos s ON s.id = a.servico_id JOIN barbeiros b ON b.id = a.barbeiro_id WHERE a.id = ?");
$stmt->execute([$id]);
$agendamento = $stmt->fetch();
if (!$agendamento) { header('Location: ' . BASE_URL . 'agendar.php'); exit; }

$stmtPago = $pdo->prepare('SELECT id, forma_pagamento, pago_em FROM pagamentos WHERE agendamento_id = ? ORDER BY id DESC LIMIT 1');
$stmtPago->execute([$id]);
$pagamentoExistente = $stmtPago->fetch();
$erro = '';
$sucesso = isset($_GET['sucesso']);
$pixCodigo = '00020126580014BR.GOV.BCB.PIX0136barbearia-system-demo-' . str_pad((string)$id, 10, '0', STR_PAD_LEFT) . '5204000053039865404' . number_format((float)$agendamento['valor_servico'], 2, '', '') . '5802BR5917BARBEARIA SYSTEM6009CAMPO MOURAO6304DEMO';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pagamentoExistente) {
    $forma = (string) ($_POST['forma_pagamento'] ?? '');
    try {
        if (!in_array($forma, ['pix', 'cartao'], true)) throw new RuntimeException('Escolha Pix ou cartão.');

        if ($forma === 'cartao') {
            $numero = preg_replace('/\D+/', '', (string)($_POST['numero_cartao'] ?? ''));
            $validade = trim((string)($_POST['validade'] ?? ''));
            $cvv = preg_replace('/\D+/', '', (string)($_POST['cvv'] ?? ''));
            $titular = trim((string)($_POST['titular'] ?? ''));
            if (strlen($numero) < 13 || strlen($numero) > 19 || strlen($cvv) < 3 || strlen($cvv) > 4 || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $validade) || $titular === '') {
                throw new RuntimeException('Confira os dados do cartão.');
            }
            // Simulação acadêmica: nenhum dado do cartão é salvo no banco.
        }

        $stmt = $pdo->prepare('CALL sp_registrar_pagamento(?, ?)');
        $stmt->execute([$id, $forma]);
        $stmt->closeCursor();
        header('Location: ' . BASE_URL . 'pagamento_publico.php?id=' . $id . '&sucesso=1');
        exit;
    } catch (Throwable $e) {
        $erro = $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível registrar o pagamento. Tente novamente.';
    }
}

if ($sucesso) {
    $stmtPago = $pdo->prepare('SELECT id, forma_pagamento, pago_em FROM pagamentos WHERE agendamento_id = ? ORDER BY id DESC LIMIT 1');
    $stmtPago->execute([$id]);
    $pagamentoExistente = $stmtPago->fetch();
}

$pageTitle = 'Pagamento';
include __DIR__ . '/includes/public_header.php';
?>
<section class="public-page-header"><div class="container"><span class="section-kicker text-gold">Finalização</span><h1 class="display-5 fw-bold text-white">Pagamento do agendamento</h1><p class="text-light-emphasis">Projeto acadêmico: o pagamento é demonstrativo e não processa uma cobrança real.</p></div></section>
<section class="section-light py-5"><div class="container"><div class="row justify-content-center g-4">
    <div class="col-lg-5">
        <div class="summary-card h-100">
            <span class="section-kicker">Resumo</span><h2 class="h4 fw-bold mt-2">Seu atendimento</h2>
            <dl class="row mt-4 mb-0">
                <dt class="col-5">Cliente</dt><dd class="col-7"><?= htmlspecialchars($agendamento['cliente']) ?></dd>
                <dt class="col-5">Serviço</dt><dd class="col-7"><?= htmlspecialchars($agendamento['servico']) ?></dd>
                <dt class="col-5">Barbeiro</dt><dd class="col-7"><?= htmlspecialchars($agendamento['barbeiro']) ?></dd>
                <dt class="col-5">Data</dt><dd class="col-7"><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></dd>
                <dt class="col-5">Horário</dt><dd class="col-7"><?= substr($agendamento['hora_agendamento'], 0, 5) ?></dd>
            </dl>
            <hr><div class="d-flex justify-content-between align-items-center"><strong>Total</strong><strong class="display-6 service-price">R$ <?= number_format((float)$agendamento['valor_servico'], 2, ',', '.') ?></strong></div>
        </div>
    </div>
    <div class="col-lg-7">
        <?php if ($erro): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <?php if ($pagamentoExistente): ?>
            <div class="payment-success-card text-center">
                <div class="success-icon"><i class="bi bi-check-lg"></i></div>
                <h2 class="h3 fw-bold">Pagamento confirmado!</h2>
                <p class="text-muted">Forma utilizada: <strong><?= strtoupper(htmlspecialchars($pagamentoExistente['forma_pagamento'])) ?></strong></p>
                <p class="small text-muted">Agendamento #<?= (int)$id ?> concluído pelo sistema.</p>
                <a href="<?= BASE_URL ?>avaliacoes.php" class="btn btn-gold"><i class="bi bi-star"></i> Avaliar atendimento</a>
            </div>
        <?php else: ?>
            <div class="payment-card">
                <h2 class="h4 fw-bold mb-3">Escolha a forma de pagamento</h2>
                <div class="payment-tabs mb-4">
                    <button type="button" class="payment-tab active" data-payment="pix"><i class="bi bi-qr-code"></i> Pix</button>
                    <button type="button" class="payment-tab" data-payment="cartao"><i class="bi bi-credit-card"></i> Cartão</button>
                </div>
                <form method="post" id="paymentForm">
                    <input type="hidden" name="agendamento_id" value="<?= (int)$id ?>"><input type="hidden" name="forma_pagamento" id="formaPagamento" value="pix">
                    <div id="pixPanel" class="payment-panel">
                        <div class="pix-demo-box"><div class="pix-qr"><i class="bi bi-qr-code fs-1"></i><small>QR DEMO</small></div><div><strong>Pix Copia e Cola</strong><textarea class="form-control mt-2" rows="3" readonly><?= htmlspecialchars($pixCodigo) ?></textarea><small class="text-muted">Código demonstrativo para a apresentação.</small></div></div>
                        <button class="btn btn-gold btn-lg w-100 mt-4" type="submit"><i class="bi bi-check-circle"></i> Simular pagamento via Pix</button>
                    </div>
                    <div id="cardPanel" class="payment-panel d-none">
                        <div class="alert alert-info small"><i class="bi bi-info-circle"></i> Demonstração acadêmica. Os dados do cartão são usados apenas para validação no formulário e não são armazenados.</div>
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label">Nome no cartão</label><input class="form-control" name="titular" autocomplete="off"></div>
                            <div class="col-12"><label class="form-label">Número do cartão</label><input class="form-control" name="numero_cartao" inputmode="numeric" autocomplete="off" maxlength="19" placeholder="0000 0000 0000 0000"></div>
                            <div class="col-6"><label class="form-label">Validade</label><input class="form-control" name="validade" placeholder="MM/AA" maxlength="5" autocomplete="off"></div>
                            <div class="col-6"><label class="form-label">CVV</label><input class="form-control" name="cvv" inputmode="numeric" maxlength="4" autocomplete="off"></div>
                        </div>
                        <button class="btn btn-dark btn-lg w-100 mt-4" type="submit"><i class="bi bi-lock"></i> Simular pagamento com cartão</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div></div></section>
<script>
const tabs = document.querySelectorAll('.payment-tab');
const pixPanel = document.getElementById('pixPanel');
const cardPanel = document.getElementById('cardPanel');
const forma = document.getElementById('formaPagamento');
tabs.forEach(tab => tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('active')); tab.classList.add('active');
    const tipo = tab.dataset.payment; forma.value = tipo;
    pixPanel.classList.toggle('d-none', tipo !== 'pix'); cardPanel.classList.toggle('d-none', tipo !== 'cartao');
}));
</script>
<?php include __DIR__ . '/includes/public_footer.php'; ?>
