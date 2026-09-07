<?php
declare(strict_types=1);
define('RSVP_DATA_DIR', getenv('RSVP_DATA_DIR') ?: dirname(__DIR__) . '/private-rsvp');
define('RSVP_PASSWORD_HASH', getenv('RSVP_PASSWORD_HASH') ?: '');
function respostas(callable $operacao, bool $escrever = false): array {
    if (!is_dir(RSVP_DATA_DIR) && !mkdir(RSVP_DATA_DIR, 0700, true) && !is_dir(RSVP_DATA_DIR)) throw new RuntimeException('Falha na pasta de dados.');
    $lock = fopen(RSVP_DATA_DIR . '/confirmacoes.lock', 'c');
    if (!$lock) throw new RuntimeException('Falha ao abrir bloqueio.');
    try {
        if (!flock($lock, $escrever ? LOCK_EX : LOCK_SH)) throw new RuntimeException('Falha no bloqueio.');
        $path = RSVP_DATA_DIR . '/confirmacoes.json';
        $dados = is_file($path) ? json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : [];
        if (!is_array($dados) || !array_is_list($dados)) throw new RuntimeException('Dados inválidos.');
        $resultado = $operacao($dados);
        if ($escrever) {
            $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $tmp = tempnam(RSVP_DATA_DIR, 'rsvp-');
            try {
                if ($tmp === false || file_put_contents($tmp, $json) !== strlen($json) || !rename($tmp, $path)) throw new RuntimeException('Falha ao guardar.');
            } finally { if ($tmp !== false && is_file($tmp)) unlink($tmp); }
        }
        return $resultado;
    } finally { flock($lock, LOCK_UN); fclose($lock); }
}
function campo(array $entrada, string $nome, int $limite): string {
    $valor = $entrada[$nome] ?? '';
    if (!is_string($valor) || strlen($valor) > $limite) throw new InvalidArgumentException('Campo inválido: ' . $nome);
    return trim($valor);
}
