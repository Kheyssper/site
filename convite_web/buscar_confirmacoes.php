<?php
// ============ DEBUG MODE ATIVADO ============
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Log de debug
$debug = [];
$debug[] = "=== INICIO DO DEBUG ===";
$debug[] = "Timestamp: " . date('Y-m-d H:i:s');

try {
    $debug[] = "1. Tentando incluir config.php";
    require_once 'config.php';
    $debug[] = "2. Config.php incluído com sucesso";
    
    // Verificar conexão
    if (!$conn) {
        throw new Exception("Conexão não estabelecida");
    }
    $debug[] = "3. Conexão MySQL OK";
    
    // Parâmetros
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
    $busca = isset($_GET['busca']) ? limpar_dados($_GET['busca']) : '';
    $debug[] = "4. Filtro: $filtro | Busca: $busca";
    
    // Query principal
    $sql = "SELECT * FROM confirmacoes WHERE 1=1";
    
    if ($filtro === 'confirmados') {
        $sql .= " AND presenca = 'sim'";
    } elseif ($filtro === 'nao-confirmados') {
        $sql .= " AND presenca = 'nao'";
    }
    
    if (!empty($busca)) {
        $sql .= " AND (nome LIKE '%$busca%' OR telefone LIKE '%$busca%' OR email LIKE '%$busca%')";
    }
    
    $sql .= " ORDER BY data_confirmacao DESC";
    
    $debug[] = "5. SQL: $sql";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Erro na query: " . $conn->error);
    }
    
    $debug[] = "6. Query executada. Linhas encontradas: " . $result->num_rows;
    
    $confirmacoes = [];
    while ($row = $result->fetch_assoc()) {
        $confirmacoes[] = $row;
        $debug[] = "   - Registro ID " . $row['id'] . ": " . $row['nome'];
    }
    
    $debug[] = "7. Total de confirmações carregadas: " . count($confirmacoes);
    
    // Estatísticas
    $sql_stats = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN presenca = 'sim' THEN 1 ELSE 0 END) as confirmados,
        SUM(CASE WHEN presenca = 'nao' THEN 1 ELSE 0 END) as nao_confirmados,
        SUM(CASE WHEN presenca = 'sim' THEN (1 + IFNULL(acompanhantes, 0)) ELSE 0 END) as total_pessoas
        FROM confirmacoes";
    
    $result_stats = $conn->query($sql_stats);
    
    if (!$result_stats) {
        throw new Exception("Erro nas estatísticas: " . $conn->error);
    }
    
    $stats = [
        'total' => 0,
        'confirmados' => 0,
        'nao_confirmados' => 0,
        'total_pessoas' => 0
    ];
    
    if ($row_stats = $result_stats->fetch_assoc()) {
        $stats = [
            'total' => intval($row_stats['total']),
            'confirmados' => intval($row_stats['confirmados']),
            'nao_confirmados' => intval($row_stats['nao_confirmados']),
            'total_pessoas' => intval($row_stats['total_pessoas'])
        ];
    }
    
    $debug[] = "8. Estatísticas: Total=" . $stats['total'] . " | Confirmados=" . $stats['confirmados'];
    $debug[] = "=== FIM DO DEBUG ===";
    
    $response = [
        'success' => true,
        'confirmacoes' => $confirmacoes,
        'estatisticas' => $stats,
        'debug' => $debug,
        'sql_executado' => $sql
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    $debug[] = "ERRO: " . $e->getMessage();
    $debug[] = "Stack trace: " . $e->getTraceAsString();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $debug
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

$conn->close();
?>
