// Aguardar que protect-page.js verifique a autenticação primeiro
(function() {
  const inicializar = async () => {
    // Verificar se está autenticado
    if (!window.authManager) {
      console.error('authManager não disponível');
      return;
    }

    // Verificar autenticação
    const user = await window.authManager.checkAuth();
    
    // Verificar se está realmente autenticado
    if (!user || user.autenticado !== true || !window.authManager.isAuthenticated) {
      // Se não estiver autenticado, protect-page.js já redirecionou
      console.log('Usuário não autenticado, aguardando redirecionamento...');
      return;
    }
    
    console.log('Usuário autenticado, carregando dados...');

    // Carregar dados do usuário do servidor
    await carregarDadosUsuario();

    // Formulário de alterar senha (se existir)
    const formAlterarSenha = document.getElementById('formAlterarSenha');
    if (formAlterarSenha) {
      formAlterarSenha.addEventListener('submit', (e) => {
        e.preventDefault();
        alterarSenha();
      });
    }

    // Formulário de alterar email (se existir)
    const formAlterarEmail = document.getElementById('formAlterarEmail');
    if (formAlterarEmail) {
      formAlterarEmail.addEventListener('submit', (e) => {
        e.preventDefault();
        alterarEmail();
      });
    }
  };

  // Aguardar evento de autenticação verificada OU aguardar authManager
  const tentarInicializar = () => {
    if (window.authManager && window.authManager.isAuthenticated) {
      inicializar();
    } else if (window.authManager) {
      // Tentar verificar autenticação
      window.authManager.checkAuth().then(() => {
        if (window.authManager.isAuthenticated) {
          inicializar();
        }
      });
    } else {
      // Aguardar um pouco e tentar novamente
      setTimeout(tentarInicializar, 100);
    }
  };

  // Escutar evento de autenticação verificada
  window.addEventListener('authVerified', () => {
    inicializar();
  });

  // Também tentar quando DOM estiver pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tentarInicializar);
  } else {
    tentarInicializar();
  }
})();

async function carregarDadosUsuario() {
  try {
    // Primeiro, tentar usar dados do authManager (mais rápido)
    const user = await window.authManager.checkAuth();
    
    if (user && user.autenticado) {
      const nomeElement = document.getElementById('nomeUsuario');
      const emailElement = document.getElementById('emailUsuario');
      const novoEmailInput = document.getElementById('novoEmail');

      if (nomeElement) nomeElement.textContent = user.user_nome || 'Usuário';
      if (emailElement) emailElement.textContent = user.user_email || 'usuario@email.com';
      if (novoEmailInput) novoEmailInput.value = user.user_email || '';
    }

    // Depois, buscar dados completos do perfil (incluindo foto)
    const response = await fetch('../PHP/perfil.php?acao=visualizar');
    const data = await response.json();

    if (data.sucesso && data.dados) {
      const usuario = data.dados;
      
      // Preencher informações na página (sobrescrever com dados completos)
      const nomeElement = document.getElementById('nomeUsuario');
      const emailElement = document.getElementById('emailUsuario');
      const fotoElement = document.getElementById('fotoUsuario');
      const novoEmailInput = document.getElementById('novoEmail');

      if (nomeElement && usuario.nome) {
        nomeElement.textContent = usuario.nome || usuario.user_nome || 'Usuário';
      }
      
      if (emailElement && usuario.email) {
        emailElement.textContent = usuario.email || usuario.user_email || 'usuario@email.com';
      }
      
      if (novoEmailInput && usuario.email) {
        novoEmailInput.value = usuario.email || usuario.user_email || '';
      }

      // Carregar foto se existir
      if (fotoElement && usuario.foto) {
        const fotoPath = usuario.foto.startsWith('http') || usuario.foto.startsWith('/')
          ? usuario.foto 
          : `../uploads/${usuario.foto}`;
        fotoElement.src = fotoPath;
        fotoElement.onerror = function() {
          // Se foto não carregar, usar logo padrão
          this.src = '../IMG/logo_descubra_short.png';
        };
      }
    }
  } catch (error) {
    console.error('Erro ao carregar dados do usuário:', error);
    
    // Fallback: usar dados da sessão via authManager
    const user = await window.authManager.checkAuth();
    if (user && user.autenticado) {
      const nomeElement = document.getElementById('nomeUsuario');
      const emailElement = document.getElementById('emailUsuario');
      const novoEmailInput = document.getElementById('novoEmail');

      if (nomeElement) nomeElement.textContent = user.user_nome || 'Usuário';
      if (emailElement) emailElement.textContent = user.user_email || 'usuario@email.com';
      if (novoEmailInput) novoEmailInput.value = user.user_email || '';
    }
  }
}

