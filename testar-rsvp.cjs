const {spawn,spawnSync}=require('child_process');
const fs=require('fs'), path=require('path'), os=require('os'), assert=require('assert/strict');
(async()=>{
 const dir=fs.mkdtempSync(path.join(os.tmpdir(),'rsvp-test-'));
 const hash=spawnSync('php',['-r',"echo password_hash('test-password', PASSWORD_DEFAULT);"],{encoding:'utf8'}).stdout;
 const server=spawn('php',['-d','session.save_path='+dir,'-S','127.0.0.1:18765','-t','convite_joaquim_evanilde'],{env:{...process.env,RSVP_DATA_DIR:dir,RSVP_PASSWORD_HASH:hash},stdio:'ignore'});
 const base='http://127.0.0.1:18765/';
 let cookie='';
 async function req(url, data, auth=false){return fetch(base+url,{method:data?'POST':'GET',body:data?new URLSearchParams(data):undefined,headers:auth?{Cookie:cookie}:{},redirect:'manual'});}
 try{
  for(let i=0;i<40;i++){try{await req('painel-noivos.php');break;}catch{await new Promise(r=>setTimeout(r,100));}}
  assert.equal((await req('salvar_confirmacao.php')).status,405);
  const input={nome:'Teste <script>',telefone:'+258 123',email:'test@example.com',presenca:'sim',acompanhantes:'5',mensagem:'Olá',submission_id:'1234567890abcdef'};
  assert.equal((await req('salvar_confirmacao.php',{...input,presenca:'x'})).status,422);
  assert.equal((await req('salvar_confirmacao.php',{...input,acompanhantes:'-1'})).status,422);
  assert.equal((await (await req('salvar_confirmacao.php',input)).json()).success,true);
  await req('salvar_confirmacao.php',input);
  await req('salvar_confirmacao.php',{...input,presenca:'nao',submission_id:'abcdef1234567890'});
  let records=JSON.parse(fs.readFileSync(path.join(dir,'confirmacoes.json')));
  assert.equal(records.length,2);assert.equal(records[1].acompanhantes,0);
  let r=await req('painel-noivos.php?exportar=1'); assert.ok(!(r.headers.get('content-type')||'').includes('csv'));
  cookie=r.headers.get('set-cookie').split(';')[0];
  let html=await r.text();const csrf=html.match(/name="csrf" value="([^"]+)"/)[1];
  assert.equal((await req('painel-noivos.php',{senha:'test-password',csrf:'invalid'},true)).status,403);
  r=await req('painel-noivos.php',{senha:'test-password',csrf},true);assert.equal(r.status,302);cookie=r.headers.get('set-cookie').split(';')[0];
  html=await (await req('painel-noivos.php',null,true)).text();assert.ok(html.includes('Teste &lt;script&gt;'));assert.ok(html.includes('>6</div>'));
  r=await req('painel-noivos.php?exportar=1',null,true);assert.ok(r.headers.get('content-type').includes('csv'));assert.ok((await r.text()).includes("'+258"));
  html=await (await req('painel-noivos.php?busca=inexistente',null,true)).text();assert.ok(html.includes('Nenhuma resposta encontrada'));
  assert.equal((await req('painel-noivos.php',{csrf,excluir:records[0].id},true)).status,302);
  assert.equal(JSON.parse(fs.readFileSync(path.join(dir,'confirmacoes.json'))).length,1);
  fs.writeFileSync(path.join(dir,'confirmacoes.json'),'INVALID');
  assert.equal((await req('salvar_confirmacao.php',input)).status,500);
  assert.equal(fs.readFileSync(path.join(dir,'confirmacoes.json'),'utf8'),'INVALID');
  assert.equal((await req('painel-noivos.php',{csrf,sair:'1'},true)).status,302);
  console.log('PASS: envio, validação, reenvio sem duplicação, ausência, login, CSRF, totais, pesquisa, CSV, exclusão, logout e preservação de JSON corrompido.');
 }finally{server.kill();}
})().catch(e=>{console.error(e);process.exitCode=1});
