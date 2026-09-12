<?php
include("conf/conexao.php");
include("includes/header.php");
include("funcoes.php");
?>

<?php

$horariosDisponiveis = [
    "09:00","10:00","11:00",
    "13:00","14:00","15:00",
    "16:00","17:00"
];

?>

<div class="container">

    <div class="card shadow p-4">

        <h2>Agendar Corte</h2>

        <br>

        <form method="POST">

            <input type="text" name="nome" placeholder="Seu nome">

            <input type="date" name="data">

            <select name="horario">

                <option value="">Selecione um horário</option>

                <?php foreach($horariosDisponiveis as $hora){ ?>
                    <option value="<?= $hora ?>"><?= $hora ?></option>
                <?php } ?>

            </select>

            <br><br>

            <button class="btn btn-primary">Agendar</button>

        </form>

<?php

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nome = $_POST['nome'];
    $data = $_POST['data'];
    $horario = $_POST['horario'];

    $validacao = validarAgendamento($nome, $data, $horario);

    if($validacao == "OK"){

        $stmt = $conn->prepare("
            INSERT INTO agendamentos (nome, data_agendamento, horario)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("sss", $nome, $data, $horario);

        if($stmt->execute()){
            echo "<br>Agendamento realizado com sucesso!";
        }

    } else {
        echo "<br>" . $validacao;
    }
}
?>

    </div>

</div>

<?php include("includes/footer.php"); ?>