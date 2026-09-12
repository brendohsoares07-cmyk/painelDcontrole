<?php
require_once __DIR__ . '/../includes/auth.php';

$clientes  = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome")->fetchAll();
$barbeiros = $pdo->query("SELECT id, nome FROM barbeiros WHERE status = 'ativo' ORDER BY nome")->fetchAll();
$servicos  = $pdo->query("SELECT id, nome, preco FROM servicos ORDER BY nome")->fetchAll();

$pageTitle = 'Novo Agendamento';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="content">
    <h2 class="mb-3">Novo Agendamento</h2>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($clientes) || empty($barbeiros) || empty($servicos)): ?>
                <div class="alert alert-warning mb-0">
                    Para criar um agendamento é necessário ter ao menos <strong>um cliente</strong>,
                    <strong>um barbeiro ativo</strong> e <strong>um serviço</strong> cadastrados.
                </div>
            <?php else: ?>
            <form method="post" action="salvar.php" id="formAgendamento">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cliente *</label>
                        <select name="cliente_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Barbeiro *</label>
                        <select name="barbeiro_id" id="barbeiro_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($barbeiros as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Serviço *</label>
                        <select name="servico_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($servicos as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['nome']) ?> - R$ <?= number_format($s['preco'], 2, ',', '.') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Data *</label>
                        <input type="date" name="data_agendamento" id="data_agendamento" class="form-control"
                               required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Horário disponível *</label>
                        <select name="hora_agendamento" id="hora_agendamento" class="form-select" required>
                            <option value="">Selecione barbeiro e data primeiro</option>
                        </select>
                        <div class="form-text">Os horários já ocupados não são exibidos na lista.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Agendar</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const barbeiroSelect = document.getElementById('barbeiro_id');
const dataInput = document.getElementById('data_agendamento');
const horaSelect = document.getElementById('hora_agendamento');

function carregarHorarios() {
    const barbeiroId = barbeiroSelect ? barbeiroSelect.value : '';
    const data = dataInput ? dataInput.value : '';

    if (!horaSelect) return;

    if (!barbeiroId || !data) {
        horaSelect.innerHTML = '<option value="">Selecione barbeiro e data primeiro</option>';
        return;
    }

    horaSelect.innerHTML = '<option value="">Carregando...</option>';

    fetch(`horarios_disponiveis.php?barbeiro_id=${encodeURIComponent(barbeiroId)}&data=${encodeURIComponent(data)}`)
        .then(res => res.json())
        .then(horarios => {
            if (!horarios.length) {
                horaSelect.innerHTML = '<option value="">Nenhum horário disponível nesta data</option>';
                return;
            }
            horaSelect.innerHTML = '<option value="">Selecione...</option>' +
                horarios.map(h => `<option value="${h}">${h}</option>`).join('');
        })
        .catch(() => {
            horaSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
        });
}

if (barbeiroSelect) barbeiroSelect.addEventListener('change', carregarHorarios);
if (dataInput) dataInput.addEventListener('change', carregarHorarios);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
