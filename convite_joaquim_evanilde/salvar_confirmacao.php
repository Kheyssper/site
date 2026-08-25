<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido. Use POST.']);
    exit;
}

require_once 'config.php';

$nome = limpar_dados($_POST['nome'] ?? '');
$telefone = limpar_dados($_POST['telefone'] ?? '');
$email = limpar_dados($_POST['email'] ?? '');
$presenca = limpar_dados($_POST['presenca'] ?? '');
$acompanhantes = isset($_POST['acompanhantes']) ? intval($_POST['acompanhantes']) : 0;
$mensagem = limpar_dados($_POST['mensagem'] ?? '');
$ip_address = $_SERVER['REMOTE_ADDR'];

if (empty($nome)) {
    echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
    exit;
}

if (empty($telefone)) {
    $telefone = 'sem_telefone_' . time() . '_' . rand(1000, 9999);
}

$sql = "INSERT INTO confirmacoes (nome, telefone, email, presenca, acompanhantes, mensagem, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar query: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssssiss", $nome, $telefone, $email, $presenca, $acompanhantes, $mensagem, $ip_address);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Confirmação salva com sucesso!', 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar confirmação: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
