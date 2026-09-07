// Cloudflare Worker (ES module). Binding KV: CONFIRMACOES.
const encoder = new TextEncoder();
function campo(data, name, max) {
  const value = data.get(name) ?? '';
  if (typeof value !== 'string' || value.length > max) throw new Error(`Campo inválido: ${name}`);
  return value.trim();
}
async function autorizado(request, env) {
  const supplied = request.headers.get('Authorization') || '';
  const hashes = await Promise.all([supplied, `Bearer ${env.ADMIN_PASSWORD}`].map(v => crypto.subtle.digest('SHA-256', encoder.encode(v))));
  const a = new Uint8Array(hashes[0]), b = new Uint8Array(hashes[1]);
  let diff = 0;
  for (let i = 0; i < a.length; i++) diff |= a[i] ^ b[i];
  return diff === 0;
}
export default {
  async fetch(request, env) {
    const origin = request.headers.get('Origin');
    const allowed = (env.ALLOWED_ORIGINS || 'https://kheyssper.site').split(',').map(s => s.trim());
    const headers = {'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'no-store', 'Vary': 'Origin'};
    if (allowed.includes(origin)) headers['Access-Control-Allow-Origin'] = origin;
    const reply = (data, status = 200) => new Response(JSON.stringify(data), {status, headers});
    if (origin && !allowed.includes(origin)) return reply({success:false, message:'Origem não permitida.'}, 403);
    if (request.method === 'OPTIONS') return new Response(null, {status:204, headers:{...headers,
      'Access-Control-Allow-Methods':'GET, POST, DELETE, OPTIONS',
      'Access-Control-Allow-Headers':'Content-Type, Authorization', 'Access-Control-Max-Age':'86400'}});
    if (!env.CONFIRMACOES || !env.ADMIN_PASSWORD || env.ADMIN_PASSWORD.length < 16) return reply({success:false, message:'Serviço ainda não configurado.'}, 503);
    const url = new URL(request.url);
    try {
      if (url.pathname === '/confirmacoes' && request.method === 'POST') {
        if (Number(request.headers.get('Content-Length')) > 20000) return reply({success:false, message:'Pedido demasiado grande.'}, 413);
        const body = await request.text();
        if (encoder.encode(body).length > 20000) return reply({success:false, message:'Pedido demasiado grande.'}, 413);
        if (!(request.headers.get('Content-Type') || '').startsWith('application/x-www-form-urlencoded')) return reply({success:false, message:'Formato inválido.'}, 415);
        let record;
        try {
          const data = new URLSearchParams(body);
          const nome = campo(data, 'nome', 255), telefone = campo(data, 'telefone', 50), email = campo(data, 'email', 255);
          const presenca = campo(data, 'presenca', 3), mensagem = campo(data, 'mensagem', 4000);
          const id = campo(data, 'submission_id', 32), amount = campo(data, 'acompanhantes', 2) || '0';
          if (!nome) throw new Error('Preencham o nome.');
          if (!['sim','nao'].includes(presenca)) throw new Error('Selecionem a presença.');
          if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) throw new Error('E-mail inválido.');
          if (!/^[a-f0-9]{32}$/.test(id)) throw new Error('Atualizem a página e tentem novamente.');
          if (!/^\d+$/.test(amount) || Number(amount) > 20) throw new Error('Indiquem entre 0 e 20 acompanhantes.');
          record = {id, nome, telefone, email, presenca, acompanhantes:presenca === 'sim' ? Number(amount) : 0, mensagem, data_confirmacao:new Date().toISOString()};
        } catch (e) { return reply({success:false, message:e.message}, 422); }
        // Uma chave por envio: convidados diferentes nunca sobrescrevem a lista inteira.
        const key = `rsvp:${record.id}`;
        if (!await env.CONFIRMACOES.get(key)) await env.CONFIRMACOES.put(key, JSON.stringify(record));
        return reply({success:true});
      }
      if (url.pathname === '/confirmacoes' && request.method === 'GET' || /^\/confirmacoes\/[a-f0-9]{32}$/.test(url.pathname) && request.method === 'DELETE') {
        if (!await autorizado(request, env)) return reply({success:false, message:'Senha incorreta.'}, 401);
        if (request.method === 'DELETE') {
          await env.CONFIRMACOES.delete(`rsvp:${url.pathname.split('/').pop()}`);
          return reply({success:true});
        }
        const page = await env.CONFIRMACOES.list({prefix:'rsvp:', limit:100, ...(url.searchParams.get('cursor') ? {cursor:url.searchParams.get('cursor')} : {})});
        const records = [];
        // Pequenos lotes respeitam o limite de ligações simultâneas do Worker.
        for (let i = 0; i < page.keys.length; i += 5) {
          const batch = await Promise.all(page.keys.slice(i, i + 5).map(key => env.CONFIRMACOES.get(key.name, 'json')));
          records.push(...batch.filter(Boolean));
        }
        return reply({success:true, records, cursor:page.list_complete ? null : page.cursor});
      }
      return reply({success:false, message:'Rota ou método não permitido.'}, 404);
    } catch (e) {
      console.error('Falha no armazenamento RSVP', e.name);
      return reply({success:false, message:'Não foi possível concluir. Tentem novamente em instantes.'}, 503);
    }
  }
};
