/**
 * Buscar Vagas - Conectado ao Backend
 * Integração completa com PHP/vagas.php
 */

document.addEventListener('DOMContentLoaded', () => {
  const vagasContainer = document.getElementById('vagasContainer');
  const semVagas = document.getElementById('semVagas');
  const filtros = document.querySelectorAll('.segmento-filter');
  const loadingIndicator = document.getElementById('loadingVagas');
  
  let segmentoAtual = 'todos';
  let paginaAtual = 1;

  // Event listeners nos filtros
  filtros.forEach(filtro => {
    filtro.addEventListener('click', () => {
      filtros.forEach(f => f.classList.remove('active'));
      filtro.classList.add('active');
      segmentoAtual = filtro.getAttribute('data-segmento');
      paginaAtual = 1;
      carregarVagas(segmentoAtual, paginaAtual);
    });
  });

  // Carregar vagas inicialmente
  carregarVagas('todos', 1);

  /**
   * Carrega vagas do backend
   */
  async function carregarVagas(segmento, pagina = 1) {
    if (loadingIndicator) {
      loadingIndicator.classList.remove('d-none');
    }
    
    if (vagasContainer) {
      vagasContainer.innerHTML = '';
    }

    try {
      // Construir URL com parâmetros
      const params = new URLSearchParams({
        acao: 'listar',
        pagina: pagina,
        por_pagina: 12
      });

      if (segmento && segmento !== 'todos') {
        params.append('categoria', segmento);
      }

      const response = await fetch(`../PHP/vagas.php?${params.toString()}`);
      const data = await response.json();

      if (loadingIndicator) {
        loadingIndicator.classList.add('d-none');
      }

      if (data.sucesso && data.dados) {
        // data.dados é o array de vagas quando vem de Response::paginated
        const vagas = Array.isArray(data.dados) ? data.dados : (data.dados.vagas || []);
        renderizarVagas(vagas);
        
        // Atualizar paginação se houver
        if (data.paginacao) {
          atualizarPaginacao(data.paginacao);
        }
      } else {
        mostrarErro(data.mensagem || 'Erro ao carregar vagas');
      }
    } catch (error) {
      console.error('Erro ao buscar vagas:', error);
      if (loadingIndicator) {
        loadingIndicator.classList.add('d-none');
      }
      mostrarErro('Erro ao conectar com o servidor. Tente novamente.');
    }
  }

  /**
   * Renderiza vagas na tela
   */
  function renderizarVagas(vagas) {
    if (!vagasContainer) return;
    
    vagasContainer.innerHTML = '';

    if (vagas.length === 0) {
      if (semVagas) {
        semVagas.classList.remove('d-none');
      }
      return;
    }

    if (semVagas) {
      semVagas.classList.add('d-none');
    }

    vagas.forEach(vaga => {
      const card = document.createElement('div');
      card.className = 'col-md-6 col-lg-4 mb-4';
      card.innerHTML = `
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge bg-primary">${getSegmentoNome(vaga.categoria)}</span>
              <button class="btn btn-sm btn-outline-primary" onclick="candidatarVaga(${vaga.id})" 
                      ${vaga.ja_candidatou ? 'disabled title="Você já se candidatou a esta vaga"' : ''}>
                <i class="bi bi-send"></i> ${vaga.ja_candidatou ? 'Candidatado' : 'Candidatar'}
              </button>
            </div>
            <h5 class="card-title">${escapeHtml(vaga.titulo)}</h5>
            <p class="text-muted small mb-2">
              <i class="bi bi-building"></i> ${escapeHtml(vaga.empresa_nome || 'Empresa não informada')}
            </p>
            <p class="card-text">${escapeHtml(vaga.descricao ? vaga.descricao.substring(0, 150) + '...' : 'Sem descrição')}</p>
            <div class="d-flex flex-wrap gap-2 mb-2">
              ${vaga.vinculo ? `<small class="text-muted"><i class="bi bi-file-earmark-text"></i> ${escapeHtml(vaga.vinculo)}</small>` : ''}
              ${vaga.modalidade ? `<small class="text-muted"><i class="bi bi-briefcase"></i> ${escapeHtml(vaga.modalidade)}</small>` : ''}
              ${vaga.empresa_endereco ? `<small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(vaga.empresa_endereco)}</small>` : ''}
              ${vaga.cargo_nome ? `<small class="text-muted"><i class="bi bi-person-badge"></i> ${escapeHtml(vaga.cargo_nome)}</small>` : ''}
            </div>
            ${vaga.total_candidatos > 0 ? `<small class="text-info"><i class="bi bi-people"></i> ${vaga.total_candidatos} candidato(s)</small>` : ''}
            <div class="mt-2">
              <button class="btn btn-sm btn-link p-0" onclick="verDetalhesVaga(${vaga.id})">
                Ver detalhes <i class="bi bi-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      `;
      vagasContainer.appendChild(card);
    });
  }

  /**
   * Atualiza controles de paginação
   */
  function atualizarPaginacao(paginacao) {
    // Implementar paginação se necessário
    // Por enquanto, apenas log
    console.log('Paginação:', paginacao);
  }

  /**
   * Mostra mensagem de erro
   */
  function mostrarErro(mensagem) {
    if (vagasContainer) {
      vagasContainer.innerHTML = `
        <div class="col-12">
          <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle"></i> ${escapeHtml(mensagem)}
          </div>
        </div>
      `;
    }
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
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Função global para candidatar-se a vaga
   */
  window.candidatarVaga = async function(vagaId) {
    // Verificar se usuário está logado
    // Nota: Verificação deve ser feita via sessão PHP, não sessionStorage
    if (confirm('Deseja se candidatar a esta vaga?')) {
      try {
        const formData = new FormData();
        formData.append('acao', 'enviar');
        formData.append('vaga_id', vagaId);

        const response = await fetch('../PHP/candidaturas.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.sucesso) {
          alert('Candidatura enviada com sucesso!');
          // Recarregar vagas para atualizar status
          carregarVagas(segmentoAtual, paginaAtual);
        } else {
          alert(data.mensagem || 'Erro ao enviar candidatura');
          if (data.mensagem && data.mensagem.includes('login')) {
            window.location.href = 'login.html';
          }
        }
      } catch (error) {
        console.error('Erro ao enviar candidatura:', error);
        alert('Erro ao conectar com o servidor. Tente novamente.');
      }
    }
  };

  /**
   * Função global para ver detalhes da vaga
   */
  window.verDetalhesVaga = function(vagaId) {
    window.location.href = `buscar_vagas.html?id=${vagaId}`;
  };

  // Carregar detalhes se ID estiver na URL
  const urlParams = new URLSearchParams(window.location.search);
  const vagaId = urlParams.get('id');
  if (vagaId) {
    carregarDetalhesVaga(vagaId);
  }

  /**
   * Carrega detalhes de uma vaga específica
   */
  async function carregarDetalhesVaga(id) {
    try {
      const response = await fetch(`../PHP/vagas.php?acao=detalhes&id=${id}`);
      const data = await response.json();

      if (data.sucesso && data.dados && data.dados.vaga) {
        // Renderizar modal ou página de detalhes
        // Por enquanto, apenas log
        console.log('Detalhes da vaga:', data.dados.vaga);
      }
    } catch (error) {
      console.error('Erro ao carregar detalhes:', error);
    }
  }
});
