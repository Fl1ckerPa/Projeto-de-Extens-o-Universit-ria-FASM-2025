/**
 * Minhas Candidaturas - Conectado ao Backend
 * Integração completa com PHP/candidaturas.php
 */

document.addEventListener('DOMContentLoaded', () => {
  const candidaturasContainer = document.getElementById('candidaturasContainer');
  const semCandidaturas = document.getElementById('semCandidaturas');
  const filtros = document.querySelectorAll('.status-filter');
  const loadingIndicator = document.getElementById('loadingCandidaturas');
  
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

/**
 * Carrega candidaturas do backend
 */
async function carregarCandidaturas(status) {
  const candidaturasContainer = document.getElementById('candidaturasContainer');
  const semCandidaturas = document.getElementById('semCandidaturas');
  const loadingIndicator = document.getElementById('loadingCandidaturas');
  
  if (loadingIndicator) {
    loadingIndicator.classList.remove('d-none');
  }
  
  if (candidaturasContainer) {
    candidaturasContainer.innerHTML = '';
  }

  try {
    const params = new URLSearchParams({
      acao: 'minhas'
    });

    if (status && status !== 'todas') {
      params.append('status', status);
    }

    const response = await fetch(`../PHP/candidaturas.php?${params.toString()}`);
    const data = await response.json();

    if (loadingIndicator) {
      loadingIndicator.classList.add('d-none');
    }

    if (data.sucesso && data.dados && data.dados.candidaturas) {
      renderizarCandidaturas(data.dados.candidaturas);
    } else {
      if (data.mensagem && data.mensagem.includes('autorizado')) {
        alert('Por favor, faça login para visualizar suas candidaturas.');
        window.location.href = 'login.html';
      } else {
        mostrarErro(data.mensagem || 'Erro ao carregar candidaturas');
      }
    }
  } catch (error) {
    console.error('Erro ao buscar candidaturas:', error);
    if (loadingIndicator) {
      loadingIndicator.classList.add('d-none');
    }
    mostrarErro('Erro ao conectar com o servidor. Tente novamente.');
  }
}

/**
 * Renderiza candidaturas na tela
 */
function renderizarCandidaturas(candidaturas) {
  const container = document.getElementById('candidaturasContainer');
  const semCandidaturas = document.getElementById('semCandidaturas');
  
  if (!container) return;
  
  container.innerHTML = '';

  if (candidaturas.length === 0) {
    if (semCandidaturas) {
      semCandidaturas.classList.remove('d-none');
    }
    return;
  }

  if (semCandidaturas) {
    semCandidaturas.classList.add('d-none');
  }

  candidaturas.forEach(candidatura => {
    const card = document.createElement('div');
    card.className = 'col-12 mb-3';
    card.innerHTML = `
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h5 class="card-title mb-2">${escapeHtml(candidatura.vaga_titulo || 'Vaga sem título')}</h5>
              <p class="text-muted mb-1">
                <i class="bi bi-building"></i> ${escapeHtml(candidatura.empresa_nome || 'Empresa não informada')}
              </p>
              <div class="d-flex flex-wrap gap-3 mb-2">
                ${candidatura.vaga_modalidade ? `<small class="text-muted"><i class="bi bi-briefcase"></i> ${escapeHtml(candidatura.vaga_modalidade)}</small>` : ''}
                ${candidatura.vaga_vinculo ? `<small class="text-muted"><i class="bi bi-file-earmark-text"></i> ${escapeHtml(candidatura.vaga_vinculo)}</small>` : ''}
                ${candidatura.empresa_endereco ? `<small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(candidatura.empresa_endereco)}</small>` : ''}
                <small class="text-muted">
                  <i class="bi bi-calendar"></i> ${formatarData(candidatura.data_candidatura)}
                </small>
              </div>
              ${candidatura.vaga_categoria ? `<span class="badge bg-secondary">${escapeHtml(candidatura.vaga_categoria)}</span>` : ''}
              ${candidatura.cargo_nome ? `<span class="badge bg-primary ms-2">${escapeHtml(candidatura.cargo_nome)}</span>` : ''}
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
              ${getBadgeStatus(candidatura.status)}
              <button class="btn btn-sm btn-outline-primary mt-2" onclick="verDetalhesCandidatura(${candidatura.vaga_id})">
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

/**
 * Mostra mensagem de erro
 */
function mostrarErro(mensagem) {
  const container = document.getElementById('candidaturasContainer');
  if (container) {
    container.innerHTML = `
      <div class="col-12">
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle"></i> ${escapeHtml(mensagem)}
        </div>
      </div>
    `;
  }
}

/**
 * Retorna badge de status formatado
 */
function getBadgeStatus(status) {
  const badges = {
    'Pendente': '<span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Pendente</span>',
    'Aprovado': '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Aprovado</span>',
    'Reprovado': '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Reprovado</span>'
  };
  return badges[status] || badges['Pendente'];
}

/**
 * Formata data
 */
function formatarData(data) {
  if (!data) return 'Data não informada';
  const dataObj = new Date(data);
  return dataObj.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
}

/**
 * Retorna nome do segmento formatado
 */
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

/**
 * Escapa HTML para prevenir XSS
 */
function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Função global para ver detalhes da candidatura
 */
window.verDetalhesCandidatura = async function(vagaId) {
  try {
    const response = await fetch(`../PHP/candidaturas.php?acao=detalhes&id=${vagaId}`);
    const data = await response.json();

    if (data.sucesso && data.dados && data.dados.candidatura) {
      // Criar modal ou redirecionar para página de detalhes
      mostrarModalDetalhes(data.dados.candidatura);
    } else {
      alert(data.mensagem || 'Erro ao carregar detalhes');
    }
  } catch (error) {
    console.error('Erro ao carregar detalhes:', error);
    alert('Erro ao conectar com o servidor.');
  }
};

/**
 * Mostra modal com detalhes da candidatura
 */
function mostrarModalDetalhes(candidatura) {
  // Criar modal dinamicamente ou usar Bootstrap Modal
  const modal = `
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${escapeHtml(candidatura.vaga_titulo)}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <h6>Status: ${getBadgeStatus(candidatura.status)}</h6>
            <p><strong>Empresa:</strong> ${escapeHtml(candidatura.empresa_nome)}</p>
            <p><strong>Descrição:</strong> ${escapeHtml(candidatura.vaga_descricao || 'Sem descrição')}</p>
            ${candidatura.vaga_requisitos ? `<p><strong>Requisitos:</strong> ${escapeHtml(candidatura.vaga_requisitos)}</p>` : ''}
            ${candidatura.vaga_beneficios ? `<p><strong>Benefícios:</strong> ${escapeHtml(candidatura.vaga_beneficios)}</p>` : ''}
            <p><strong>Data da candidatura:</strong> ${formatarData(candidatura.data_candidatura)}</p>
            ${candidatura.data_avaliacao ? `<p><strong>Data da avaliação:</strong> ${formatarData(candidatura.data_avaliacao)}</p>` : ''}
            ${candidatura.observacoes ? `<p><strong>Observações:</strong> ${escapeHtml(candidatura.observacoes)}</p>` : ''}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Remover modal existente
  const modalExistente = document.getElementById('modalDetalhes');
  if (modalExistente) {
    modalExistente.remove();
  }
  
  // Adicionar novo modal
  document.body.insertAdjacentHTML('beforeend', modal);
  
  // Mostrar modal
  const bsModal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
  bsModal.show();
}
