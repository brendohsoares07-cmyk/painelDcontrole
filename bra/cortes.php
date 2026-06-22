<?php include("includes/header.php"); 

$cortes = [
    ["codigo" => 1, "nome" => "Degradê", "preco" => 25],
    ["codigo" => 2, "nome" => "Social", "preco" => 25],
    ["codigo" => 3, "nome" => "Navalhado", "preco" => 25]
];

?>

<div class="container">

    <h1>✂️ Galeria de Cortes</h1>
    <p>Confira alguns dos nossos estilos mais procurados.</p>

    <div class="galeria">

        <div class="corte">
            <img src="assets/img/lowfade.jpeg" alt="Corte Degradê">
            <h3>Degradê</h3>
        </div>

        <div class="corte">
            <img src="assets/img/moicano.jpg" alt="Moicano">
            <h3>Moicano</h3>
        </div>

        <div class="corte">
            <img src="assets/img/a.jpg" alt="Americano">
            <h3>Americano</h3>
        </div>

    </div>

</div>

<?php include("includes/footer.php"); ?>