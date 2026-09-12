<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Indicadores principais via Stored Procedure.
    $stmtIndicadores = $pdo->prepare('CALL sp_dashboard()');
    $stmtIndicadores->execute();
    $indicadores = $stmtIndicadores->fetch() ?: [];
    $stmtIndicadores->closeCursor();

    // Dados brutos para o TypeScript processar com map/filter/reduce.
    // A procedure centraliza busca, filtro e paginação.
    $busca = trim((string) ($_GET['busca'] ?? ''));
    $status = trim((string) ($_GET['status'] ?? ''));
    $limite = min(max((int) ($_GET['limite'] ?? 1000), 1), 1000);
    $pagina = max((int) ($_GET['pagina'] ?? 1), 1);

    $stmtDados = $pdo->prepare(
        'CALL sp_dashboard_indicadores_paginado(?, ?, ?, ?)'
    );
    $stmtDados->execute([
        $busca,
        $status,
        $limite,
        $pagina
    ]);

    $agendamentos = $stmtDados->fetchAll() ?: [];
    $stmtDados->closeCursor();

    // Ranking inicial também é fornecido pelo banco para comparação/apoio.
    $rankingBanco = $pdo->query(
        'SELECT id, servico, total_realizados, faturamento_gerado
         FROM vw_ranking_servicos
         ORDER BY total_realizados DESC'
    )->fetchAll() ?: [];

    echo json_encode([
        'sucesso' => true,
        'indicadores' => [
            'total_clientes' => (int) ($indicadores['total_clientes'] ?? 0),
            'total_barbeiros' => (int) ($indicadores['total_barbeiros'] ?? 0),
            'total_servicos' => (int) ($indicadores['total_servicos'] ?? 0),
            'agendamentos_hoje' => (int) ($indicadores['agendamentos_hoje'] ?? 0),
            'faturamento_mes' => (float) ($indicadores['faturamento_mes'] ?? 0),
        ],
        'agendamentos' => array_map(
            static function (array $item): array {
                return [
                    'id' => (int) $item['id'],
                    'cliente' => (string) $item['cliente'],
                    'barbeiro' => (string) $item['barbeiro'],
                    'servico' => (string) $item['servico'],
                    'preco' => (float) $item['preco'],
                    'data_agendamento' => (string) $item['data_agendamento'],
                    'hora_agendamento' => (string) $item['hora_agendamento'],
                    'status' => (string) $item['status'],
                ];
            },
            $agendamentos
        ),
        'ranking_banco' => array_map(
            static function (array $item): array {
                return [
                    'id' => (int) $item['id'],
                    'servico' => (string) $item['servico'],
                    'total_realizados' => (int) $item['total_realizados'],
                    'faturamento_gerado' => (float) $item['faturamento_gerado'],
                ];
            },
            $rankingBanco
        ),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível carregar os dados da dashboard.',
        'detalhes' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
