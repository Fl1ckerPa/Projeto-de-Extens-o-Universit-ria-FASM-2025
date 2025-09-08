// ---------- UTIL ----------
const $ = (sel, root=document) => root.querySelector(sel);
const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

// ---------- DATA MOCK ----------
const seed = [
  { id: crypto.randomUUID(), titulo: "Balconista", cliente: "horti-fruti", analista: "Marina", prioridade: "Médio", dataLimite: "2025-09-15", status: "Triando", candidatos: 12, limiarAtencao: 10, limiarUrgente: 5, obs: "" },
  { id: crypto.randomUUID(), titulo: "Vendedor", cliente: "distribuidora chave inglesa", analista: "Rafael", prioridade: "Alto", dataLimite: "2025-08-30", status: "Entrevistando", candidatos: 8, limiarAtencao: 7, limiarUrgente: 3, obs: "" },
  { id: crypto.randomUUID(), titulo: "Vendedora interna", cliente: "afrodite cosmeticos", analista: "Marina", prioridade: "Baixo", dataLimite: "2025-10-05", status: "Abrindo", candidatos: 3, limiarAtencao: 14, limiarUrgente: 5, obs: "" },
  { id: crypto.randomUUID(), titulo: "Designer", cliente: "Studio pallet", analista: "Carla", prioridade: "Médio", dataLimite: "2025-08-25", status: "Finalizando", candidatos: 5, limiarAtencao: 7, limiarUrgente: 2, obs: "" }
];

const STORAGE_KEY = "vagas-dashboard-v1";

function loadData() {
  const raw = localStorage.getItem(STORAGE_KEY);
  return raw ? JSON.parse(raw) : seed;
}
function saveData(data) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  new bootstrap.Toast($('#toastOk')).show();
}

function diasAte(dataISO) {
  const hoje = new Date();
  const dt = new Date(dataISO + "T00:00:00");
  const diff = Math.ceil((dt - hoje) / (1000*60*60*24));
  return diff;
}

function categoriaUrgencia(vaga) {
  const faltam = diasAte(vaga.dataLimite);
  if (faltam <= vaga.limiarUrgente) return "Urgente";
  if (faltam <= vaga.limiarAtencao) return "Atenção";
  return "Em dia";
}

// ---------- STATE ----------
let data = loadData();
let filtro = { cliente: "", analista: "", texto: "", kpi: "Todos", sortKey: "dataLimite", sortDir: "asc" };

// ---------- RENDER ----------

function renderFiltros() {
  const clientes = [...new Set(data.map(v => v.cliente))].sort();
  const analistas = [...new Set(data.map(v => v.analista))].sort();
  const selCliente = $('#filtroCliente');
  const selAnalista = $('#filtroAnalista');

  const currentCliente = filtro.cliente;
  const currentAnalista = filtro.analista;

  selCliente.innerHTML = '<option value=\"\">Todos</option>' + clientes.map(c=>`<option>${c}</option>`).join('');
  selAnalista.innerHTML = '<option value=\"\">Todos</option>' + analistas.map(a=>`<option>${a}</option>`).join('');

  if (!clientes.includes(currentCliente)) filtro.cliente = '';
  if (!analistas.includes(currentAnalista)) filtro.analista = '';

  selCliente.value = filtro.cliente;
  selAnalista.value = filtro.analista;
}


function renderKPIs(lista) {
  const cont = { "Urgente":0, "Atenção":0, "Em dia":0 };
  lista.forEach(v => cont[categoriaUrgencia(v)]++);
  $('#kpiUrgente').textContent = cont["Urgente"];
  $('#kpiAtencao').textContent = cont["Atenção"];
  $('#kpiEmDia').textContent = cont["Em dia"];
  $('#kpiTodos').textContent = lista.length;
  $$('.kpi').forEach(card => {
    card.classList.toggle('active', card.dataset.kpi === filtro.kpi);
  });
}

function renderTabela() {
  let lista = data.slice();

  // filtro por KPI
  if (filtro.kpi !== "Todos") {
    lista = lista.filter(v => categoriaUrgencia(v) === filtro.kpi);
  }
  // filtros
  if (filtro.cliente) lista = lista.filter(v => v.cliente === filtro.cliente);
  if (filtro.analista) lista = lista.filter(v => v.analista === filtro.analista);
  if (filtro.texto) {
    const t = filtro.texto.toLowerCase();
    lista = lista.filter(v =>
      v.titulo.toLowerCase().includes(t) ||
      v.cliente.toLowerCase().includes(t) ||
      v.analista.toLowerCase().includes(t));
  }

  // ordenar
  lista.sort((a,b) => {
    const key = filtro.sortKey;
    let va = a[key], vb = b[key];
    if (key === 'dataLimite') { va = new Date(va); vb = new Date(vb); }
    if (key === 'candidatos') { va = +va; vb = +vb; }
    if (va < vb) return filtro.sortDir === 'asc' ? -1 : 1;
    if (va > vb) return filtro.sortDir === 'asc' ? 1 : -1;
    return 0;
  });

  // KPIs
  renderKPIs(data);

  const tbody = $('#tbodyVagas');
  tbody.innerHTML = lista.map(v => {
    const cat = categoriaUrgencia(v);
    const badgeClass = cat === 'Urgente' ? 'text-bg-danger' : (cat === 'Atenção' ? 'text-bg-warning' : 'text-bg-success');
    return `
      <tr>
        <td class="fw-medium">${v.titulo}</td>
        <td>${v.cliente}</td>
        <td>${v.analista}</td>
        <td><span class="badge bg-prioridade-${v.prioridade.toLowerCase()}">${v.prioridade}</span></td>
        <td>${new Date(v.dataLimite).toLocaleDateString()}</td>
        <td><span class="badge ${badgeClass}">${cat}</span></td>
        <td>${v.candidatos}</td>
        <td class="text-nowrap">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="editar" data-id="${v.id}"><i class="bi bi-pencil-square"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="excluir" data-id="${v.id}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `;
  }).join('');

  // bind ações
  $$('#tbodyVagas [data-action="editar"]').forEach(btn => btn.addEventListener('click', onEditar));
  $$('#tbodyVagas [data-action="excluir"]').forEach(btn => btn.addEventListener('click', onExcluir));
}

