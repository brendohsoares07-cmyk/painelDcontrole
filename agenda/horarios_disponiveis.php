<?php
/**
 * Endpoint AJAX que retorna (em JSON) os horários disponíveis
 * para um barbeiro em uma determinada data.
 *
 * Usa a stored procedure sp_horarios_disponiveis, que gera a
 * grade de horários (09:00-19:00) com uma CTE recursiva e já
 * exclui os horários ocupados diretamente no banco de dados.
 */
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$barbeiroId = $_GET['barbeiro_id'] ?? '';
$data = $_GET['data'] ?? '';

if ($barbeiroId === '' || $data === '') {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare('CALL sp_horarios_disponiveis(?, ?)');
    $stmt->execute([$barbeiroId, $data]);
    $linhas = $stmt->fetchAll();
    $stmt->closeCursor();

    $horarios = array_map(function ($row) {
        return substr($row['hora'], 0, 5);
    }, $linhas);

    echo json_encode($horarios);
} catch (PDOException $e) {
    echo json_encode([]);
}
