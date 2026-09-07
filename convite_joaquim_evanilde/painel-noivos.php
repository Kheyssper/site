<?php
require_once __DIR__ . '/config.php';
session_name('joaquim_evanilde');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict', 'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
header('Cache-Control: no-store');
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$erro_login = RSVP_PASSWORD_HASH === '' ? 'Configure a senha do painel no servidor (RSVP_PASSWORD_HASH).' : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403); exit('Pedido inválido. Atualize a página.');
    }
    if (isset($_POST['senha']) && is_string($_POST['senha'])) {
        if (time() < ($_SESSION['login_after'] ?? 0)) {
            $erro_login = 'Aguarde alguns segundos antes de tentar novamente.';
        } elseif (RSVP_PASSWORD_HASH !== '' && password_verify($_POST['senha'], RSVP_PASSWORD_HASH)) {
            session_regenerate_id(true);
            $_SESSION['painel_autenticado'] = true;
            header('Location: painel-noivos.php'); exit;
        } else {
            $_SESSION['login_after'] = time() + 3;
            $erro_login = 'Senha incorreta.';
        }
    }
    if (isset($_POST['sair'])) {
        $_SESSION = []; session_destroy(); header('Location: painel-noivos.php'); exit;
    }
}
$autenticado = ($_SESSION['painel_autenticado'] ?? false) === true;
$erro_dados = '';
if ($autenticado) {
    $filtro = is_string($_GET['filtro'] ?? null) ? $_GET['filtro'] : 'todos';
    $busca = is_string($_GET['busca'] ?? null) ? trim($_GET['busca']) : '';
    $stats = ['total'=>0, 'confirmados'=>0, 'nao_confirmados'=>0, 'total_pessoas'=>0];
    $confirmacoes = [];
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
            $id = campo($_POST, 'excluir', 64);
            respostas(function (&$dados) use ($id) {
                $dados = array_values(array_filter($dados, fn($c) => $c['id'] !== $id)); return [];
            }, true);
            header('Location: painel-noivos.php'); exit;
        }
        $todos = respostas(fn($dados) => $dados);
        foreach ($todos as $c) {
            $stats['total']++;
            $stats[$c['presenca'] === 'sim' ? 'confirmados' : 'nao_confirmados']++;
            if ($c['presenca'] === 'sim') $stats['total_pessoas'] += 1 + $c['acompanhantes'];
        }
        $confirmacoes = array_values(array_filter($todos, fn($c) =>
            ($filtro !== 'confirmados' || $c['presenca'] === 'sim') &&
            ($filtro !== 'nao-confirmados' || $c['presenca'] === 'nao') &&
            ($busca === '' || stripos($c['nome'] . ' ' . $c['telefone'] . ' ' . $c['email'], $busca) !== false)
        ));
        usort($confirmacoes, fn($a, $b) => strcmp($b['data_confirmacao'], $a['data_confirmacao']));
        if (isset($_GET['exportar'])) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="confirmacoes.csv"');
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Nome', 'Telefone', 'Email', 'Presença', 'Acompanhantes', 'Mensagem', 'Data'], ';');
            foreach ($confirmacoes as $c) {
                $row = [$c['nome'], $c['telefone'], $c['email'], $c['presenca'], $c['acompanhantes'], $c['mensagem'], $c['data_confirmacao']];
                $row = array_map(fn($v) => preg_match('/^[\s]*[=+@-]/u', (string)$v) ? "'" . $v : $v, $row);
                fputcsv($out, $row, ';');
            }
            fclose($out); exit;
        }
    } catch (Throwable $e) {
        error_log('Painel RSVP: ' . $e->getMessage());
        $erro_dados = 'Não foi possível carregar ou alterar as respostas. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel &middot; Evanilde &amp; Joaquim</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#142238; --sapphire:#1F3D6B; --sapphire-2:#0E1F38; --gold:#C7A05C;
    --clay:#B26A45; --parchment:#FAF4E9; --parchment-2:#F1E7D5; --mist:#B9C6D3;
  }
  *{ margin:0; padding:0; box-sizing:border-box; }
  body{ font-family:'Jost',sans-serif; background:var(--parchment); color:var(--ink); font-weight:300; }
  h1,h2{ font-family:'Cormorant Garamond',serif; font-weight:500; }

  .login-wrap{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
  .login-box{ background:#fff; max-width:380px; width:100%; padding:2.6rem 2.2rem; border-top:3px solid var(--gold); box-shadow:0 16px 44px rgba(20,34,56,0.1); text-align:center; }
  .login-box h1{ font-size:1.7rem; color:var(--sapphire-2); margin-bottom:0.4rem; }
  .login-box p{ font-size:0.85rem; color:#8B7A5E; letter-spacing:0.05em; margin-bottom:1.8rem; }
  .login-box input{ width:100%; padding:0.85rem 1rem; border:1px solid #D8CFC0; background:var(--parchment); font-family:'Jost',sans-serif; margin-bottom:1rem; }
  .login-box button{ width:100%; padding:0.85rem; background:var(--sapphire); color:#fff; border:none; letter-spacing:0.06em; text-transform:uppercase; font-size:0.85rem; cursor:pointer; }
  .login-box button:hover{ background:var(--sapphire-2); }
  .erro{ color:#B23A3A; font-size:0.85rem; margin-bottom:1rem; }

  .topbar{ background:var(--sapphire-2); color:#fff; padding:1.4rem clamp(1.2rem,4vw,3rem); display:flex; justify-content:space-between; align-items:center; }
  .topbar .brand{ font-family:'Cormorant Garamond',serif; font-size:1.3rem; }
  .topbar a{ color:var(--gold); text-decoration:none; font-size:0.85rem; letter-spacing:0.05em; }

  main{ max-width:1000px; margin:0 auto; padding:clamp(2rem,5vw,3rem) clamp(1.2rem,4vw,2rem); }

  .stats{ display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:2.4rem; }
  .stat{ background:#fff; padding:1.4rem 1.2rem; text-align:center; border:1px solid rgba(20,34,56,0.08); }
  .stat .num{ font-family:'Cormorant Garamond',serif; font-size:2.2rem; color:var(--sapphire); }
  .stat .lbl{ font-size:0.7rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--clay); margin-top:0.3rem; }

  .filters{ flex-wrap:wrap; margin-bottom:1.2rem; display:flex; gap:0.6rem; }
  .filters a{ padding:0.5rem 1rem; border:1px solid #D8CFC0; text-decoration:none; color:var(--ink); font-size:0.82rem; }
  .filters a.active{ background:var(--sapphire); color:#fff; border-color:var(--sapphire); }

  table{ width:100%; border-collapse:collapse; background:#fff; box-shadow:0 10px 30px rgba(20,34,56,0.06); }
  th,td{ text-align:left; padding:0.8rem 1rem; border-bottom:1px solid #EFE7D8; font-size:0.88rem; }
  th{ background:var(--parchment-2); font-weight:500; font-size:0.72rem; letter-spacing:0.08em; text-transform:uppercase; color:var(--clay); }
  tr:last-child td{ border-bottom:none; }
  .tag{ font-size:0.72rem; padding:0.2rem 0.6rem; letter-spacing:0.04em; }
  .tag.sim{ background:rgba(80,140,90,0.12); color:#3E7A4E; }
  .tag.nao{ background:rgba(178,58,58,0.1); color:#B23A3A; }
  .empty{ text-align:center; padding:3rem 1rem; color:#8B7A5E; }

  @media (max-width:700px){
    .stats{ grid-template-columns:repeat(2,1fr); }
    table{ display:block; overflow-x:auto; }
  }
</style>
</head>
<body>

<?php if (!$autenticado): ?>
  <div class="login-wrap">
    <div class="login-box">
      <h1>Painel dos Noivos</h1>
      <p>Evanilde &amp; Joaquim &middot; acesso restrito</p>
      <?php if (!empty($erro_login)): ?><div class="erro"><?= htmlspecialchars($erro_login) ?></div><?php endif; ?>
      <form method="POST"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
        <input type="password" name="senha" placeholder="Senha" required autofocus>
        <button type="submit">Entrar</button>
      </form>
    </div>
  </div>
<?php else: ?>
  <div class="topbar">
    <span class="brand">Painel &middot; Evanilde &amp; Joaquim</span>
    <form method="POST"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>"><button name="sair" value="1">Sair</button></form>
  </div>
  <main>
    <div class="stats">
      <div class="stat"><div class="num"><?= (int)$stats['total'] ?></div><div class="lbl">Respostas</div></div>
      <div class="stat"><div class="num"><?= (int)$stats['confirmados'] ?></div><div class="lbl">Confirmados</div></div>
      <div class="stat"><div class="num"><?= (int)$stats['nao_confirmados'] ?></div><div class="lbl">Não vão</div></div>
      <div class="stat"><div class="num"><?= (int)$stats['total_pessoas'] ?></div><div class="lbl">Pessoas no total</div></div>
    </div>

    <?php if ($erro_dados): ?><p class="erro" role="alert"><?= htmlspecialchars($erro_dados) ?></p><?php endif; ?>
    <form method="GET" style="margin-bottom:1rem;display:flex;gap:.5rem;flex-wrap:wrap">
      <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtro) ?>">
      <input name="busca" aria-label="Pesquisar nome, telefone ou email" placeholder="Nome, telefone ou email" value="<?= htmlspecialchars($busca) ?>">
      <button>Pesquisar</button><button name="exportar" value="1">Exportar CSV</button>
      <a href="painel-noivos.php">Atualizar / limpar pesquisa</a>
    </form>
    <div class="filters">
      <a href="?filtro=todos" class="<?= $filtro==='todos'?'active':'' ?>">Todos</a>
      <a href="?filtro=confirmados" class="<?= $filtro==='confirmados'?'active':'' ?>">Confirmados</a>
      <a href="?filtro=nao-confirmados" class="<?= $filtro==='nao-confirmados'?'active':'' ?>">Não vão</a>
    </div>

    <?php if (empty($confirmacoes)): ?>
      <div class="empty">Nenhuma resposta encontrada.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Presença</th><th>Acomp.</th><th>Mensagem</th><th>Data (UTC)</th><th>Ações</th></tr>
        </thead>
        <tbody>
          <?php foreach ($confirmacoes as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td><?= htmlspecialchars(strpos($c['telefone'], 'sem_telefone_') === 0 ? '—' : $c['telefone']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><span class="tag <?= $c['presenca'] === 'sim' ? 'sim' : 'nao' ?>"><?= $c['presenca'] === 'sim' ? 'Vai' : 'Não vai' ?></span></td>
            <td><?= (int)$c['acompanhantes'] ?></td>
            <td><?= htmlspecialchars($c['mensagem']) ?></td>
            <td><?= htmlspecialchars($c['data_confirmacao']) ?></td>
            <td><form method="POST" onsubmit="return confirm('Excluir esta resposta? Esta ação não pode ser desfeita.');">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <button name="excluir" value="<?= htmlspecialchars($c['id']) ?>">Excluir</button>
            </form></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
<?php endif; ?>

</body>
</html>
