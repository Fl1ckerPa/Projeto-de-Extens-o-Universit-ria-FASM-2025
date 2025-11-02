// ---------- UTIL ----------
const $ = (sel, root=document) => root.querySelector(sel);
const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

// ---------- DATA MOCK ----------
const seedVagas = [
  { 
    id: crypto.randomUUID(), 
    titulo: "Desenvolvedor Full Stack", 
    categoria: "tecnologia", 
    salario: "R$ 5.000,00",
    tipoContrato: "CLT",
    dataPublicacao: "2025-01-15", 
    dataLimite: "2025-02-15", 
    status: "Aberta", 
    candidatos: 15,
    descricao: "Desenvolvedor para trabalhar com React, Node.js e banco de dados PostgreSQL. Experiência em desenvolvimento web moderno.",
    requisitos: "Conhecimento em JavaScript, React, Node.js, PostgreSQL. Experiência mínima de 2 anos.",
    beneficios: "Vale refeição, plano de saúde, ambiente de trabalho flexível."
  },
  { 
    id: crypto.randomUUID(), 
    titulo: "Vendedor Externo", 
    categoria: "comercio", 
    salario: "R$ 2.500,00 + Comissão",
    tipoContrato: "CLT",
    dataPublicacao: "2025-01-10", 
    dataLimite: "2025-02-10", 
    status: "Aberta", 
    candidatos: 8,
    descricao: "Vendedor para atuar na região de Muriaé e cidades vizinhas. Experiência em vendas B2B.",
    requisitos: "CNH categoria B, experiência em vendas, boa comunicação.",
    beneficios: "Comissão sobre vendas, vale combustível, celular corporativo."
  },
  { 
    id: crypto.randomUUID(), 
    titulo: "Assistente Administrativo", 
    categoria: "administracao", 
    salario: "R$ 2.000,00",
    tipoContrato: "CLT",
    dataPublicacao: "2025-01-05", 
    dataLimite: "2025-01-25", 
    status: "Pausada", 
    candidatos: 12,
    descricao: "Assistente para área administrativa com foco em atendimento ao cliente e organização de documentos.",
    requisitos: "Ensino médio completo, conhecimento em informática básica, boa organização.",
    beneficios: "Vale refeição, plano de saúde, ambiente climatizado."
  },
  { 
    id: crypto.randomUUID(), 
    titulo: "Enfermeiro", 
    categoria: "saude", 
    salario: "R$ 4.500,00",
    tipoContrato: "CLT",
    dataPublicacao: "2024-12-20", 
    dataLimite: "2025-01-20", 
    status: "Fechada", 
    candidatos: 25,
    descricao: "Enfermeiro para clínica médica com experiência em atendimento ambulatorial.",
    requisitos: "CRF ativo, experiência em clínica médica, disponibilidade para plantões.",
    beneficios: "Plano de saúde, vale refeição, adicional noturno."
  }
];

