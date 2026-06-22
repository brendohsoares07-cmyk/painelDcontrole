<?php include("../conf/conexao.php"); ?>

<h2>Cancelar Agendamento</h2>

<form method="POST">
    ID do agendamento: <input type="number" name="id"><br><br>
    <button type="submit">Cancelar</button>
</form>

<?php
if ($_POST) {
    $id = $_POST['id'];

    $sql = "UPDATE agendamentos SET status='cancelado' WHERE id=$id";

    if ($conn->query($sql)) {
        echo "Agendamento cancelado!";
    } else {
        echo "Erro ao cancelar.";
    }
}
?>