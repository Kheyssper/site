<?php
session_start();

// IMPORTANTE: alterem esta senha antes de partilhar o painel.
$senha_correta = 'evanilde-joaquim-2026';

if (isset($_POST['senha'])) {
    if ($_POST['senha'] === $senha_correta) {
        $_SESSION['painel_autenticado'] = true;
    } else {
        $erro_login = 'Senha incorreta.';
    }
}

if (isset($_GET['sair'])) {
    session_destroy();
    header('Location: painel-noivos.php');
    exit;
}

$autenticado = isset($_SESSION['painel_autenticado']) && $_SESSION['painel_autenticado'] === true;

if ($autenticado) {
    require_once 'config.php';

    $filtro = $_GET['filtro'] ?? 'todos';
    $sql = "SELECT * FROM confirmacoes WHERE 1=1";
    if ($filtro === 'confirmados') $sql .= " AND presenca = 'sim'";
    if ($filtro === 'nao-confirmados') $sql .= " AND presenca = 'nao'";
    $sql .= " ORDER BY data_confirmacao DESC";

    $result = $conn->query($sql);
    $confirmacoes = [];
    while ($result && $row = $result->fetch_assoc()) $confirmacoes[] = $row;

    $stats_sql = "SELECT COUNT(*) as total,
        SUM(CASE WHEN presenca='sim' THEN 1 ELSE 0 END) as confirmados,
        SUM(CASE WHEN presenca='nao' THEN 1 ELSE 0 END) as nao_confirmados,
        SUM(CASE WHEN presenca='sim' THEN (1+IFNULL(acompanhantes,0)) ELSE 0 END) as total_pessoas
        FROM confirmacoes";
    $stats_result = $conn->query($stats_sql);
    $stats = $stats_result ? $stats_result->fetch_assoc() : ['total'=>0,'confirmados'=>0,'nao_confirmados'=>0,'total_pessoas'=>0];

    $conn->close();
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

  .filters{ margin-bottom:1.2rem; display:flex; gap:0.6rem; }
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
      <form method="POST">
        <input type="password" name="senha" placeholder="Senha" required autofocus>
        <button type="submit">Entrar</button>
      </form>
    </div>
  </div>
<?php else: ?>
  <div class="topbar">
    <span class="brand">Painel &middot; Evanilde &amp; Joaquim</span>
    <a href="?sair=1">Sair</a>
  </div>
  <main>
    <div class="stats">
      <div class="stat"><div class="num"><?= (int)$stats['total'] ?></div><div class="lbl">Respostas</div></div>
      <div class="stat"><div class="num"><?= (int)$stats['confirmados'] ?></div><div class="lbl">Confirmados</div></div>
      <div class="stat"><div class="num"><?= (int)$stats['nao_confirmados'] ?></div><div class="lbl">Não vão</div></div>
      <div class="stat"><div class="num"><?= (int)$stats['total_pessoas'] ?></div><div class="lbl">Pessoas no total</div></div>
    </div>

    <div class="filters">
      <a href="?filtro=todos" class="<?= $filtro==='todos'?'active':'' ?>">Todos</a>
      <a href="?filtro=confirmados" class="<?= $filtro==='confirmados'?'active':'' ?>">Confirmados</a>
      <a href="?filtro=nao-confirmados" class="<?= $filtro==='nao-confirmados'?'active':'' ?>">Não vão</a>
    </div>

    <?php if (empty($confirmacoes)): ?>
      <div class="empty">Ainda não há confirmações.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Nome</th><th>Telefone</th><th>Presença</th><th>Acomp.</th><th>Mensagem</th><th>Data</th></tr>
        </thead>
        <tbody>
          <?php foreach ($confirmacoes as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td><?= htmlspecialchars(strpos($c['telefone'], 'sem_telefone_') === 0 ? '—' : $c['telefone']) ?></td>
            <td><span class="tag <?= $c['presenca'] ?>"><?= $c['presenca'] === 'sim' ? 'Vai' : 'Não vai' ?></span></td>
            <td><?= (int)$c['acompanhantes'] ?></td>
            <td><?= htmlspecialchars($c['mensagem']) ?></td>
            <td><?= htmlspecialchars($c['data_confirmacao']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
<?php endif; ?>

</body>
</html>
