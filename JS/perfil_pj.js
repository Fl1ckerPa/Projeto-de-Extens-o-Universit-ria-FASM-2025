document.addEventListener('DOMContentLoaded', () => {
  // Verificar se usuário está autenticado e é PJ
  verificarAutenticacao();
  carregarInformacoesEmpresa();
});

function verificarAutenticacao() {
  fetch('../PHP/check_user_type.php')
    .then(response => response.json())
    .then(data => {
      // Compatibilidade com ambos os formatos
      const dados = data.dados || data.data || {};
      
      // Verificar se está autenticado
      if (!dados.autenticado) {
        alert('Por favor, faça login para acessar seu perfil.');
        window.location.href = 'login.html';
        return;
      }
      
      // Verificar tipo de usuário
      const userType = dados.user_type;
      if (userType !== 'pj') {
        alert('Acesso negado. Esta página é apenas para pessoas jurídicas.');
        window.location.href = userType === 'pf' ? 'perfil.html' : 'login.html';
        return;
      }
      
      // Se chegou aqui, é PJ autenticado - pode continuar
    })
    .catch(error => {
      console.error('Erro ao verificar autenticação:', error);
      alert('Erro ao verificar autenticação. Por favor, tente novamente.');
      window.location.href = 'login.html';
    });
}

function carregarInformacoesEmpresa() {
  const loading = document.getElementById('loading');
  const semInformacoes = document.getElementById('semInformacoes');
  const empresaSection = document.getElementById('empresaSection');

  // Buscar dados do perfil via API PHP
  fetch('../PHP/perfil.php?acao=visualizar')
    .then(response => response.json())
    .then(data => {
      loading.classList.add('d-none');
      
      // Compatibilidade com ambos os formatos
      const perfil = data.dados || data.data || {};
      if (data.status === 'success' || data.sucesso) {
        const usuario = perfil.usuario;
        const empresa = perfil.empresa;
        
        if (!empresa || !perfil.tem_empresa) {
          semInformacoes.classList.remove('d-none');
          empresaSection.classList.add('d-none');
          return;
        }

        semInformacoes.classList.add('d-none');
        exibirEmpresa(usuario, empresa);
        empresaSection.classList.remove('d-none');
      } else {
        console.error('Erro ao carregar perfil:', data.message);
        semInformacoes.classList.remove('d-none');
        empresaSection.classList.add('d-none');
      }
    })
    .catch(error => {
      console.error('Erro ao carregar perfil:', error);
      loading.classList.add('d-none');
      semInformacoes.classList.remove('d-none');
      empresaSection.classList.add('d-none');
    });
}

function exibirEmpresa(usuario, empresa) {
  const content = document.getElementById('empresaContent');
  
  // Formatar CNPJ se existir
  const formatarCNPJ = (cnpj) => {
    if (!cnpj) return 'Não informado';
    const cnpjLimpo = cnpj.replace(/\D/g, '');
    if (cnpjLimpo.length === 14) {
      return cnpjLimpo.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
    }
    return cnpj;
  };
  
  // Formatar telefone se existir
  const formatarTelefone = (telefone) => {
    if (!telefone) return 'Não informado';
    const telLimpo = telefone.replace(/\D/g, '');
    if (telLimpo.length === 11) {
      return telLimpo.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    } else if (telLimpo.length === 10) {
      return telLimpo.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
    }
    return telefone;
  };
  
  // Formatar CEP se existir
  const formatarCEP = (cep) => {
    if (!cep) return 'Não informado';
    const cepLimpo = cep.replace(/\D/g, '');
    if (cepLimpo.length === 8) {
      return cepLimpo.replace(/^(\d{5})(\d{3})$/, '$1-$2');
    }
    return cep;
  };

  // Extrair dados da empresa (pode vir de estabelecimento ou empresas)
  const nomeEmpresa = empresa.nome_social || empresa.nome || 'Não informado';
  const cnpj = formatarCNPJ(empresa.cnpj || usuario.cnpj || '');
  const email = empresa.email || usuario.email || 'Não informado';
  const telefone = formatarTelefone(empresa.telefone || '');
  const endereco = empresa.endereco || 'Não informado';
  const cidade = empresa.cidade || 'Não informado';
  const estado = empresa.estado || 'Não informado';
  const cep = formatarCEP(empresa.cep || '');
  const segmento = empresa.segmento || 'Não informado';
  const site = empresa.site || '';
  const linkedin = empresa.linkedin || '';
  const sobre = empresa.sobre || '';
  const funcionarios = empresa.funcionarios || 'Não informado';
  const fundacao = empresa.fundacao || '';
  const logo = empresa.logo || '';

  let html = `
    <div class="row">
      ${logo ? `
        <div class="col-12 mb-4 text-center">
          <img src="../${logo}" alt="Logo da empresa" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
        </div>
      ` : ''}
      
      <div class="col-md-6 mb-4">
        <h6 class="mb-3 text-primary"><i class="bi bi-building"></i> Dados Básicos</h6>
        <p><strong>Nome Social:</strong> ${nomeEmpresa}</p>
        <p><strong>CNPJ:</strong> ${cnpj}</p>
        <p><strong>Segmento:</strong> ${segmento}</p>
        ${funcionarios !== 'Não informado' ? `<p><strong>Número de Funcionários:</strong> ${funcionarios}</p>` : ''}
        ${fundacao ? `<p><strong>Ano de Fundação:</strong> ${fundacao}</p>` : ''}
      </div>
      
      <div class="col-md-6 mb-4">
        <h6 class="mb-3 text-primary"><i class="bi bi-geo-alt"></i> Endereço</h6>
        <p><strong>Endereço:</strong> ${endereco}</p>
        <p><strong>Cidade:</strong> ${cidade}</p>
        <p><strong>Estado:</strong> ${estado}</p>
        ${cep !== 'Não informado' ? `<p><strong>CEP:</strong> ${cep}</p>` : ''}
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-6 mb-4">
        <h6 class="mb-3 text-primary"><i class="bi bi-telephone"></i> Contato</h6>
        <p><strong>Email:</strong> <a href="mailto:${email}">${email}</a></p>
        <p><strong>Telefone:</strong> ${telefone}</p>
        ${site ? `<p><strong>Site:</strong> <a href="${site}" target="_blank" rel="noopener noreferrer">${site}</a></p>` : ''}
        ${linkedin ? `<p><strong>LinkedIn:</strong> <a href="${linkedin}" target="_blank" rel="noopener noreferrer">${linkedin}</a></p>` : ''}
      </div>
    </div>
  `;

  if (sobre) {
    html += `
      <div class="row">
        <div class="col-12 mb-4">
          <h6 class="mb-3 text-primary"><i class="bi bi-info-circle"></i> Sobre a Empresa</h6>
          <p class="text-justify">${sobre}</p>
        </div>
      </div>
    `;
  }

  content.innerHTML = html;
}

