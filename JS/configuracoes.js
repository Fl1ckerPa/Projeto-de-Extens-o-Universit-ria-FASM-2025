document.addEventListener('DOMContentLoaded', () => {
  // Verificar se usuário está logado
  const usuarioLogado = sessionStorage.getItem('usuarioLogado');
  if (!usuarioLogado) {
    alert('Por favor, faça login para acessar as configurações.');
    window.location.href = 'login.html';
    return;
  }

  carregarDadosUsuario();

  // Formulário de alterar email
  document.getElementById('formAlterarEmail').addEventListener('submit', (e) => {
    e.preventDefault();
    alterarEmail();
  });
});

function carregarDadosUsuario() {
  // Buscar dados do usuário logado
  const usuarioId = sessionStorage.getItem('usuarioId');
  const usuarioData = JSON.parse(localStorage.getItem(`usuario_${usuarioId}`) || '{}');
  
  // Preencher informações
  document.getElementById('nomeUsuario').textContent = usuarioData.nome || 'Usuário';
  document.getElementById('emailUsuario').textContent = usuarioData.email || 'usuario@email.com';
  
  // Carregar foto se existir
  if (usuarioData.foto) {
    document.getElementById('fotoUsuario').src = usuarioData.foto;
  }
  
  // Preencher campo de email atual
  document.getElementById('novoEmail').value = usuarioData.email || '';
}

function alterarEmail() {
  const novoEmail = document.getElementById('novoEmail').value;
  
  if (!novoEmail) {
    alert('Por favor, informe um email válido.');
    return;
  }

  // Simulação - substituir por chamada API real
  const usuarioId = sessionStorage.getItem('usuarioId');
  const usuarioData = JSON.parse(localStorage.getItem(`usuario_${usuarioId}`) || '{}');
  usuarioData.email = novoEmail;
  localStorage.setItem(`usuario_${usuarioId}`, JSON.stringify(usuarioData));
  
  // Atualizar exibição
  document.getElementById('emailUsuario').textContent = novoEmail;
  
  alert('Email alterado com sucesso!');
}

function deslogar() {
  if (confirm('Deseja realmente deslogar?')) {
    sessionStorage.removeItem('usuarioLogado');
    sessionStorage.removeItem('usuarioId');
    window.location.href = 'login.html';
  }
}

function excluirConta() {
  if (confirm('ATENÇÃO: Esta ação não pode ser desfeita. Deseja realmente excluir sua conta?')) {
    if (confirm('Tem certeza? Todos os seus dados serão permanentemente excluídos.')) {
      // Simulação - substituir por chamada API real
      const usuarioId = sessionStorage.getItem('usuarioId');
      localStorage.removeItem(`usuario_${usuarioId}`);
      localStorage.removeItem(`curriculo_${usuarioId}`);
      localStorage.removeItem(`empresa_${usuarioId}`);
      
      sessionStorage.removeItem('usuarioLogado');
      sessionStorage.removeItem('usuarioId');
      
      alert('Conta excluída com sucesso.');
      window.location.href = 'login.html';
    }
  }
}

