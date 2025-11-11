/**
 * Script para gerenciar a interface do index.html baseado no tipo de usuário
 */

document.addEventListener('DOMContentLoaded', async () => {
  // Aguardar autenticação ser verificada pelo auth.js
  await new Promise(resolve => {
    if (window.authManager && window.authManager.isAuthenticated !== undefined) {
      resolve();
    } else {
      // Aguardar até que auth.js seja carregado
      const checkAuth = setInterval(() => {
        if (window.authManager && window.authManager.isAuthenticated !== undefined) {
          clearInterval(checkAuth);
          resolve();
        }
      }, 100);
    }
  });
  
  // Verificar tipo de usuário apenas se estiver autenticado
  if (window.authManager && window.authManager.isAuthenticated) {
    verificarTipoUsuarioEAjustarInterface();
  }
});

function verificarTipoUsuarioEAjustarInterface() {
  // Verificar tipo de usuário via PHP
  fetch('../PHP/check_user_type.php')
    .then(response => response.json())
    .then(data => {
      // Response retorna 'sucesso'/'status' e 'dados'/'data'
      const dados = data.dados || data.data || {};
      if ((data.sucesso || data.status === 'success') && dados.autenticado) {
        const userType = dados.user_type;
        
        // Ajustar interface baseado no tipo de usuário
        if (userType === 'pj') {
          // Pessoa Jurídica - mostrar Gestão de Vagas
          ajustarParaPessoaJuridica();
        } else if (userType === 'pf') {
          // Pessoa Física - manter Buscar Vagas e mostrar cards de PF
          ajustarParaPessoaFisica();
        } else {
          // Tipo desconhecido - manter interface padrão
          manterPadrao();
        }
      } else {
        // Usuário não autenticado - manter interface padrão
        manterPadrao();
      }
    })
    .catch(error => {
      console.error('Erro ao verificar tipo de usuário:', error);
      // Em caso de erro, manter interface padrão
      manterPadrao();
    });
}

function ajustarParaPessoaJuridica() {
  // Modificar card de Vagas para Gestão de Vagas
  const linkVagas = document.getElementById('linkVagas');
  const tituloVagas = document.getElementById('tituloVagas');
  const descVagas = document.getElementById('descVagas');
  const iconVagas = document.getElementById('iconVagas');
  
  if (linkVagas) {
    linkVagas.href = 'gestao_vagas_empresa.html';
  }
  
  if (tituloVagas) {
    tituloVagas.textContent = 'Gestão de Vagas';
  }
  
  if (descVagas) {
    descVagas.textContent = 'Gerencie suas vagas';
  }
  
  if (iconVagas) {
    // Mudar ícone para gestão de vagas
    iconVagas.className = 'bi bi-clipboard-check fs-2 text-primary mb-2 d-block';
  }
  
  // Esconder cards específicos de Pessoa Física
  const cardCurriculo = document.getElementById('cardCurriculo');
  const cardCandidaturas = document.getElementById('cardCandidaturas');
  
  if (cardCurriculo) {
    cardCurriculo.style.display = 'none';
  }
  
  if (cardCandidaturas) {
    cardCandidaturas.style.display = 'none';
  }
  
  // Esconder seção de cadastro de currículo (apenas para PF)
  const secaoCadastroCurriculo = document.getElementById('hanging-icons');
  if (secaoCadastroCurriculo) {
    // Encontrar o card de cadastro de currículo
    const cards = secaoCadastroCurriculo.querySelectorAll('.col');
    cards.forEach(card => {
      const titulo = card.querySelector('h3');
      if (titulo && titulo.textContent.includes('Currículo')) {
        card.style.display = 'none';
      }
    });
    
    // Ajustar título da seção se necessário
    const tituloSecao = secaoCadastroCurriculo.querySelector('h2');
    if (tituloSecao && tituloSecao.textContent.includes('Cadastro de Currículo')) {
      tituloSecao.textContent = 'Cadastro de Empresa';
    }
  }
}

function ajustarParaPessoaFisica() {
  // Manter card de Buscar Vagas
  const linkVagas = document.getElementById('linkVagas');
  const tituloVagas = document.getElementById('tituloVagas');
  const descVagas = document.getElementById('descVagas');
  const iconVagas = document.getElementById('iconVagas');
  
  if (linkVagas) {
    linkVagas.href = 'buscar_vagas.html';
  }
  
  if (tituloVagas) {
    tituloVagas.textContent = 'Buscar Vagas';
  }
  
  if (descVagas) {
    descVagas.textContent = 'Encontre oportunidades';
  }
  
  if (iconVagas) {
    iconVagas.className = 'bi bi-search-heart fs-2 text-primary mb-2 d-block';
  }
  
  // Mostrar cards específicos de Pessoa Física
  const cardCurriculo = document.getElementById('cardCurriculo');
  const cardCandidaturas = document.getElementById('cardCandidaturas');
  
  if (cardCurriculo) {
    cardCurriculo.style.display = '';
  }
  
  if (cardCandidaturas) {
    cardCandidaturas.style.display = '';
  }
  
  // Mostrar seção de cadastro de currículo
  const secaoCadastroCurriculo = document.getElementById('hanging-icons');
  if (secaoCadastroCurriculo) {
    const cards = secaoCadastroCurriculo.querySelectorAll('.col');
    cards.forEach(card => {
      card.style.display = '';
    });
    
    // Restaurar título original se necessário
    const tituloSecao = secaoCadastroCurriculo.querySelector('h2');
    if (tituloSecao && tituloSecao.textContent === 'Cadastro de Empresa') {
      tituloSecao.textContent = 'Cadastro de Currículo ou Empresa';
    }
  }
}

function manterPadrao() {
  // Interface padrão (para usuários não logados)
  // Manter tudo como está no HTML
  ajustarParaPessoaFisica(); // Por padrão, mostrar como PF
}

// Função removida - agora gerenciada pelo auth.js

