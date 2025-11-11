document.addEventListener('DOMContentLoaded', () => {
  // Verificar tipo de usuário via PHP
  verificarTipoUsuario();
});

function verificarTipoUsuario() {
  fetch('../PHP/check_user_type.php')
    .then(response => response.json())
    .then(data => {
      // Compatibilidade com ambos os formatos
      const dados = data.dados || data.data || {};
      if (data.status === 'success' || data.sucesso) {
        const userType = dados.user_type;
        
        // Se for pessoa jurídica, redirecionar para página específica
        if (userType === 'pj') {
          window.location.href = 'perfil_pj.html';
          return;
        }
        
        // Se for pessoa física, continuar carregando perfil normal
        if (userType === 'pf') {
          carregarInformacoesPerfil();
        } else {
          alert('Tipo de usuário não reconhecido.');
          window.location.href = 'login.html';
        }
      } else {
        // Usuário não autenticado
        alert('Por favor, faça login para acessar seu perfil.');
        window.location.href = 'login.html';
      }
    })
    .catch(error => {
      console.error('Erro ao verificar tipo de usuário:', error);
      alert('Erro ao carregar perfil. Por favor, tente novamente.');
      window.location.href = 'login.html';
    });
}

function carregarInformacoesPerfil() {
  // Buscar dados do perfil via API PHP
  fetch('../PHP/perfil.php?acao=visualizar')
    .then(response => response.json())
    .then(data => {
      // Compatibilidade com ambos os formatos
      const perfil = data.dados || data.data || {};
      if (data.status === 'success' || data.sucesso) {
        const curriculo = perfil.curriculo;
        
        const semInformacoes = document.getElementById('semInformacoes');
        const curriculoSection = document.getElementById('curriculoSection');
        const empresaSection = document.getElementById('empresaSection');

        if (!curriculo) {
          semInformacoes.classList.remove('d-none');
          curriculoSection.classList.add('d-none');
          empresaSection.classList.add('d-none');
          return;
        }

        semInformacoes.classList.add('d-none');
        exibirCurriculo(curriculo);
        curriculoSection.classList.remove('d-none');
      } else {
        console.error('Erro ao carregar perfil:', data.message);
        document.getElementById('semInformacoes').classList.remove('d-none');
      }
    })
    .catch(error => {
      console.error('Erro ao carregar perfil:', error);
      document.getElementById('semInformacoes').classList.remove('d-none');
    });
}

function exibirCurriculo(curriculo) {
  const content = document.getElementById('curriculoContent');
  
  // Formatar data de nascimento
  const dataNascimento = curriculo.nascimento ? new Date(curriculo.nascimento).toLocaleDateString('pt-BR') : 'Não informado';
  
  // Processar experiências se existirem
  let experienciasHtml = '';
  if (curriculo.experiencias) {
    try {
      const experiencias = typeof curriculo.experiencias === 'string' 
        ? JSON.parse(curriculo.experiencias) 
        : curriculo.experiencias;
      
      if (Array.isArray(experiencias) && experiencias.length > 0) {
        experienciasHtml = '<div class="mt-3"><h6>Experiências Profissionais</h6><ul class="list-unstyled">';
        experiencias.forEach(exp => {
          experienciasHtml += `
            <li class="mb-3 p-3 border rounded">
              <strong>${exp.empresa || 'Não informado'}</strong><br>
              <em>${exp.cargo || 'Não informado'}</em><br>
              <small>${exp.atividades || 'Não informado'}</small>
            </li>
          `;
        });
        experienciasHtml += '</ul></div>';
      }
    } catch (e) {
      console.error('Erro ao processar experiências:', e);
    }
  }
  
  content.innerHTML = `
    <div class="row">
      <div class="col-md-6">
        <h6 class="mb-3">Dados Pessoais</h6>
        <p><strong>Nome:</strong> ${curriculo.nome || 'Não informado'}</p>
        <p><strong>Email:</strong> ${curriculo.email || 'Não informado'}</p>
        <p><strong>Telefone:</strong> ${curriculo.telefone || 'Não informado'}</p>
        <p><strong>Data de Nascimento:</strong> ${dataNascimento}</p>
        ${curriculo.genero ? `<p><strong>Gênero:</strong> ${curriculo.genero}</p>` : ''}
        ${curriculo.estado_civil ? `<p><strong>Estado Civil:</strong> ${curriculo.estado_civil}</p>` : ''}
      </div>
      <div class="col-md-6">
        <h6 class="mb-3">Formação</h6>
        <p><strong>Escolaridade:</strong> ${curriculo.escolaridade || 'Não informado'}</p>
        ${curriculo.outros_cursos ? `<p><strong>Cursos e Certificados:</strong><br>${curriculo.outros_cursos}</p>` : ''}
      </div>
    </div>
    ${experienciasHtml}
    ${curriculo.foto ? `<div class="mt-3"><img src="../${curriculo.foto}" alt="Foto" class="img-thumbnail" style="max-width: 200px;"></div>` : ''}
  `;
}

