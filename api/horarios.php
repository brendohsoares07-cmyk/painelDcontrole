<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$barbeiroId = (int) ($_GET['barbeiro_id'] ?? 0);
$data = (string) ($_GET['data'] ?? '');
if (!$barbeiroId || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) { echo json_encode([]); exit; }
try {
    $stmt = $pdo->prepare('CALL sp_horarios_disponiveis(?, ?)');
    $stmt->execute([$barbeiroId, $data]);
    $linhas = $stmt->fetchAll();
    $stmt->closeCursor();
    echo json_encode(array_map(static fn(array $row): string => substr((string)$row['hora'], 0, 5), $linhas), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) { echo json_encode([]); }