const seedCandidatos = [
  {
    id: crypto.randomUUID(),
    vagaId: seedVagas[0].id,
    nome: "João Silva",
    email: "joao.silva@email.com",
    telefone: "(32) 99999-9999",
    experiencia: "3 anos de experiência em desenvolvimento web",
    formacao: "Ciência da Computação - UFV",
    curriculo: {
      dadosPessoais: {
        nome: "João Silva",
        email: "joao.silva@email.com",
        telefone: "(32) 99999-9999",
        endereco: "Rua das Flores, 123 - Muriaé/MG",
        nascimento: "1995-03-15"
      },
      formacao: {
        escolaridade: "Graduação - Completo",
        curso: "Ciência da Computação",
        instituicao: "Universidade Federal de Viçosa"
      },
      experiencia: [
        {
          empresa: "Tech Solutions",
          cargo: "Desenvolvedor Frontend",
          periodo: "2022-2024",
          atividades: "Desenvolvimento de interfaces web com React e Vue.js"
        }
      ],
      sobre: "Desenvolvedor apaixonado por tecnologia, com foco em soluções web modernas e eficientes."
    },
    status: "Pendente",
    dataCandidatura: "2025-01-16"
  },
  {
    id: crypto.randomUUID(),
    vagaId: seedVagas[0].id,
    nome: "Maria Santos",
    email: "maria.santos@email.com",
    telefone: "(32) 88888-8888",
    experiencia: "5 anos de experiência em desenvolvimento full stack",
    formacao: "Sistemas de Informação - PUC",
    curriculo: {
      dadosPessoais: {
        nome: "Maria Santos",
        email: "maria.santos@email.com",
        telefone: "(32) 88888-8888",
        endereco: "Av. Central, 456 - Muriaé/MG",
        nascimento: "1990-07-22"
      },
      formacao: {
        escolaridade: "Graduação - Completo",
        curso: "Sistemas de Informação",
        instituicao: "PUC Minas"
      },
      experiencia: [
        {
          empresa: "Digital Corp",
          cargo: "Desenvolvedora Full Stack",
          periodo: "2020-2024",
          atividades: "Desenvolvimento completo de aplicações web e mobile"
        }
      ],
      sobre: "Profissional dedicada com ampla experiência em desenvolvimento de software e liderança de equipes."
    },
    status: "Pendente",
    dataCandidatura: "2025-01-17"
  }
];

const STORAGE_KEY_VAGAS = "vagas-empresa-v1";
const STORAGE_KEY_CANDIDATOS = "candidatos-empresa-v1";

function loadVagas() {
  const raw = localStorage.getItem(STORAGE_KEY_VAGAS);
  return raw ? JSON.parse(raw) : seedVagas;
}

function saveVagas(data) {
  localStorage.setItem(STORAGE_KEY_VAGAS, JSON.stringify(data));
  showToast();
}

function loadCandidatos() {
  const raw = localStorage.getItem(STORAGE_KEY_CANDIDATOS);
  return raw ? JSON.parse(raw) : seedCandidatos;
}

function saveCandidatos(data) {
  localStorage.setItem(STORAGE_KEY_CANDIDATOS, JSON.stringify(data));
  showToast();
}

function showToast() {
  new bootstrap.Toast($('#toastOk')).show();
}

// ---------- STATE ----------
let vagas = loadVagas();
let candidatos = loadCandidatos();
let filtro = { 
  status: "", 
  categoria: "", 
  texto: "", 
  kpi: "Todos", 
  sortKey: "dataPublicacao", 
  sortDir: "desc" 
};

// ---------- RENDER ----------

function renderFiltros() {
  const categorias = [...new Set(vagas.map(v => v.categoria))].sort();
  const selCategoria = $('#filtroCategoria');
  
  const currentCategoria = filtro.categoria;
  
  selCategoria.innerHTML = '<option value="">Todas</option>' + 
    categorias.map(c => `<option value="${c}">${c.charAt(0).toUpperCase() + c.slice(1)}</option>`).join('');
  
  if (!categorias.includes(currentCategoria)) filtro.categoria = '';
  
  selCategoria.value = filtro.categoria;
}

function renderKPIs(lista) {
  const cont = { "Aberta": 0, "Pausada": 0, "Fechada": 0 };
  lista.forEach(v => cont[v.status]++);
  
  $('#kpiAbertas').textContent = cont["Aberta"];
  $('#kpiPausadas').textContent = cont["Pausada"];
  $('#kpiFechadas').textContent = cont["Fechada"];
  $('#kpiTodos').textContent = lista.length;
  
  $$('.kpi').forEach(card => {
    card.classList.toggle('active', card.dataset.kpi === filtro.kpi);
  });
}

