<?php
// Senha de acesso (altere isso!)
$senha_correta = 'laurinda2025'; // ALTERE ESTA SENHA!

session_start();

// Verificar login
if (!isset($_SESSION['logado'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['senha'])) {
        if ($_POST['senha'] === $senha_correta) {
            $_SESSION['logado'] = true;
        } else {
            $erro = 'Senha incorreta!';
        }
    }
    
    if (!isset($_SESSION['logado'])) {
        ?>
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login - Painel dos Noivos</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@300;400;600&display=swap');
                
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Montserrat', sans-serif;
                    background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }

                .login-container {
                    background: rgba(26, 26, 26, 0.9);
                    border: 2px solid #d4af37;
                    border-radius: 20px;
                    padding: 50px 40px;
                    max-width: 400px;
                    width: 100%;
                    text-align: center;
                    box-shadow: 0 20px 60px rgba(212, 175, 55, 0.3);
                }

                .login-icon {
                    font-size: 4rem;
                    color: #d4af37;
                    margin-bottom: 20px;
                }

                .login-title {
                    font-family: 'Cormorant Garamond', serif;
                    font-size: 2rem;
                    color: #d4af37;
                    margin-bottom: 10px;
                }

                .login-subtitle {
                    color: #999;
                    margin-bottom: 30px;
                    font-size: 0.9rem;
                }

                .form-group {
                    margin-bottom: 20px;
                    text-align: left;
                }

                .form-label {
                    display: block;
                    color: #d4af37;
                    margin-bottom: 10px;
                    font-size: 0.9rem;
                    font-weight: 600;
                }

                .form-input {
                    width: 100%;
                    padding: 15px 20px;
                    border: 2px solid #d4af37;
                    border-radius: 10px;
                    background: rgba(212, 175, 55, 0.1);
                    color: #fff;
                    font-family: 'Montserrat', sans-serif;
                    font-size: 1rem;
                }

                .form-input:focus {
                    outline: none;
                    background: rgba(212, 175, 55, 0.2);
                }

                .submit-btn {
                    background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
                    color: #1a1a1a;
                    border: none;
                    padding: 15px 40px;
                    font-size: 1rem;
                    font-weight: 600;
                    border-radius: 50px;
                    cursor: pointer;
                    width: 100%;
                    transition: all 0.3s ease;
                    margin-top: 10px;
                }

                .submit-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.5);
                }

                .erro {
                    background: rgba(244, 67, 54, 0.2);
                    border: 1px solid #f44336;
                    color: #f44336;
                    padding: 10px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                    font-size: 0.9rem;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <i class="fas fa-lock login-icon"></i>
                <h1 class="login-title">Painel dos Noivos</h1>
                <p class="login-subtitle">Acesso restrito • Laurinda & Douglas</p>
                
                <?php if (isset($erro)): ?>
                    <div class="erro">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i> Senha de Acesso
                        </label>
                        <input type="password" name="senha" class="form-input" placeholder="Digite a senha" required autofocus>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: painel-noivos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel dos Noivos - Confirmações</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@300;400;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            color: #fff;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            padding: 40px 20px;
            border-bottom: 2px solid #d4af37;
            margin-bottom: 40px;
            position: relative;
        }

        .header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.5rem;
            color: #d4af37;
            margin-bottom: 10px;
        }

        .header p {
            color: #999;
            font-size: 1rem;
        }

        .logout-btn {
            position: absolute;
            top: 40px;
            right: 20px;
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid #f44336;
            color: #f44336;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: #f44336;
            color: #fff;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
            border: 2px solid #d4af37;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
        }

        .stat-card i {
            font-size: 2.5rem;
            color: #d4af37;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #d4af37;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-btn {
            background: rgba(212, 175, 55, 0.2);
            border: 2px solid #d4af37;
            color: #d4af37;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #d4af37;
            color: #1a1a1a;
            transform: translateY(-2px);
        }

        .search-box {
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #d4af37;
            border-radius: 25px;
            background: rgba(212, 175, 55, 0.1);
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
        }

        .search-box input::placeholder {
            color: #999;
        }

        .search-box input:focus {
            outline: none;
            background: rgba(212, 175, 55, 0.2);
        }

        .confirmacoes-list {
            background: rgba(26, 26, 26, 0.8);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .confirmacao-item {
            background: rgba(212, 175, 55, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .confirmacao-item:hover {
            background: rgba(212, 175, 55, 0.1);
            transform: translateX(5px);
        }

        .confirmacao-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .confirmacao-nome {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            color: #d4af37;
            font-weight: 600;
        }

        .confirmacao-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .confirmacao-status.confirmado {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid #4caf50;
        }

        .confirmacao-status.nao-confirmado {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid #f44336;
        }

        .confirmacao-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .detail-item i {
            color: #d4af37;
            font-size: 1.1rem;
            margin-top: 3px;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.75rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .detail-value {
            color: #fff;
            font-size: 0.95rem;
        }

        .confirmacao-mensagem {
            background: rgba(212, 175, 55, 0.1);
            border-left: 3px solid #d4af37;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .mensagem-label {
            font-size: 0.75rem;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .mensagem-text {
            color: #ccc;
            font-style: italic;
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #d4af37;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state p {
            color: #999;
            font-size: 1.1rem;
        }

        .actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 1000;
        }

        .action-btn {
            background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
            color: #1a1a1a;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(212, 175, 55, 0.6);
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #d4af37;
        }

        .loading i {
            font-size: 3rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .logout-btn {
                position: static;
                display: block;
                margin: 20px auto 0;
                width: fit-content;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .confirmacao-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .confirmacao-details {
                grid-template-columns: 1fr;
            }

            .actions {
                bottom: 20px;
                right: 20px;
            }

            .action-btn {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heart"></i> Painel dos Noivos</h1>
            <p>Confirmações de Presença - Laurinda & Douglas</p>
            <a href="?logout=1" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>

        <div class="stats" id="stats">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-value" id="totalConfirmacoes">0</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <div class="stat-value" id="totalConfirmados">0</div>
                <div class="stat-label">Confirmados</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-times-circle"></i>
                <div class="stat-value" id="totalNaoConfirmados">0</div>
                <div class="stat-label">Não Confirmados</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-friends"></i>
                <div class="stat-value" id="totalPessoas">0</div>
                <div class="stat-label">Total de Pessoas</div>
            </div>
        </div>

        <div class="filters">
            <button class="filter-btn active" onclick="filtrar('todos')">
                <i class="fas fa-list"></i> Todos
            </button>
            <button class="filter-btn" onclick="filtrar('confirmados')">
                <i class="fas fa-check"></i> Confirmados
            </button>
            <button class="filter-btn" onclick="filtrar('nao-confirmados')">
                <i class="fas fa-times"></i> Não Confirmados
            </button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Buscar por nome..." oninput="buscar()">
            </div>
        </div>

        <div class="confirmacoes-list" id="confirmacoesLista">
            <div class="loading">
                <i class="fas fa-spinner"></i>
                <p>Carregando confirmações...</p>
            </div>
        </div>
    </div>

    <div class="actions">
        <button class="action-btn" onclick="exportarCSV()">
            <i class="fas fa-download"></i>
            Exportar CSV
        </button>
        <button class="action-btn" onclick="atualizar()">
            <i class="fas fa-sync"></i>
            Atualizar
        </button>
    </div>

    <script>
        let confirmacoes = [];
        let filtroAtual = 'todos';

        // Carregar confirmações
       // Carregar confirmações COM DEBUG
function carregarConfirmacoes() {
    console.log('🔍 === INICIANDO CARREGAMENTO ===');
    
    const busca = document.getElementById('searchInput').value;
    const url = `buscar_confirmacoes.php?filtro=${filtroAtual}&busca=${encodeURIComponent(busca)}`;
    
    console.log('📡 URL:', url);
    console.log('🔎 Filtro atual:', filtroAtual);
    console.log('🔎 Busca:', busca);
    
    fetch(url)
        .then(response => {
            console.log('📥 Resposta recebida:', response);
            console.log('   Status:', response.status);
            console.log('   OK?:', response.ok);
            console.log('   Headers:', response.headers);
            
            if (!response.ok) {
                throw new Error('HTTP Error: ' + response.status);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('📄 TEXTO COMPLETO RECEBIDO:');
            console.log(text);
            console.log('📄 Tamanho:', text.length, 'caracteres');
            
            try {
                const data = JSON.parse(text);
                console.log('✅ JSON PARSEADO COM SUCESSO:');
                console.log(data);
                
                if (data.debug) {
                    console.log('🐛 DEBUG DO SERVIDOR:');
                    data.debug.forEach(line => console.log('   ' + line));
                }
                
                if (data.success) {
                    console.log('✅ Sucesso! Confirmações:', data.confirmacoes);
                    console.log('📊 Estatísticas:', data.estatisticas);
                    console.log('📝 SQL:', data.sql_executado);
                    
                    confirmacoes = data.confirmacoes;
                    console.log('💾 Confirmações armazenadas:', confirmacoes.length);
                    
                    atualizarEstatisticas(data.estatisticas);
                    renderizarConfirmacoes(confirmacoes);
                } else {
                    console.error('❌ Erro do servidor:', data.message);
                    throw new Error(data.message || 'Erro desconhecido');
                }
            } catch (parseError) {
                console.error('❌ ERRO AO FAZER PARSE DO JSON:');
                console.error(parseError);
                console.error('Texto que causou erro:', text.substring(0, 500));
                throw parseError;
            }
        })
        .catch(error => {
            console.error('❌ ERRO COMPLETO:', error);
            console.error('Stack:', error.stack);
            
            document.getElementById('confirmacoesLista').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Erro ao carregar confirmações</p>
                    <p style="color: #f44336; font-size: 0.9rem; margin-top: 10px;">${error.message}</p>
                    <button onclick="carregarConfirmacoes()" style="margin-top: 20px; padding: 10px 20px; background: #d4af37; color: #1a1a1a; border: none; border-radius: 25px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-sync"></i> Tentar Novamente
                    </button>
                    <details style="margin-top: 20px; text-align: left; background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px;">
                        <summary style="cursor: pointer; color: #d4af37; margin-bottom: 10px;">🔍 Ver detalhes técnicos</summary>
                        <pre style="color: #fff; font-size: 0.8rem; overflow-x: auto;">${error.stack || 'Sem stack trace'}</pre>
                    </details>
                </div>
            `;
        });
    
    console.log('🔍 === FIM DO CARREGAMENTO ===');
}

        // Atualizar estatísticas
        function atualizarEstatisticas(stats) {
            document.getElementById('totalConfirmacoes').textContent = stats.total || 0;
            document.getElementById('totalConfirmados').textContent = stats.confirmados || 0;
            document.getElementById('totalNaoConfirmados').textContent = stats.nao_confirmados || 0;
            document.getElementById('totalPessoas').textContent = stats.total_pessoas || 0;
        }

       // Renderizar confirmações COM DEBUG
function renderizarConfirmacoes(lista) {
    console.log('🎨 === RENDERIZANDO CONFIRMAÇÕES ===');
    console.log('📋 Lista recebida:', lista);
    console.log('📊 Quantidade:', lista ? lista.length : 'undefined/null');
    
    const container = document.getElementById('confirmacoesLista');
    console.log('📦 Container encontrado:', container ? 'SIM' : 'NÃO');

    if (!lista || lista.length === 0) {
        console.log('⚠️ Lista vazia ou undefined');
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Nenhuma confirmação encontrada</p>
                <p style="color: #999; font-size: 0.9rem; margin-top: 10px;">
                    ${lista === null ? 'Lista é NULL' : lista === undefined ? 'Lista é UNDEFINED' : 'Lista está vazia (length = 0)'}
                </p>
            </div>
        `;
        return;
    }

    console.log('✅ Renderizando', lista.length, 'confirmações...');
    
    let html = '';
    lista.forEach((confirmacao, index) => {
        console.log(`   ${index + 1}. Renderizando:`, confirmacao.nome);
        
        // Garantir que os valores existem
        const nome = confirmacao.nome || 'Sem nome';
        const telefone = confirmacao.telefone || '';
        const email = confirmacao.email || '';
        const presenca = confirmacao.presenca || '';
        const acompanhantes = parseInt(confirmacao.acompanhantes) || 0;
        const mensagem = confirmacao.mensagem || '';
        const data = confirmacao.data_confirmacao || '';

        const statusClass = presenca === 'sim' ? 'confirmado' : 'nao-confirmado';
        const statusText = presenca === 'sim' ? 'Confirmado' : presenca === 'nao' ? 'Não Confirmado' : 'Não informado';
        const statusIcon = presenca === 'sim' ? 'fa-check-circle' : 'fa-times-circle';

        const acompanhantesText = acompanhantes === 0 ? 'Sozinho' : 
                                 acompanhantes === 1 ? '1 acompanhante' : 
                                 `${acompanhantes} acompanhantes`;

        // Formatar telefone (remover prefixo sem_telefone)
        const telefoneFormatado = telefone.startsWith('sem_telefone') ? 'Não informado' : telefone;

        html += `
            <div class="confirmacao-item">
                <div class="confirmacao-header">
                    <div class="confirmacao-nome">${nome}</div>
                    ${presenca ? `
                    <div class="confirmacao-status ${statusClass}">
                        <i class="fas ${statusIcon}"></i>
                        ${statusText}
                    </div>
                    ` : ''}
                </div>
                <div class="confirmacao-details">
                    ${telefone && !telefone.startsWith('sem_telefone') ? `
                    <div class="detail-item">
                        <i class="fas fa-phone"></i>
                        <div class="detail-content">
                            <div class="detail-label">Telefone</div>
                            <div class="detail-value">${telefoneFormatado}</div>
                        </div>
                    </div>
                    ` : ''}
                    ${email ? `
                    <div class="detail-item">
                        <i class="fas fa-envelope"></i>
                        <div class="detail-content">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">${email}</div>
                        </div>
                    </div>
                    ` : ''}
                    ${presenca === 'sim' ? `
                    <div class="detail-item">
                        <i class="fas fa-user-friends"></i>
                        <div class="detail-content">
                            <div class="detail-label">Acompanhantes</div>
                            <div class="detail-value">${acompanhantesText}</div>
                        </div>
                    </div>
                    ` : ''}
                    ${data ? `
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <div class="detail-content">
                            <div class="detail-label">Data</div>
                            <div class="detail-value">${new Date(data).toLocaleString('pt-BR')}</div>
                        </div>
                    </div>
                    ` : ''}
                </div>
                ${mensagem ? `
                <div class="confirmacao-mensagem">
                    <div class="mensagem-label">💌 Mensagem</div>
                    <div class="mensagem-text">${mensagem}</div>
                </div>
                ` : ''}
            </div>
        `;
    });

    console.log('📝 HTML gerado, tamanho:', html.length, 'caracteres');
    container.innerHTML = html;
    console.log('✅ HTML inserido no DOM');
    console.log('🎨 === FIM DA RENDERIZAÇÃO ===');
}

        // Filtrar
        function filtrar(tipo) {
            filtroAtual = tipo;
            
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.filter-btn').classList.add('active');

            carregarConfirmacoes();
        }

        // Buscar
        function buscar() {
            carregarConfirmacoes();
        }

        // Atualizar
        function atualizar() {
            carregarConfirmacoes();
        }

        // Exportar CSV
        function exportarCSV() {
            if (confirmacoes.length === 0) {
                alert('Não há confirmações para exportar');
                return;
            }

            let csv = 'Nome,Telefone,Email,Presença,Acompanhantes,Mensagem,Data\n';
            
            confirmacoes.forEach(c => {
                const linha = [
                    `"${c.nome}"`,
                    `"${c.telefone || ''}"`,
                    `"${c.email || ''}"`,
                    c.presenca === 'sim' ? 'Confirmado' : 'Não Confirmado',
                    c.acompanhantes || 0,
                    `"${(c.mensagem || '').replace(/"/g, '""')}"`,
                    `"${new Date(c.data_confirmacao).toLocaleString('pt-BR')}"`
                ].join(',');
                csv += linha + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            link.setAttribute('href', url);
            link.setAttribute('download', `confirmacoes_casamento_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Auto-atualizar a cada 30 segundos
        setInterval(carregarConfirmacoes, 30000);

        // Carregar ao iniciar
        carregarConfirmacoes();
    </script>
</body>
</html>