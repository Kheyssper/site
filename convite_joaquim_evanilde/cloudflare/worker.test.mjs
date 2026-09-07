import { webcrypto } from 'node:crypto';
import assert from 'node:assert/strict';
import worker from './worker.mjs';
globalThis.crypto ??= webcrypto;
const store = new Map();
const env = {ADMIN_PASSWORD:'senha-teste-longa-123',ALLOWED_ORIGINS:'https://kheyssper.site',CONFIRMACOES:{
 async get(k,type){const v=store.get(k);return v ? type==='json'?JSON.parse(v):v : null;},
 async put(k,v){store.set(k,v);},async delete(k){store.delete(k);},
 async list({cursor}){const keys=[...store.keys()].sort();const offset=Number(cursor||0);return {keys:keys.slice(offset,offset+1).map(name=>({name})),list_complete:offset+1>=keys.length,cursor:String(offset+1)};}
}};
const input={nome:'Convidado <script>',telefone:'+258 123',email:'nome@example.com',presenca:'sim',acompanhantes:'4',mensagem:'Olá',submission_id:'a'.repeat(32)};
function call(method,path='/confirmacoes',body,auth=false,origin='https://kheyssper.site') {
 return worker.fetch(new Request('https://rsvp.example'+path,{method,headers:{Origin:origin,...(auth?{Authorization:'Bearer '+env.ADMIN_PASSWORD}:{})},...(body?{body:new URLSearchParams(body)}:{})}),env);
}
assert.equal((await call('OPTIONS')).status,204);
assert.equal((await call('POST','/confirmacoes',input,false,'https://outro.example')).status,403);
assert.equal((await call('GET')).status,401);
assert.equal((await call('DELETE','/confirmacoes/'+'a'.repeat(32))).status,401);
assert.equal((await call('POST','/confirmacoes',{...input,presenca:'talvez'})).status,422);
assert.equal((await call('POST','/confirmacoes',{...input,acompanhantes:'-1'})).status,422);
assert.equal((await call('POST','/confirmacoes',{...input,acompanhantes:'1.5'})).status,422);
assert.equal((await call('POST','/confirmacoes',{...input,nome:''})).status,422);
assert.equal((await call('POST','/confirmacoes',input)).status,200);
assert.equal((await call('POST','/confirmacoes',input)).status,200);assert.equal(store.size,1);
assert.equal((await call('POST','/confirmacoes',{...input,presenca:'nao',submission_id:'b'.repeat(32)})).status,200);
assert.equal(JSON.parse(store.get('rsvp:'+'b'.repeat(32))).acompanhantes,0);
let page=await (await call('GET','/confirmacoes',null,true)).json();assert.equal(page.records.length,1);assert.ok(page.cursor);assert.equal(page.records[0].nome,input.nome);
page=await (await call('GET','/confirmacoes?cursor='+page.cursor,null,true)).json();assert.equal(page.records.length,1);assert.equal(page.cursor,null);
assert.equal((await call('DELETE','/confirmacoes/'+'a'.repeat(32),null,true)).status,200);assert.equal(store.size,1);
env.CONFIRMACOES.put=async()=>{throw new Error('storage unavailable');};
assert.equal((await call('POST','/confirmacoes',input)).status,503);
env.ADMIN_PASSWORD='';assert.equal((await call('GET','/confirmacoes',null,true)).status,503);
console.log('PASS: CORS, autenticação, validação, gravação JSON, reenvio, ausência, paginação, exclusão e falhas.');
