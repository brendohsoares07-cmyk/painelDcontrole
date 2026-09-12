<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';

$imagens = [
    ['url' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1200&q=85', 'titulo' => 'Ambiente moderno', 'categoria' => 'Espaço'],
    ['url' => 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1200&q=85', 'titulo' => 'Corte profissional', 'categoria' => 'Cortes'],
    ['url' => 'https://images.unsplash.com/photo-1599351431202-1e0f0d6e0f5b?auto=format&fit=crop&w=1200&q=85', 'titulo' => 'Estilo masculino', 'categoria' => 'Cortes'],
    ['url' => 'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=1200&q=85', 'titulo' => 'Ferramentas profissionais', 'categoria' => 'Detalhes'],
    ['url' => 'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1200&q=85', 'titulo' => 'Barbearia clássica', 'categoria' => 'Espaço'],
    ['url' => 'https://images.unsplash.com/photo-1622296089863-eb7fc530daa8?auto=format&fit=crop&w=1200&q=85', 'titulo' => 'Barba e acabamento', 'categoria' => 'Barba'],
];
$pageTitle = 'Galeria';
include __DIR__ . '/includes/public_header.php';
?>
<section class="public-page-header"><div class="container"><span class="section-kicker text-gold">Galeria</span><h1 class="display-5 fw-bold text-white">Conheça nosso estilo</h1><p class="text-light-emphasis">Um pouco do ambiente, dos cortes e dos detalhes que fazem parte da experiência.</p></div></section>
<section class="section-light py-5"><div class="container"><div class="row g-4">
<?php foreach ($imagens as $imagem): ?>
    <div class="col-sm-6 col-lg-4"><figure class="gallery-card mb-0"><img src="<?= htmlspecialchars($imagem['url']) ?>" alt="<?= htmlspecialchars($imagem['titulo']) ?>"><figcaption><span><?= htmlspecialchars($imagem['categoria']) ?></span><strong><?= htmlspecialchars($imagem['titulo']) ?></strong></figcaption></figure></div>
<?php endforeach; ?>
</div></div></section>
<?php include __DIR__ . '/includes/public_footer.php'; ?>
