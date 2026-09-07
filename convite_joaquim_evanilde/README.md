# Confirmações gratuitas — GitHub Pages + Cloudflare

O convite e o painel HTML continuam no GitHub Pages. Um Cloudflare Worker recebe as respostas e guarda cada uma como JSON privado no Workers KV. Não é preciso PHP nem MySQL para esta versão. O JSON não é gravado no repositório público.

## Ativar pelo navegador

1. Crie uma conta gratuita em https://dash.cloudflare.com/sign-up e mantenha o plano **Workers Free**. Não precisa transferir o domínio nem mudar o alojamento do site.
2. Em **Workers & Pages → Create application → Start with Hello World**, crie um Worker chamado `convite-joaquim-evanilde` e publique-o.
3. Abra **Edit code**, substitua o código pelo conteúdo de `cloudflare/worker.mjs` e publique.
4. Em **Workers KV**, crie um namespace chamado `convite-confirmacoes`.
5. No Worker, em **Bindings → Add binding → KV namespace**, use o nome de variável **CONFIRMACOES** e selecione o namespace criado.
6. Em **Settings → Variables and Secrets**, adicione **ADMIN_PASSWORD**, do tipo **Secret**, com uma senha exclusiva de pelo menos 16 caracteres. Esta é a senha que o casal vai usar. Nunca a coloque nos ficheiros do GitHub.
7. Adicione a variável de texto **ALLOWED_ORIGINS** com `https://kheyssper.site`. Se também usa outro endereço, acrescente a origem exata separada por vírgula, por exemplo `https://kheyssper.site,https://kheyssper.github.io`. Não inclua caminhos nem barras finais.
8. Publique as alterações e copie a URL do Worker, semelhante a `https://convite-joaquim-evanilde.sua-conta.workers.dev`.
9. Em `rsvp-config.js`, coloque essa URL entre as aspas de `window.RSVP_API_URL = '';` e publique os ficheiros do site pelo processo habitual do GitHub.
10. Abra o convite e envie uma resposta de teste. Abra `https://kheyssper.site/convite_joaquim_evanilde/painel-noivos.html`, entre com a senha e confira a resposta. Pode depois excluí-la.

Enquanto a URL estiver vazia, o formulário avisa que a confirmação está indisponível e não apresenta sucesso falso. Os ficheiros PHP da versão anterior não participam neste fluxo; o painel a usar é **painel-noivos.html**.

## Gestão e armazenamento

- Pesquisa por nome, telefone ou email; filtros de presença; totais com acompanhantes; exclusão com confirmação.
- Exportação dos resultados filtrados em CSV ou JSON para cópia de segurança.
- A senha fica apenas na memória da página durante o acesso; recarregar a página exige novo login. Sair limpa os dados apresentados.
- Cada envio tem uma chave aleatória própria. Repetir o mesmo envio pendente usa a mesma chave; um novo envio após recarregar pode criar duplicados, que o casal pode excluir.
- KV tem consistência eventual: uma resposta ou exclusão pode demorar cerca de 60 segundos ou mais a aparecer noutra região. Atualize mais tarde. Não há atualização automática para poupar operações.
- Quem não vai fica com zero acompanhantes. O máximo é 20 acompanhantes por resposta.
- Os totais representam respostas recebidas. Para saber quem ainda não respondeu seria necessária uma lista prévia de convidados.

## Gratuidade e limites

À data de 7 de setembro de 2026, Workers Free inclui 100.000 pedidos/dia. KV Free inclui 100.000 leituras/dia e 1.000 operações/dia de cada tipo: escrita, exclusão e listagem. Uma atualização do painel lê cada resposta; a listagem é paginada de 100 em 100. Ao esgotar a quota gratuita, operações podem falhar até à reposição. Mantenha o plano Free.

Documentação oficial:
- https://developers.cloudflare.com/workers/platform/pricing/
- https://developers.cloudflare.com/kv/platform/pricing/
- https://developers.cloudflare.com/kv/get-started/
- https://developers.cloudflare.com/workers/configuration/secrets/
- https://developers.cloudflare.com/kv/concepts/how-kv-works/

## Testes locais

Na raiz do projeto: `node convite_joaquim_evanilde/cloudflare/worker.test.mjs`.

O teste usa uma simulação de KV e verifica validação, armazenamento JSON, autenticação, CORS, paginação, reenvio, exclusão e falhas. Não contacta a Cloudflare. A verificação real no navegador deve ser feita após a configuração acima; a propagação e as quotas da Cloudflare não são simuladas.
