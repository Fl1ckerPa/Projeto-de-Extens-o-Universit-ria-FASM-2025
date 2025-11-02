document.addEventListener('DOMContentLoaded', () => {
  // Verificar se usuário está logado
  const usuarioLogado = sessionStorage.getItem('usuarioLogado');
  if (!usuarioLogado) {
    alert('Por favor, faça login para acessar seu perfil.');
    window.location.href = 'login.html';
    return;
  }

  carregarInformacoesPerfil();
});

function carregarInformacoesPerfil() {
  // Simulação - substituir por chamada API real
  const usuarioId = sessionStorage.getItem('usuarioId');
  
  // Verificar se tem currículo cadastrado
  const temCurriculo = localStorage.getItem(`curriculo_${usuarioId}`);
  
  // Verificar se tem empresa cadastrada
  const temEmpresa = localStorage.getItem(`empresa_${usuarioId}`);

  const semInformacoes = document.getElementById('semInformacoes');
  const curriculoSection = document.getElementById('curriculoSection');
  const empresaSection = document.getElementById('empresaSection');

  if (!temCurriculo && !temEmpresa) {
    semInformacoes.classList.remove('d-none');
    curriculoSection.classList.add('d-none');
    empresaSection.classList.add('d-none');
    return;
  }

  semInformacoes.classList.add('d-none');

  if (temCurriculo) {
    const curriculoData = JSON.parse(temCurriculo);
    exibirCurriculo(curriculoData);
    curriculoSection.classList.remove('d-none');
  } else {
    curriculoSection.classList.add('d-none');
  }

  if (temEmpresa) {
    const empresaData = JSON.parse(temEmpresa);
    exibirEmpresa(empresaData);
    empresaSection.classList.remove('d-none');
  } else {
    empresaSection.classList.add('d-none');
  }
}

function exibirCurriculo(curriculo) {
  const content = document.getElementById('curriculoContent');
  content.innerHTML = `
    <div class="row">
      <div class="col-md-6">
        <h6>Dados Pessoais</h6>
        <p><strong>Nome:</strong> ${curriculo.nome || 'Não informado'}</p>
        <p><strong>Email:</strong> ${curriculo.email || 'Não informado'}</p>
        <p><strong>Telefone:</strong> ${curriculo.telefone || 'Não informado'}</p>
        <p><strong>Data de Nascimento:</strong> ${curriculo.nascimento || 'Não informado'}</p>
      </div>
      <div class="col-md-6">
        <h6>Formação</h6>
        <p><strong>Escolaridade:</strong> ${curriculo.escolaridade || 'Não informado'}</p>
      </div>
    </div>
  `;
}

function exibirEmpresa(empresa) {
  const content = document.getElementById('empresaContent');
  content.innerHTML = `
    <div class="row">
      <div class="col-md-6">
        <h6>Dados Básicos</h6>
        <p><strong>Nome Social:</strong> ${empresa.nome_social || 'Não informado'}</p>
        <p><strong>CNPJ:</strong> ${empresa.cnpj || 'Não informado'}</p>
        <p><strong>Segmento:</strong> ${empresa.segmento || 'Não informado'}</p>
      </div>
      <div class="col-md-6">
        <h6>Contato</h6>
        <p><strong>Email:</strong> ${empresa.email || 'Não informado'}</p>
        <p><strong>Telefone:</strong> ${empresa.telefone || 'Não informado'}</p>
        <p><strong>Cidade:</strong> ${empresa.cidade || 'Não informado'}</p>
      </div>
    </div>
    ${empresa.sobre ? `<div class="mt-3"><h6>Sobre</h6><p>${empresa.sobre}</p></div>` : ''}
  `;
}

