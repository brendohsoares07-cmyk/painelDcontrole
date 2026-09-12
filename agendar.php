<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$barbeiros = $pdo->query("SELECT id, nome, especialidade FROM barbeiros WHERE status = 'ativo' ORDER BY nome")->fetchAll();
$servicos = $pdo->query("SELECT id, nome, preco, duracao_minutos FROM servicos ORDER BY nome")->fetchAll();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $barbeiroId = (int) ($_POST['barbeiro_id'] ?? 0);
    $servicoId = (int) ($_POST['servico_id'] ?? 0);
    $data = (string) ($_POST['data_agendamento'] ?? '');
    $hora = (string) ($_POST['hora_agendamento'] ?? '');

    if ($nome === '' || $telefone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$barbeiroId || !$servicoId || $data === '' || $hora === '') {
        $erro = 'Preencha todos os campos corretamente.';
    } elseif ($data < date('Y-m-d')) {
        $erro = 'Escolha uma data a partir de hoje.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmtCliente = $pdo->prepare('SELECT id FROM clientes WHERE email = ? LIMIT 1');
            $stmtCliente->execute([$email]);
            $clienteId = $stmtCliente->fetchColumn();

            if (!$clienteId) {
                $stmtCliente = $pdo->prepare('INSERT INTO clientes (nome, telefone, email) VALUES (?, ?, ?)');
                $stmtCliente->execute([$nome, $telefone, $email]);
                $clienteId = (int) $pdo->lastInsertId();
            } else {
                $stmtCliente = $pdo->prepare('UPDATE clientes SET nome = ?, telefone = ? WHERE id = ?');
                $stmtCliente->execute([$nome, $telefone, $clienteId]);
            }

            $stmtServico = $pdo->prepare("SELECT preco FROM servicos WHERE id = ?");
            $stmtServico->execute([$servicoId]);
            $preco = $stmtServico->fetchColumn();
            if ($preco === false) {
                throw new RuntimeException('Serviço não encontrado.');
            }

            $stmt = $pdo->prepare("INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, valor_servico, data_agendamento, hora_agendamento, status) VALUES (?, ?, ?, ?, ?, ?, 'agendado')");
            $stmt->execute([$clienteId, $barbeiroId, $servicoId, $preco, $data, $hora]);
            $agendamentoId = (int) $pdo->lastInsertId();
            $pdo->commit();

            header('Location: ' . BASE_URL . 'pagamento_publico.php?id=' . $agendamentoId);
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erro = $e->getCode() === '23000'
                ? 'Esse horário acabou de ser ocupado. Escolha outro horário.'
                : 'Não foi possível criar o agendamento. Verifique os dados e tente novamente.';
        }
    }
}

$pageTitle = 'Agendar';
include __DIR__ . '/includes/public_header.php';
?>
<section class="public-page-header"><div class="container"><span class="section-kicker text-gold">Agendamento</span><h1 class="display-5 fw-bold text-white">Reserve seu horário</h1><p class="text-light-emphasis">Depois do agendamento, você poderá escolher Pix ou cartão.</p></div></section>
<section class="section-light py-5"><div class="container"><div class="row justify-content-center"><div class="col-lg-9">
<?php if ($erro): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>
<form method="post" class="booking-form-card" novalidate>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Nome completo *</label><input class="form-control" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Telefone *</label><input class="form-control" name="telefone" required value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">E-mail *</label><input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Barbeiro *</label><select class="form-select" name="barbeiro_id" id="publicBarbeiro" required><option value="">Selecione...</option><?php foreach ($barbeiros as $b): ?><option value="<?= (int) $b['id'] ?>" <?= ((int)($_POST['barbeiro_id'] ?? 0) === (int)$b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['nome']) ?> — <?= htmlspecialchars($b['especialidade']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Serviço *</label><select class="form-select" name="servico_id" required><option value="">Selecione...</option><?php foreach ($servicos as $s): ?><option value="<?= (int) $s['id'] ?>" <?= ((int)($_POST['servico_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nome']) ?> — R$ <?= number_format((float)$s['preco'], 2, ',', '.') ?> (<?= (int)$s['duracao_minutos'] ?> min)</option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Data *</label><input type="date" class="form-control" name="data_agendamento" id="publicData" min="<?= date('Y-m-d') ?>" required value="<?= htmlspecialchars($_POST['data_agendamento'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Horário *</label><select class="form-select" name="hora_agendamento" id="publicHora" required><option value="">Selecione barbeiro e data</option></select></div>
    </div>
    <div class="payment-info mt-4"><i class="bi bi-shield-check"></i><div><strong>Pagamento seguro para o projeto</strong><span>O sistema não armazena número completo, CVV ou senha do cartão.</span></div></div>
    <button class="btn btn-gold btn-lg mt-4" type="submit"><i class="bi bi-arrow-right-circle"></i> Continuar para pagamento</button>
</form>
</div></div></div></section>
<script>
const pb = document.getElementById('publicBarbeiro');
const pd = document.getElementById('publicData');
const ph = document.getElementById('publicHora');
async function carregarHorariosPublicos() {
    if (!pb || !pd || !ph || !pb.value || !pd.value) { if (ph) ph.innerHTML = '<option value="">Selecione barbeiro e data</option>'; return; }
    ph.innerHTML = '<option value="">Carregando...</option>';
    try {
        const resposta = await fetch(`api/horarios.php?barbeiro_id=${encodeURIComponent(pb.value)}&data=${encodeURIComponent(pd.value)}`);
        if (!resposta.ok) throw new Error('Falha ao consultar horários');
        const horarios = await resposta.json();
        ph.innerHTML = horarios.length ? '<option value="">Selecione...</option>' + horarios.map(h => `<option value="${h}">${h}</option>`).join('') : '<option value="">Nenhum horário disponível</option>';
    } catch (erro) { ph.innerHTML = '<option value="">Não foi possível carregar</option>'; }
}
pb?.addEventListener('change', carregarHorariosPublicos);
pd?.addEventListener('change', carregarHorariosPublicos);
carregarHorariosPublicos();
</script>
<?php include __DIR__ . '/includes/public_footer.php'; ?>