function renderTabela() {
  let lista = vagas.slice();

  // filtro por KPI
  if (filtro.kpi !== "Todos") {
    lista = lista.filter(v => v.status === filtro.kpi);
  }
  
  // filtros
  if (filtro.status) lista = lista.filter(v => v.status === filtro.status);
  if (filtro.categoria) lista = lista.filter(v => v.categoria === filtro.categoria);
  if (filtro.texto) {
    const t = filtro.texto.toLowerCase();
    lista = lista.filter(v =>
      v.titulo.toLowerCase().includes(t) ||
      v.descricao.toLowerCase().includes(t));
  }

  // ordenar
  lista.sort((a,b) => {
    const key = filtro.sortKey;
    let va = a[key], vb = b[key];
    if (key === 'dataPublicacao' || key === 'dataLimite') { 
      va = new Date(va); 
      vb = new Date(vb); 
    }
    if (key === 'candidatos') { 
      va = +va; 
      vb = +vb; 
    }
    if (va < vb) return filtro.sortDir === 'asc' ? -1 : 1;
    if (va > vb) return filtro.sortDir === 'asc' ? 1 : -1;
    return 0;
  });

  // KPIs
  renderKPIs(vagas);

  const tbody = $('#tbodyVagas');
  tbody.innerHTML = lista.map(v => {
    const candidatosVaga = candidatos.filter(c => c.vagaId === v.id).length;
    const statusClass = v.status.toLowerCase();
    const categoriaClass = v.categoria;
    
    return `
      <tr>
        <td class="fw-medium">${v.titulo}</td>
        <td><span class="badge bg-categoria-${categoriaClass}">${categoriaClass.charAt(0).toUpperCase() + categoriaClass.slice(1)}</span></td>
        <td>
          <span class="badge bg-primary">${candidatosVaga}</span>
          ${candidatosVaga > 0 ? `<button class="btn btn-sm btn-outline-success ms-1" data-action="ver-candidatos" data-id="${v.id}">
            <i class="bi bi-people"></i>
          </button>` : ''}
        </td>
        <td>${new Date(v.dataPublicacao).toLocaleDateString()}</td>
        <td>${new Date(v.dataLimite).toLocaleDateString()}</td>
        <td><span class="badge bg-status-${statusClass}">${v.status}</span></td>
        <td class="text-nowrap">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="editar" data-id="${v.id}">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button class="btn btn-sm btn-outline-warning me-1" data-action="toggle-status" data-id="${v.id}">
            <i class="bi bi-${v.status === 'Aberta' ? 'pause' : 'play'}-circle"></i>
          </button>
          <button class="btn btn-sm btn-outline-danger" data-action="excluir" data-id="${v.id}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');

  // bind ações
  $$('#tbodyVagas [data-action="editar"]').forEach(btn => btn.addEventListener('click', onEditar));
  $$('#tbodyVagas [data-action="toggle-status"]').forEach(btn => btn.addEventListener('click', onToggleStatus));
  $$('#tbodyVagas [data-action="excluir"]').forEach(btn => btn.addEventListener('click', onExcluir));
  $$('#tbodyVagas [data-action="ver-candidatos"]').forEach(btn => btn.addEventListener('click', onVerCandidatos));
}

// ---------- HANDLERS ----------

function onEditar(e) {
  const id = e.currentTarget.dataset.id;
  const v = vagas.find(x => x.id === id);
  const form = $('#formEditarVaga');
  
  form.id.value = v.id;
  form.titulo.value = v.titulo;
  form.categoria.value = v.categoria;
  form.salario.value = v.salario;
  form.tipoContrato.value = v.tipoContrato;
  form.dataLimite.value = v.dataLimite;
  form.status.value = v.status;
  form.descricao.value = v.descricao;
  form.requisitos.value = v.requisitos;
  form.beneficios.value = v.beneficios;
  
  new bootstrap.Modal($('#modalEditarVaga')).show();
}

function onToggleStatus(e) {
  const id = e.currentTarget.dataset.id;
  const vaga = vagas.find(v => v.id === id);
  
  if (vaga.status === 'Aberta') {
    vaga.status = 'Pausada';
  } else if (vaga.status === 'Pausada') {
    vaga.status = 'Aberta';
  }
  
  saveVagas(vagas);
  renderTabela();
}

function onExcluir(e) {
  const id = e.currentTarget.dataset.id;
  const vaga = vagas.find(v => v.id === id);
  
  if (confirm(`Confirma excluir a vaga "${vaga.titulo}"?`)) {
    vagas = vagas.filter(v => v.id !== id);
    candidatos = candidatos.filter(c => c.vagaId !== id);
    saveVagas(vagas);
    saveCandidatos(candidatos);
    renderFiltros();
    renderTabela();
  }
}

function onVerCandidatos(e) {
  const vagaId = e.currentTarget.dataset.id;
  const vaga = vagas.find(v => v.id === vagaId);
  const candidatosVaga = candidatos.filter(c => c.vagaId === vagaId);
  
  $('#candidatosInfo').textContent = `Vaga: ${vaga.titulo} (${candidatosVaga.length} candidatos)`;
  
  const listaCandidatos = $('#listaCandidatos');
  // Armazenar o ID da vaga para uso posterior usando dataset
  listaCandidatos.dataset.vagaId = vagaId;
  
  listaCandidatos.innerHTML = candidatosVaga.map(c => `
    <div class="candidato-card">
      <div class="candidato-header">
        <div class="candidato-avatar">
          <i class="bi bi-person"></i>
        </div>
        <div class="candidato-info">
          <h6>${c.nome}</h6>
          <small class="text-muted">${c.email} • ${c.telefone}</small>
          <br>
          <small class="text-muted">Candidatou-se em: ${new Date(c.dataCandidatura).toLocaleDateString()}</small>
        </div>
      </div>
      <div class="candidato-details">
        <p><strong>Experiência:</strong> ${c.experiencia}</p>
        <p><strong>Formação:</strong> ${c.formacao}</p>
        <div class="candidato-actions">
          <button class="btn btn-sm btn-primary" data-action="ver-curriculo" data-id="${c.id}">
            <i class="bi bi-file-person me-1"></i> Ver Currículo
          </button>
          <button class="btn btn-sm btn-success" data-action="aprovar" data-id="${c.id}">
            <i class="bi bi-check-circle me-1"></i> Aprovar
          </button>
          <button class="btn btn-sm btn-danger" data-action="reprovar" data-id="${c.id}">
            <i class="bi bi-x-circle me-1"></i> Reprovar
          </button>
        </div>
      </div>
    </div>
  `).join('');
  
  // Bind eventos dos candidatos
  $$('#listaCandidatos [data-action="ver-curriculo"]').forEach(btn => {
    btn.addEventListener('click', (e) => onVerCurriculo(e, candidatos));
  });
  $$('#listaCandidatos [data-action="aprovar"]').forEach(btn => {
    btn.addEventListener('click', (e) => onAprovarCandidato(e));
  });
  $$('#listaCandidatos [data-action="reprovar"]').forEach(btn => {
    btn.addEventListener('click', (e) => onReprovarCandidato(e));
  });
  
  new bootstrap.Modal($('#modalCandidatos')).show();
}

function onVerCurriculo(e, candidatosList) {
  const candidatoId = e.currentTarget.dataset.id;
  const candidato = candidatosList.find(c => c.id === candidatoId);
  
  $('#curriculoInfo').textContent = `Candidato: ${candidato.nome}`;
  
  const curriculo = candidato.curriculo;
  const detalhes = $('#detalhesCurriculo');
  
  detalhes.innerHTML = `
    <div class="curriculo-section">
      <h6><i class="bi bi-person me-2"></i>Dados Pessoais</h6>
      <p><strong>Nome:</strong> ${curriculo.dadosPessoais.nome}</p>
      <p><strong>E-mail:</strong> ${curriculo.dadosPessoais.email}</p>
      <p><strong>Telefone:</strong> ${curriculo.dadosPessoais.telefone}</p>
      <p><strong>Endereço:</strong> ${curriculo.dadosPessoais.endereco}</p>
      <p><strong>Data de Nascimento:</strong> ${new Date(curriculo.dadosPessoais.nascimento).toLocaleDateString()}</p>
    </div>
    
    <div class="curriculo-section">
      <h6><i class="bi bi-mortarboard me-2"></i>Formação</h6>
      <p><strong>Escolaridade:</strong> ${curriculo.formacao.escolaridade}</p>
      <p><strong>Curso:</strong> ${curriculo.formacao.curso}</p>
      <p><strong>Instituição:</strong> ${curriculo.formacao.instituicao}</p>
    </div>
    
    <div class="curriculo-section">
      <h6><i class="bi bi-briefcase me-2"></i>Experiência Profissional</h6>
      ${curriculo.experiencia.map(exp => `
        <div class="mb-3">
          <p><strong>Empresa:</strong> ${exp.empresa}</p>
          <p><strong>Cargo:</strong> ${exp.cargo}</p>
          <p><strong>Período:</strong> ${exp.periodo}</p>
          <p><strong>Atividades:</strong> ${exp.atividades}</p>
        </div>
      `).join('')}
    </div>
    
    <div class="curriculo-section">
      <h6><i class="bi bi-info-circle me-2"></i>Sobre</h6>
      <p>${curriculo.sobre}</p>
    </div>
  `;
  
  // Armazenar ID do candidato para ações
  $('#btnAprovarCandidato').data('candidato-id', candidatoId);
  $('#btnReprovarCandidato').data('candidato-id', candidatoId);
  
  new bootstrap.Modal($('#modalCurriculo')).show();
}

function onAprovarCandidato(e) {
  const candidatoId = e.currentTarget.dataset.id;
  const candidato = candidatos.find(c => c.id === candidatoId);
  candidato.status = 'Aprovado';
  saveCandidatos(candidatos);
  showToast();
}

function onReprovarCandidato(e) {
  const candidatoId = e.currentTarget.dataset.id;
  
  if (confirm('Tem certeza que deseja reprovar e remover este candidato da lista?')) {
    // Remover candidato da lista
    candidatos = candidatos.filter(c => c.id !== candidatoId);
    saveCandidatos(candidatos);
    
    // Fechar modal de currículo se estiver aberto
    const modalCurriculo = bootstrap.Modal.getInstance($('#modalCurriculo'));
    if (modalCurriculo) {
      modalCurriculo.hide();
    }
    
    // Atualizar lista de candidatos
    const modalCandidatos = bootstrap.Modal.getInstance($('#modalCandidatos'));
    if (modalCandidatos) {
      // Recarregar a lista de candidatos
      const vagaId = $('#listaCandidatos').dataset.vagaId;
      if (vagaId) {
        const candidatosVaga = candidatos.filter(c => c.vagaId === vagaId);
        const vaga = vagas.find(v => v.id === vagaId);
        
        $('#candidatosInfo').textContent = `Vaga: ${vaga.titulo} (${candidatosVaga.length} candidatos)`;
        
        const listaCandidatos = $('#listaCandidatos');
        listaCandidatos.innerHTML = candidatosVaga.map(c => `
          <div class="candidato-card">
            <div class="candidato-header">
              <div class="candidato-avatar">
                <i class="bi bi-person"></i>
              </div>
              <div class="candidato-info">
                <h6>${c.nome}</h6>
                <small class="text-muted">${c.email} • ${c.telefone}</small>
                <br>
                <small class="text-muted">Candidatou-se em: ${new Date(c.dataCandidatura).toLocaleDateString()}</small>
              </div>
            </div>
            <div class="candidato-details">
              <p><strong>Experiência:</strong> ${c.experiencia}</p>
              <p><strong>Formação:</strong> ${c.formacao}</p>
              <div class="candidato-actions">
                <button class="btn btn-sm btn-primary" data-action="ver-curriculo" data-id="${c.id}">
                  <i class="bi bi-file-person me-1"></i> Ver Currículo
                </button>
                <button class="btn btn-sm btn-success" data-action="aprovar" data-id="${c.id}">
                  <i class="bi bi-check-circle me-1"></i> Aprovar
                </button>
                <button class="btn btn-sm btn-danger" data-action="reprovar" data-id="${c.id}">
                  <i class="bi bi-x-circle me-1"></i> Reprovar
                </button>
              </div>
            </div>
          </div>
        `).join('');
        
        // Rebind eventos dos candidatos
        $$('#listaCandidatos [data-action="ver-curriculo"]').forEach(btn => {
          btn.addEventListener('click', (e) => onVerCurriculo(e, candidatos));
        });
        $$('#listaCandidatos [data-action="aprovar"]').forEach(btn => {
          btn.addEventListener('click', (e) => onAprovarCandidato(e));
        });
        $$('#listaCandidatos [data-action="reprovar"]').forEach(btn => {
          btn.addEventListener('click', (e) => onReprovarCandidato(e));
        });
      }
    }
    
    // Atualizar tabela principal
    renderTabela();
    showToast();
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
  $('#filtroStatus').addEventListener('change', (e) => {
    filtro.status = e.target.value; 
    renderTabela();
  });
  $('#filtroCategoria').addEventListener('change', (e) => {
    filtro.categoria = e.target.value; 
    renderTabela();
  });
  $('#pesquisa').addEventListener('input', (e) => {
    filtro.texto = e.target.value; 
    renderTabela();
  });
  $('#btnLimparFiltros').addEventListener('click', () => {
    filtro = { ...filtro, status: "", categoria: "", texto: "", kpi: "Todos" };
    $('#filtroStatus').value = '';
    $('#filtroCategoria').value = '';
    $('#pesquisa').value = '';
    renderTabela();
  });

  // submit nova vaga
  $('#formVaga').addEventListener('submit', (e) => {
    e.preventDefault();
    const f = e.target;
    const nova = {
      id: crypto.randomUUID(),
      titulo: f.titulo.value.trim(),
      categoria: f.categoria.value,
      salario: f.salario.value.trim(),
      tipoContrato: f.tipoContrato.value,
      dataPublicacao: new Date().toISOString().split('T')[0],
      dataLimite: f.dataLimite.value,
      status: f.status.value,
      candidatos: 0,
      descricao: f.descricao.value.trim(),
      requisitos: f.requisitos.value.trim(),
      beneficios: f.beneficios.value.trim()
    };
    vagas.push(nova);
    saveVagas(vagas);
    renderFiltros();
    renderTabela();
    bootstrap.Modal.getInstance($('#modalNovaVaga')).hide();
    f.reset();
  });

  // submit editar
  $('#formEditarVaga').addEventListener('submit', (e) => {
    e.preventDefault();
    const f = e.target;
    const idx = vagas.findIndex(v => v.id === f.id.value);
    if (idx >= 0) {
      vagas[idx] = {
        ...vagas[idx],
        titulo: f.titulo.value.trim(),
        categoria: f.categoria.value,
        salario: f.salario.value.trim(),
        tipoContrato: f.tipoContrato.value,
        dataLimite: f.dataLimite.value,
        status: f.status.value,
        descricao: f.descricao.value.trim(),
        requisitos: f.requisitos.value.trim(),
        beneficios: f.beneficios.value.trim()
      };
      saveVagas(vagas);
      renderFiltros();
      renderTabela();
      bootstrap.Modal.getInstance($('#modalEditarVaga')).hide();
    }
  });

  // Ações do currículo
  $('#btnAprovarCandidato').addEventListener('click', (e) => {
    const candidatoId = e.currentTarget.data('candidato-id');
    const candidato = candidatos.find(c => c.id === candidatoId);
    candidato.status = 'Aprovado';
    saveCandidatos(candidatos);
    bootstrap.Modal.getInstance($('#modalCurriculo')).hide();
    showToast();
  });

  $('#btnReprovarCandidato').addEventListener('click', (e) => {
    const candidatoId = e.currentTarget.data('candidato-id');
    const candidato = candidatos.find(c => c.id === candidatoId);
    candidato.status = 'Reprovado';
    saveCandidatos(candidatos);
    bootstrap.Modal.getInstance($('#modalCurriculo')).hide();
    showToast();
  });
});