async function alterarEmail() {
  const novoEmail = document.getElementById('novoEmail').value.trim();
  
  if (!novoEmail) {
    alert('Por favor, informe um email válido.');
    return;
  }

  // Validar formato de email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(novoEmail)) {
    alert('Por favor, informe um email válido.');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('acao', 'atualizar');
    formData.append('email', novoEmail);

    const response = await fetch('../PHP/perfil.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.sucesso) {
      // Atualizar exibição
      document.getElementById('emailUsuario').textContent = novoEmail;
      alert('Email alterado com sucesso!');
      
      // Atualizar dados no authManager
      if (window.authManager && window.authManager.user) {
        window.authManager.user.user_email = novoEmail;
      }
    } else {
      alert(data.mensagem || 'Erro ao alterar email. Tente novamente.');
    }
  } catch (error) {
    console.error('Erro ao alterar email:', error);
    alert('Erro ao conectar com o servidor. Tente novamente.');
  }
}

async function alterarSenha() {
  const senhaAtual = document.getElementById('senhaAtual').value;
  const senhaNova = document.getElementById('senhaNova').value;
  const senhaNovaConfirm = document.getElementById('senhaNovaConfirm').value;

  if (!senhaAtual || !senhaNova || !senhaNovaConfirm) {
    alert('Por favor, preencha todos os campos.');
    return;
  }

  if (senhaNova !== senhaNovaConfirm) {
    alert('As novas senhas não coincidem.');
    return;
  }

  if (!/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/.test(senhaNova)) {
    alert('A senha deve ter entre 8 e 20 caracteres, letras, números e um caractere especial.');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('acao', 'alterar_senha');
    formData.append('senha_atual', senhaAtual);
    formData.append('senha_nova', senhaNova);
    formData.append('senha_nova_confirm', senhaNovaConfirm);

    const response = await fetch('../PHP/configuracoes.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.sucesso) {
      alert('Senha alterada com sucesso!');
      // Limpar campos
      document.getElementById('senhaAtual').value = '';
      document.getElementById('senhaNova').value = '';
      document.getElementById('senhaNovaConfirm').value = '';
    } else {
      alert(data.mensagem || 'Erro ao alterar senha. Tente novamente.');
    }
  } catch (error) {
    console.error('Erro ao alterar senha:', error);
    alert('Erro ao conectar com o servidor. Tente novamente.');
  }
}

function deslogar() {
  if (confirm('Deseja realmente deslogar?')) {
    if (window.authManager) {
      window.authManager.logout();
    } else {
      window.location.href = 'login.html';
    }
  }
}

async function excluirConta() {
  if (!confirm('ATENÇÃO: Esta ação não pode ser desfeita. Deseja realmente excluir sua conta?')) {
    return;
  }

  const senha = prompt('Por favor, digite sua senha para confirmar a exclusão:');
  if (!senha) {
    return;
  }

  if (!confirm('Tem certeza? Todos os seus dados serão permanentemente excluídos. Digite CONFIRMAR para continuar.')) {
    return;
  }

  try {
    const formData = new FormData();
    formData.append('acao', 'excluir_conta');
    formData.append('senha', senha);
    formData.append('confirmar', 'CONFIRMAR');

    const response = await fetch('../PHP/configuracoes.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.sucesso) {
      alert('Conta excluída com sucesso.');
      // Fazer logout
      if (window.authManager) {
        window.authManager.logout();
      } else {
        window.location.href = 'login.html';
      }
    } else {
      alert(data.mensagem || 'Erro ao excluir conta. Verifique sua senha e tente novamente.');
    }
  } catch (error) {
    console.error('Erro ao excluir conta:', error);
    alert('Erro ao conectar com o servidor. Tente novamente.');
  }
}

