<?php
// Configurações da base de dados
// IMPORTANTE: alterem DB_USER e DB_PASS antes de colocar o site em produção.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'convite_evanilde_joaquim');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
$conn->query($sql);
$conn->select_db(DB_NAME);

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

function limpar_dados($dados) {
    global $conn;
    $dados = trim($dados);
    $dados = stripslashes($dados);
    $dados = htmlspecialchars($dados);
    return $conn->real_escape_string($dados);
}

$conn->set_charset("utf8mb4");
?>
