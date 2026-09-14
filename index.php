<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$servicos = $pdo->query(
    "SELECT id, nome, descricao, preco, duracao_minutos FROM servicos ORDER BY id LIMIT 6"
)->fetchAll();

$avaliacoes = $pdo->query(
    "SELECT nome, nota, comentario, criado_em FROM avaliacoes WHERE aprovado = 1 ORDER BY criado_em DESC LIMIT 6"
)->fetchAll();

$media = (float) ($pdo->query(
    "SELECT COALESCE(AVG(nota), 0) FROM avaliacoes WHERE aprovado = 1"
)->fetchColumn());

$totalAvaliacoes = (int) ($pdo->query(
    "SELECT COUNT(*) FROM avaliacoes WHERE aprovado = 1"
)->fetchColumn());

$pageTitle = 'Início';
include __DIR__ . '/includes/public_header.php';
?>

<section id="inicio" class="hero-section">
    <div class="container py-5">
        <div class="row align-items-center min-vh-75 g-5">
            <div class="col-lg-7">
                <span class="badge hero-badge mb-3"><i class="bi bi-stars"></i> Seu estilo começa aqui</span>
                <h1 class="display-3 fw-bold text-white">Corte de respeito.<br><span class="text-gold">Estilo de verdade.</span></h1>
                <p class="lead text-light-emphasis mt-3 mb-4">Agende seu horário online, escolha o serviço e pague por Pix ou cartão com praticidade.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-gold btn-lg" href="<?= BASE_URL ?>agendar.php"><i class="bi bi-calendar-check"></i> Agendar agora</a>
                    <a class="btn btn-outline-light btn-lg" href="<?= BASE_URL ?>galeria.php"><i class="bi bi-images"></i> Ver galeria</a>
                </div>
                <div class="hero-stats mt-5">
                    <div><strong>5+</strong><span>serviços</span></div>
                    <div><strong><?= number_format($media, 1, ',', '.') ?></strong><span>avaliação média</span></div>
                    <div><strong><?= $totalAvaliacoes ?></strong><span>avaliações</span></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-photo-card">
                    <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1000&q=85" alt="Interior moderno de uma barbearia">
                    <div class="hero-photo-caption"><i class="bi bi-scissors"></i> Atendimento com estilo</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="servicos" class="section-light py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="section-kicker">Nossos serviços</span>
            <h2 class="display-6 fw-bold">Escolha seu próximo visual</h2>
            <p class="text-muted">Profissionais preparados para deixar seu corte do seu jeito.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($servicos as $servico): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100">
                        <div class="service-icon"><i class="bi bi-scissors"></i></div>
                        <h3 class="h5 fw-bold"><?= htmlspecialchars($servico['nome']) ?></h3>
                        <p class="text-muted small"><?= htmlspecialchars($servico['descricao'] ?? 'Atendimento personalizado.') ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-3">
                            <strong class="service-price">R$ <?= number_format((float) $servico['preco'], 2, ',', '.') ?></strong>
                            <span class="small text-muted"><i class="bi bi-clock"></i> <?= (int) $servico['duracao_minutos'] ?> min</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="gallery-preview py-5">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
            <div><span class="section-kicker text-gold">Nosso espaço</span><h2 class="display-6 fw-bold text-white mb-0">Ambiente e estilo</h2></div>
            <a href="<?= BASE_URL ?>galeria.php" class="btn btn-outline-light">Ver galeria completa</a>
        </div>
        <div class="row g-3">
            <div class="col-md-6"><img class="gallery-tile large" src="https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1200&q=85" alt="Barbeiro trabalhando em um corte"></div>
            <div class="col-md-3"><img class="gallery-tile" src="https://blog.kert.com.br/wp-content/uploads/2023/03/tendencias-em-cortes-de-cabelo-masculino.jpg" alt="Corte masculino em barbearia"></div>
            <div class="col-md-3"><img class="gallery-tile" src="https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=800&q=85" alt="Ferramentas profissionais de barbearia"></div>
        </div>
    </div>
</section>

<section class="section-light py-5">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <span class="section-kicker">Experiência completa</span>
                <h2 class="display-6 fw-bold">Agende sem ligar</h2>
                <p class="text-muted">Escolha barbeiro, serviço, data e horário. Depois, finalize seu pagamento de forma simples.</p>
                <div class="row g-3 mt-2">
                    <div class="col-6"><div class="feature-box"><i class="bi bi-calendar2-check"></i><strong>Agendamento online</strong><small>Horários disponíveis em tempo real.</small></div></div>
                    <div class="col-6"><div class="feature-box"><i class="bi bi-credit-card"></i><strong>Pagamento</strong><small>Pix ou cartão em uma tela.</small></div></div>
                    <div class="col-6"><div class="feature-box"><i class="bi bi-shield-check"></i><strong>Seguro</strong><small>Dados de cartão não são armazenados.</small></div></div>
                    <div class="col-6"><div class="feature-box"><i class="bi bi-star"></i><strong>Avalie</strong><small>Conte como foi sua experiência.</small></div></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="booking-cta-card">
                    <div><span class="text-gold">Pronto para o próximo corte?</span><h3 class="text-white fw-bold">Reserve seu horário em poucos passos.</h3></div>
                    <a class="btn btn-gold" href="<?= BASE_URL ?>agendar.php">Quero agendar <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="reviews-section py-5">
    <div class="container py-4">
        <div class="text-center mb-5"><span class="section-kicker text-gold">Quem já veio</span><h2 class="display-6 fw-bold text-white">Avaliações dos clientes</h2></div>
        <div class="row g-4">
            <?php if (!$avaliacoes): ?>
                <div class="col-12 text-center text-light-emphasis">Seja o primeiro a avaliar nossa barbearia.</div>
            <?php endif; ?>
            <?php foreach ($avaliacoes as $avaliacao): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="review-card h-100">
                        <div class="stars" aria-label="<?= (int) $avaliacao['nota'] ?> de 5 estrelas"><?= str_repeat('★', (int) $avaliacao['nota']) . str_repeat('☆', 5 - (int) $avaliacao['nota']) ?></div>
                        <p>“<?= htmlspecialchars($avaliacao['comentario']) ?>”</p>
                        <strong><?= htmlspecialchars($avaliacao['nome']) ?></strong>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4"><a href="<?= BASE_URL ?>avaliacoes.php" class="btn btn-outline-light">Ver e deixar avaliação</a></div>
    </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
