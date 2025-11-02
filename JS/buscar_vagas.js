document.addEventListener('DOMContentLoaded', () => {
  const vagasContainer = document.getElementById('vagasContainer');
  const semVagas = document.getElementById('semVagas');
  const filtros = document.querySelectorAll('.segmento-filter');
  let segmentoAtual = 'todos';

  // Event listeners nos filtros
  filtros.forEach(filtro => {
    filtro.addEventListener('click', () => {
      filtros.forEach(f => f.classList.remove('active'));
      filtro.classList.add('active');
      segmentoAtual = filtro.getAttribute('data-segmento');
      carregarVagas(segmentoAtual);
    });
  });

  // Carregar vagas inicialmente
  carregarVagas('todos');

  function carregarVagas(segmento) {
    // Simulação de dados - substituir por chamada API real
    const vagasMock = [
      {
        id: 1,
        titulo: 'Desenvolvedor Full Stack',
        empresa: 'Tech Solutions',
        segmento: 'tecnologia',
        salario: 'R$ 5.000,00',
        tipoContrato: 'CLT',
        localizacao: 'Muriaé - MG',
        descricao: 'Desenvolvedor para trabalhar com React, Node.js e banco de dados PostgreSQL.'
      },
      {
        id: 2,
        titulo: 'Vendedor Externo',
        empresa: 'Vendas Pro',
        segmento: 'comercio',
        salario: 'R$ 2.500,00 + Comissão',
        tipoContrato: 'CLT',
        localizacao: 'Muriaé - MG',
        descricao: 'Vendedor para atuar na região de Muriaé e cidades vizinhas.'
      },
      {
        id: 3,
        titulo: 'Assistente Administrativo',
        empresa: 'Admin Corp',
        segmento: 'administracao',
        salario: 'R$ 2.000,00',
        tipoContrato: 'CLT',
        localizacao: 'Muriaé - MG',
        descricao: 'Assistente para área administrativa com foco em atendimento.'
      },
      {
        id: 4,
        titulo: 'Enfermeiro',
        empresa: 'Saúde Total',
        segmento: 'saude',
        salario: 'R$ 3.500,00',
        tipoContrato: 'CLT',
        localizacao: 'Muriaé - MG',
        descricao: 'Enfermeiro para atuar em clínica médica.'
      },
      {
        id: 5,
        titulo: 'Professor de Matemática',
        empresa: 'Colégio Educar',
        segmento: 'educacao',
        salario: 'R$ 3.000,00',
        tipoContrato: 'CLT',
        localizacao: 'Muriaé - MG',
        descricao: 'Professor para ensino médio, licenciatura em Matemática.'
      },
      {
        id: 6,
        titulo: 'Engenheiro Civil',
        empresa: 'Construtora ABC',
        segmento: 'construcao',
        salario: 'R$ 6.000,00',
        tipoContrato: 'CLT',
        localizacao: 'Muriaé - MG',
        descricao: 'Engenheiro para coordenar obras residenciais e comerciais.'
      }
    ];

    // Filtrar vagas por segmento
    const vagasFiltradas = segmento === 'todos' 
      ? vagasMock 
      : vagasMock.filter(v => v.segmento === segmento);

    // Renderizar vagas
    renderizarVagas(vagasFiltradas);
  }

  function renderizarVagas(vagas) {
    vagasContainer.innerHTML = '';
    
    if (vagas.length === 0) {
      semVagas.classList.remove('d-none');
      return;
    }

    semVagas.classList.add('d-none');

    vagas.forEach(vaga => {
      const card = document.createElement('div');
      card.className = 'col-md-6 col-lg-4';
      card.innerHTML = `
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge bg-primary">${getSegmentoNome(vaga.segmento)}</span>
              <button class="btn btn-sm btn-outline-primary" onclick="candidatarVaga(${vaga.id})">
                <i class="bi bi-send"></i> Candidatar
              </button>
            </div>
            <h5 class="card-title">${vaga.titulo}</h5>
            <p class="text-muted small mb-2"><i class="bi bi-building"></i> ${vaga.empresa}</p>
            <p class="card-text">${vaga.descricao}</p>
            <div class="d-flex flex-wrap gap-2 mb-2">
              <small class="text-muted"><i class="bi bi-currency-dollar"></i> ${vaga.salario}</small>
              <small class="text-muted"><i class="bi bi-file-earmark-text"></i> ${vaga.tipoContrato}</small>
              <small class="text-muted"><i class="bi bi-geo-alt"></i> ${vaga.localizacao}</small>
            </div>
          </div>
        </div>
      `;
      vagasContainer.appendChild(card);
    });
  }

  function getSegmentoNome(segmento) {
    const segmentos = {
      'tecnologia': 'Tecnologia',
      'comercio': 'Comércio',
      'servicos': 'Serviços',
      'industria': 'Indústria',
      'construcao': 'Construção Civil',
      'saude': 'Saúde',
      'educacao': 'Educação',
      'agronegocio': 'Agronegócio',
      'administracao': 'Administração',
      'engenharia': 'Engenharia',
      'outros': 'Outros'
    };
    return segmentos[segmento] || segmento;
  }

  window.candidatarVaga = function(vagaId) {
    // Verificar se usuário está logado
    const usuarioLogado = sessionStorage.getItem('usuarioLogado');
    if (!usuarioLogado) {
      alert('Por favor, faça login para se candidatar a vagas.');
      window.location.href = 'login.html';
      return;
    }

    // Aqui seria feita a chamada para a API
    alert(`Candidatura enviada para a vaga #${vagaId}!`);
  };
});