// ---------- HANDLERS ----------
function onEditar(e) {
  const id = e.currentTarget.dataset.id;
  const v = data.find(x => x.id === id);
  const form = $('#formEditarVaga');
  form.id.value = v.id;
  form.titulo.value = v.titulo;
  form.cliente.value = v.cliente;
  form.analista.value = v.analista;
  form.prioridade.value = v.prioridade;
  form.dataLimite.value = v.dataLimite;
  form.status.value = v.status;
  form.candidatos.value = v.candidatos;
  form.limiarAtencao.value = v.limiarAtencao;
  form.limiarUrgente.value = v.limiarUrgente;
  form.obs.value = v.obs || "";
  new bootstrap.Modal($('#modalEditarVaga')).show();
}

function onExcluir(e) {
  const id = e.currentTarget.dataset.id;
  if (confirm('Confirma excluir esta vaga?')) {
    data = data.filter(v => v.id !== id);
    saveData(data);
    renderFiltros(); renderTabela();
  }
}

// ---------- INIT ----------
document.addEventListener('DOMContentLoaded', () => {
  renderFiltros();
  renderTabela();

  // ordenar por cabeçalho
  $$('#tabelaVagas thead th[data-sort]').forEach(th => {
    th.style.cursor = 'pointer';
    th.addEventListener('click', () => {
      const key = th.getAttribute('data-sort');
      if (filtro.sortKey === key) {
        filtro.sortDir = filtro.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        filtro.sortKey = key;
        filtro.sortDir = 'asc';
      }
      renderTabela();
    });
  });

  // KPI click
  $$('.kpi').forEach(card => {
    card.addEventListener('click', () => {
      filtro.kpi = card.dataset.kpi;
      renderTabela();
    });
  });

  // filtros
  $('#filtroCliente').addEventListener('change', (e)=>{filtro.cliente = e.target.value; renderTabela();});
  $('#filtroAnalista').addEventListener('change', (e)=>{filtro.analista = e.target.value; renderTabela();});
  $('#pesquisa').addEventListener('input', (e)=>{filtro.texto = e.target.value; renderTabela();});
  $('#btnLimparFiltros').addEventListener('click', ()=>{
    filtro = { ...filtro, cliente: "", analista: "", texto: "", kpi: "Todos" };
    $('#filtroCliente').value = '';
    $('#filtroAnalista').value = '';
    $('#pesquisa').value = '';
    renderTabela();
  });

  // submit nova vaga
  $('#formVaga').addEventListener('submit', (e)=>{
    e.preventDefault();
    const f = e.target;
    const nova = {
      id: crypto.randomUUID(),
      titulo: f.titulo.value.trim(),
      cliente: f.cliente.value.trim(),
      analista: f.analista.value.trim(),
      prioridade: f.prioridade.value,
      dataLimite: f.dataLimite.value,
      status: f.status.value,
      candidatos: Number(f.candidatos.value || 0),
      limiarAtencao: Number(f.limiarAtencao.value || 7),
      limiarUrgente: Number(f.limiarUrgente.value || 3),
      obs: f.obs.value.trim()
    };
    data.push(nova);
    saveData(data);
    renderFiltros();
    renderTabela();
    bootstrap.Modal.getInstance($('#modalNovaVaga')).hide();
    f.reset();
  });

  // submit editar
  $('#formEditarVaga').addEventListener('submit', (e)=>{
    e.preventDefault();
    const f = e.target;
    const idx = data.findIndex(v => v.id === f.id.value);
    if (idx >= 0) {
      data[idx] = {
        ...data[idx],
        titulo: f.titulo.value.trim(),
        cliente: f.cliente.value.trim(),
        analista: f.analista.value.trim(),
        prioridade: f.prioridade.value,
        dataLimite: f.dataLimite.value,
        status: f.status.value,
        candidatos: Number(f.candidatos.value || 0),
        limiarAtencao: Number(f.limiarAtencao.value || 7),
        limiarUrgente: Number(f.limiarUrgente.value || 3),
        obs: f.obs.value.trim()
      };
      saveData(data);
      renderFiltros();
      renderTabela();
      bootstrap.Modal.getInstance($('#modalEditarVaga')).hide();
    }
  });
});
