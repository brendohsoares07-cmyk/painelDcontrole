<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$erro = '';
$sucesso = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string)($_POST['nome'] ?? ''));
    $nota = (int)($_POST['nota'] ?? 0);
    $comentario = trim((string)($_POST['comentario'] ?? ''));
    if ($nome === '' || $nota < 1 || $nota > 5 || mb_strlen($comentario) < 5) {
        $erro = 'Informe seu nome, uma nota de 1 a 5 e um comentário com pelo menos 5 caracteres.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO avaliacoes (nome, nota, comentario, aprovado) VALUES (?, ?, ?, 1)');
        $stmt->execute([$nome, $nota, $comentario]);
        $sucesso = true;
    }
}

$avaliacoes = $pdo->query("SELECT nome, nota, comentario, criado_em FROM avaliacoes WHERE aprovado = 1 ORDER BY criado_em DESC")->fetchAll();
$media = (float)($pdo->query("SELECT COALESCE(AVG(nota), 0) FROM avaliacoes WHERE aprovado = 1")->fetchColumn());
$pageTitle = 'Avaliações';
include __DIR__ . '/includes/public_header.php';
?>
<section class="public-page-header"><div class="container"><span class="section-kicker text-gold">Avaliações</span><h1 class="display-5 fw-bold text-white">Sua opinião importa</h1><p class="text-light-emphasis">Avalie sua experiência e ajude outros clientes a conhecer a barbearia.</p></div></section>
<section class="section-light py-5"><div class="container"><div class="row g-4">
<div class="col-lg-5"><div class="review-form-card"><span class="section-kicker">Deixe sua avaliação</span><h2 class="h3 fw-bold mt-2">Como foi seu atendimento?</h2>
<?php if ($sucesso): ?><div class="alert alert-success">Obrigado! Sua avaliação foi registrada.</div><?php endif; ?>
<?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<form method="post" class="mt-4"><div class="mb-3"><label class="form-label">Nome</label><input class="form-control" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"></div><div class="mb-3"><label class="form-label">Nota</label><select class="form-select" name="nota" required><option value="">Selecione</option><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> estrela<?= $i>1?'s':'' ?></option><?php endfor; ?></select></div><div class="mb-3"><label class="form-label">Comentário</label><textarea class="form-control" name="comentario" rows="5" required><?= htmlspecialchars($_POST['comentario'] ?? '') ?></textarea></div><button class="btn btn-gold" type="submit"><i class="bi bi-star"></i> Publicar avaliação</button></form></div></div>
<div class="col-lg-7"><div class="rating-overview mb-4"><div class="rating-number"><?= number_format($media, 1, ',', '.') ?></div><div><div class="stars big"><?= str_repeat('★', (int)round($media)) . str_repeat('☆', 5 - (int)round($media)) ?></div><strong>Experiência dos clientes</strong><small class="d-block text-muted"><?= count($avaliacoes) ?> avaliação(ões) publicada(s)</small></div></div><div class="row g-3">
<?php foreach ($avaliacoes as $a): ?><div class="col-md-6"><article class="review-light-card h-100"><div class="stars"><?= str_repeat('★', (int)$a['nota']) . str_repeat('☆', 5-(int)$a['nota']) ?></div><p class="mb-3">“<?= htmlspecialchars($a['comentario']) ?>”</p><strong><?= htmlspecialchars($a['nome']) ?></strong><small class="d-block text-muted"><?= date('d/m/Y', strtotime($a['criado_em'])) ?></small></article></div><?php endforeach; ?>
</div></div></div></div></section>
<?php include __DIR__ . '/includes/public_footer.php'; ?>
