<?php
$host = "192.168.56.101";
$usuario = "root";
$senha = "";
$banco = "barbearia";

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}
?>