<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/config.php';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST'); http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Use POST.']); exit;
    }
    $nome = campo($_POST, 'nome', 255);
    $telefone = campo($_POST, 'telefone', 50);
    $email = campo($_POST, 'email', 255);
    $presenca = campo($_POST, 'presenca', 3);
    $mensagem = campo($_POST, 'mensagem', 4000);
    $token = campo($_POST, 'submission_id', 64);
    if ($nome === '') throw new InvalidArgumentException('Preencham o nome.');
    if (!in_array($presenca, ['sim', 'nao'], true)) throw new InvalidArgumentException('Selecionem a presença.');
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('E-mail inválido.');
    $acompanhantes = filter_var($_POST['acompanhantes'] ?? '0', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 20]]);
    if ($acompanhantes === false) throw new InvalidArgumentException('Indiquem entre 0 e 20 acompanhantes.');
    if (!preg_match('/^[a-zA-Z0-9-]{16,64}$/', $token)) throw new InvalidArgumentException('Atualizem a página e tentem novamente.');
    echo json_encode(respostas(function (&$dados) use ($nome, $telefone, $email, $presenca, $mensagem, $acompanhantes, $token) {
        foreach ($dados as $item) if (($item['submission_id'] ?? '') === $token) return ['success' => true];
        $dados[] = ['id' => bin2hex(random_bytes(16)), 'submission_id' => $token, 'nome' => $nome, 'telefone' => $telefone, 'email' => $email, 'presenca' => $presenca, 'acompanhantes' => $presenca === 'sim' ? $acompanhantes : 0, 'mensagem' => $mensagem, 'data_confirmacao' => gmdate('c')];
        return ['success' => true];
    }, true));
} catch (InvalidArgumentException $e) {
    http_response_code(422); echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('RSVP: ' . $e->getMessage()); http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível guardar. Tentem novamente.'], JSON_UNESCAPED_UNICODE);
}
