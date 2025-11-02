document.addEventListener('DOMContentLoaded', () => {
  // Verificar se usuário está logado
  const usuarioLogado = sessionStorage.getItem('usuarioLogado');
  if (!usuarioLogado) {
    alert('Por favor, faça login para visualizar suas candidaturas.');
    window.location.href = 'login.html';
    return;
  }

  const filtros = document.querySelectorAll('.status-filter');
  let statusAtual = 'todas';

  // Event listeners nos filtros
  filtros.forEach(filtro => {
    filtro.addEventListener('click', () => {
      filtros.forEach(f => f.classList.remove('active'));
      filtro.classList.add('active');
      statusAtual = filtro.getAttribute('data-status');
      carregarCandidaturas(statusAtual);
    });
  });

  // Carregar candidaturas inicialmente
  carregarCandidaturas('todas');
});

function carregarCandidaturas(status) {
  const usuarioId = sessionStorage.getItem('usuarioId');
  
  // Simulação - substituir por chamada API real
  const candidaturasMock = [
    {
      id: 1,
      vagaId: 1,
      titulo: 'Desenvolvedor Full Stack',
      empresa: 'Tech Solutions',
      status: 'Pendente',
      dataCandidatura: '2025-01-15',
      salario: 'R$ 5.000,00',
      localizacao: 'Muriaé - MG'
    },
    {
      id: 2,
      vagaId: 2,
      titulo: 'Vendedor Externo',
      empresa: 'Vendas Pro',
      status: 'Aprovado',
      dataCandidatura: '2025-01-10',
      salario: 'R$ 2.500,00 + Comissão',
      localizacao: 'Muriaé - MG'
    },
    {
      id: 3,
      vagaId: 3,
      titulo: 'Assistente Administrativo',
      empresa: 'Admin Corp',
      status: 'Reprovado',
      dataCandidatura: '2025-01-05',
      salario: 'R$ 2.000,00',
      localizacao: 'Muriaé - MG'
    }
  ];

  // Filtrar por status
  const candidaturasFiltradas = status === 'todas'
    ? candidaturasMock
    : candidaturasMock.filter(c => c.status === status);

  renderizarCandidaturas(candidaturasFiltradas);
}

function renderizarCandidaturas(candidaturas) {
  const container = document.getElementById('candidaturasContainer');
  const semCandidaturas = document.getElementById('semCandidaturas');
  
  container.innerHTML = '';

  if (candidaturas.length === 0) {
    semCandidaturas.classList.remove('d-none');
    return;
  }

  semCandidaturas.classList.add('d-none');

  candidaturas.forEach(candidatura => {
    const card = document.createElement('div');
    card.className = 'col-12';
    card.innerHTML = `
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h5 class="card-title mb-2">${candidatura.titulo}</h5>
              <p class="text-muted mb-1"><i class="bi bi-building"></i> ${candidatura.empresa}</p>
              <div class="d-flex flex-wrap gap-3 mb-2">
                <small class="text-muted"><i class="bi bi-currency-dollar"></i> ${candidatura.salario}</small>
                <small class="text-muted"><i class="bi bi-geo-alt"></i> ${candidatura.localizacao}</small>
                <small class="text-muted"><i class="bi bi-calendar"></i> ${formatarData(candidatura.dataCandidatura)}</small>
              </div>
            </div>
            <div class="col-md-4 text-md-end">
              ${getBadgeStatus(candidatura.status)}
              <button class="btn btn-sm btn-outline-primary mt-2" onclick="verDetalhes(${candidatura.vagaId})">
                <i class="bi bi-eye"></i> Ver Detalhes
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
}

function getBadgeStatus(status) {
  const badges = {
    'Pendente': '<span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Pendente</span>',
    'Aprovado': '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Aprovado</span>',
    'Reprovado': '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Reprovado</span>'
  };
  return badges[status] || badges['Pendente'];
}

function formatarData(data) {
  const dataObj = new Date(data);
  return dataObj.toLocaleDateString('pt-BR');
}

window.verDetalhes = function(vagaId) {
  window.location.href = `buscar_vagas.html?vaga=${vagaId}`;
};

