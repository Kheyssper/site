'use strict';
const el = id => document.getElementById(id);
let senha = '', records = [], busy = false;
function base() {
  if (!window.RSVP_API_URL) throw new Error('Configure a URL do serviço em rsvp-config.js.');
  return window.RSVP_API_URL.replace(/\/$/, '');
}
function sair() {
  senha = ''; records = []; el('rows').replaceChildren(); el('stats').replaceChildren();
  el('painel').hidden = true; el('login').hidden = false; el('senha').value = '';
}
async function api(path, method = 'GET') {
  const response = await fetch(base() + path, {method, headers:{Authorization:`Bearer ${senha}`}, cache:'no-store'});
  const data = await response.json();
  if (response.status === 401) sair();
  if (!response.ok || !data.success) throw new Error(data.message || 'Falha ao carregar respostas.');
  return data;
}
async function executar(action) {
  if (busy) return;
  busy = true; el('status').textContent = 'A processar…';
  document.querySelectorAll('button').forEach(b => b.disabled = true);
  try { await action(); el('status').textContent = ''; }
  catch (e) { el('status').textContent = e instanceof TypeError || e instanceof SyntaxError ? 'Não foi possível contactar o serviço. Tente novamente.' : e.message; }
  finally { busy = false; document.querySelectorAll('button').forEach(b => b.disabled = false); }
}
function filtradas() {
  const busca = el('busca').value.trim().toLocaleLowerCase('pt');
  return records.filter(r => (el('filtro').value === 'todos' || r.presenca === el('filtro').value) && `${r.nome} ${r.telefone} ${r.email}`.toLocaleLowerCase('pt').includes(busca));
}
function render() {
  const yes = records.filter(r => r.presenca === 'sim');
  el('stats').replaceChildren();
  for (const [label, value] of [['Respostas',records.length],['Vão',yes.length],['Não vão',records.length-yes.length],['Pessoas',yes.reduce((n,r)=>n+1+r.acompanhantes,0)]]) {
    const card=document.createElement('div'), num=document.createElement('strong');
    card.className='stat'; num.textContent=value; card.append(num,document.createTextNode(label)); el('stats').append(card);
  }
  const selected=filtradas(); el('empty').hidden=selected.length!==0; el('rows').replaceChildren();
  for (const r of selected) {
    const tr=document.createElement('tr');
    for (const value of [r.nome,r.telefone || '—',r.email || '—',r.presenca==='sim'?'Vai':'Não vai',r.acompanhantes,r.mensagem,new Date(r.data_confirmacao).toLocaleString('pt')]) {
      const td=document.createElement('td');td.textContent=value;tr.append(td);
    }
    const td=document.createElement('td'), button=document.createElement('button');button.textContent='Excluir';
    button.onclick=()=>{
      if (!confirm(`Excluir a resposta de ${r.nome}? Esta ação não pode ser desfeita.`)) return;
      executar(async()=>{await api(`/confirmacoes/${r.id}`,'DELETE');records=records.filter(item=>item.id!==r.id);render();});
    };
    td.append(button);tr.append(td);el('rows').append(tr);
  }
}
async function carregar() {
  const loaded=[]; let cursor=null;
  do {
    const page=await api('/confirmacoes'+(cursor?'?cursor='+encodeURIComponent(cursor):''));
    loaded.push(...page.records);cursor=page.cursor;
  } while(cursor);
  records=[...new Map(loaded.map(r=>[r.id,r])).values()].sort((a,b)=>b.data_confirmacao.localeCompare(a.data_confirmacao));
  render();el('login').hidden=true;el('painel').hidden=false;el('senha').value='';
}
el('login').onsubmit=event=>{event.preventDefault();executar(async()=>{senha=el('senha').value;await carregar();});};
el('atualizar').onclick=()=>executar(carregar);
el('sair').onclick=()=>{sair();el('status').textContent='';};
el('busca').oninput=render;el('filtro').onchange=render;
function download(content,type,name) {
  const url=URL.createObjectURL(new Blob([content],{type})), a=document.createElement('a');
  a.href=url;a.download=name;a.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
}
el('json').onclick=()=>download(JSON.stringify(filtradas(),null,2),'application/json','confirmacoes.json');
el('csv').onclick=()=>{
  const quote=value=>{let s=String(value??'');if (/^\s*[=+@-]/.test(s)) s="'"+s;return '"'+s.replaceAll('"','""')+'"';};
  const rows=[['Nome','Telefone','Email','Presença','Acompanhantes','Mensagem','Data'],...filtradas().map(r=>[r.nome,r.telefone,r.email,r.presenca,r.acompanhantes,r.mensagem,r.data_confirmacao])];
  download('\uFEFF'+rows.map(row=>row.map(quote).join(';')).join('\r\n'),'text/csv;charset=utf-8','confirmacoes.csv');
};
