<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Altere para seu usuário
define('DB_PASS', ''); // Altere para sua senha
define('DB_NAME', 'casamento_laurinda_douglas');

// Criar conexão
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Criar banco de dados se não existir
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
$conn->query($sql);

// Selecionar banco de dados
$conn->select_db(DB_NAME);

// Criar tabela de confirmações se não existir
$sql = "CREATE TABLE IF NOT EXISTS confirmacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    presenca ENUM('sim', 'nao') NOT NULL,
    acompanhantes INT DEFAULT 0,
    mensagem TEXT,
    data_confirmacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
)";

$conn->query($sql);

// Função para limpar dados
function limpar_dados($dados) {
    global $conn;
    $dados = trim($dados);
    $dados = stripslashes($dados);
    $dados = htmlspecialchars($dados);
    return $conn->real_escape_string($dados);
}

// Definir charset
$conn->set_charset("utf8mb4");
?>
