    _<?php

    include("../conf/conexao.php");
    include("../includes/header.php");

    $sql = "SELECT * FROM agendamentos";

    $resultado = $conn->query($sql);

    ?>

    <div class="container mt-4">

        <h2>Agendamentos</h2>

    <table class="table table-striped table-hover">

        <thead class="table-dark">
            <tr>
                <th>Nome</th>
                <th>Data</th>
                <th>Horário</th>
            </tr>
        </thead>

        <tbody>

            <?php while($linha = $resultado->fetch_assoc()) { ?>

            <tr>
                <td><?= $linha['nome'] ?></td>
                <td><?= $linha['data_agendamento'] ?></td>
                <td><?= $linha['horario'] ?></td>
            </tr>

            <?php } ?>

        </tbody>

    </table>

    </div>

    <?php include("../includes/footer.php"); ?>