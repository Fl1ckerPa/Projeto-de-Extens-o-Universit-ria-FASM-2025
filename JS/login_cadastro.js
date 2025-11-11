/* Exibe a dica para inserir a senha */

// popover-init.js

document.addEventListener("DOMContentLoaded", function () {
  const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
  popoverTriggerList.forEach(function (popoverTriggerEl) {
    new bootstrap.Popover(popoverTriggerEl);
  });
});

/*
const senhaInput = document.getElementById("senha")
const senhaDica = document.getElementById("senhaDica")

senhaInput.addEventListener('focus',() =>{
    console.log('Foco no input');
    senhaDica.classList.remove('d-none');
});

senhaInput.addEventListener('blur',() =>{
    console.log('sem Foco no input');
    senhaDica.classList.add('d-none');
});
*/

/* --------------------------------------------------------------------------------------------- */

/*  | Modal seletor de tipo de cadastro / login |
    | Inclusao de campos CPF e CNPJ |
    | Exibe o modal automaticamente ao carregar a página |
    | Inclui máscaras para CPF e CNPJ |
*/
document.addEventListener('DOMContentLoaded', function () {
    // Inicializa o modal
    const modalElement = document.getElementById('staticBackdrop1');
    const modal = new bootstrap.Modal(modalElement);
    
    // Flag para controlar se o modal seletor já foi mostrado
    let modalSeletorMostrado = false;

    // Mostra o modal na tela automaticamente apenas uma vez
    if (!modalSeletorMostrado) {
        modal.show();
        modalSeletorMostrado = true;
    }

    // Seleciona o formulário e o input hidden do tipo cadastro
    const form = document.getElementById('formCadastro');
    const tipoInput = document.getElementById('tipoCadastro');
    const redirectInput = document.getElementById('redirect');
    
    // Preservar parâmetro redirect da URL
    const urlParams = new URLSearchParams(window.location.search);
    const redirect = urlParams.get('redirect');
    if (redirect && redirectInput) {
        redirectInput.value = redirect;
    }
    
    // Variável global para armazenar o tipo escolhido (usado também no modal de recuperação)
    let tipoUsuarioEscolhido = null;
    
    // Seleciona os campos de CPF e CNPJ
    const campoCPF = document.getElementById('campoCPF');
    const campoCNPJ = document.getElementById('campoCNPJ');
    const inputCPF = document.getElementById('cpf');
    const inputCNPJ = document.getElementById('cnpj');

    // Aplicar máscaras com IMask
    if (window.IMask && inputCPF) {
        IMask(inputCPF, { mask: '000.000.000-00' });
    }
    if (window.IMask && inputCNPJ) {
        IMask(inputCNPJ, { mask: '00.000.000/0000-00' });
    }

    // Quando o modal seletor for fechado, marcar como já mostrado
    modalElement.addEventListener('hidden.bs.modal', function() {
        modalSeletorMostrado = true;
    });
    
    // Prevenir que o modal seletor apareça quando o modal de recuperação estiver aberto
    // Esta verificação será configurada depois que o modal de recuperação for definido
    
    // Botões do modal
    document.getElementById('btnPF').addEventListener('click', function () {
        tipoInput.value = 'pf';
        tipoUsuarioEscolhido = 'pf'; // Armazenar tipo escolhido

        // mostra CPF, esconde CNPJ
        campoCPF.classList.remove('d-none');
        inputCPF.required = true;

        campoCNPJ.classList.add('d-none');
        inputCNPJ.required = false;
        inputCNPJ.value = '';

        modal.hide();
        form.classList.remove('d-none');
        modalSeletorMostrado = true; // Marcar como já mostrado
    });

    document.getElementById('btnPJ').addEventListener('click', function () {
        tipoInput.value = 'pj';
        tipoUsuarioEscolhido = 'pj'; // Armazenar tipo escolhido

        // mostra CNPJ, esconde CPF
        campoCNPJ.classList.remove('d-none');
        inputCNPJ.required = true;

        campoCPF.classList.add('d-none');
        inputCPF.required = false;
        inputCPF.value = '';

        modal.hide();
        form.classList.remove('d-none');
        modalSeletorMostrado = true; // Marcar como já mostrado
    });

    /* --------------------------------------------------------------------------------------------- */
    /*  | Modal de Recuperação de Senha | */
    
    // Elementos do modal de recuperação
    const modalRecuperarSenha = document.getElementById('modalRecuperarSenha');
    const modalTipoRecuperar = document.getElementById('modalTipoRecuperar');
    const formRecuperarSenha = document.getElementById('formRecuperarSenha');
    const btnEnviarRecuperar = document.getElementById('btnEnviarRecuperar');
    const campoCPFRecuperar = document.getElementById('campoCPFRecuperar');
    const campoCNPJRecuperar = document.getElementById('campoCNPJRecuperar');
    const inputCPFRecuperar = document.getElementById('cpfRecuperar');
    const inputCNPJRecuperar = document.getElementById('cnpjRecuperar');
    const mensagemRecuperar = document.getElementById('mensagemRecuperar');
    
    // Instanciar modais
    const modalRecuperar = new bootstrap.Modal(modalRecuperarSenha);
    const modalTipo = new bootstrap.Modal(modalTipoRecuperar);
    
    // Prevenir que o modal seletor apareça quando o modal de recuperação estiver aberto
    modalElement.addEventListener('show.bs.modal', function(e) {
        const modalRecuperarAberto = modalRecuperarSenha && 
                                     (modalRecuperarSenha.classList.contains('show') || 
                                      modalRecuperarSenha.getAttribute('aria-hidden') === 'false');
        if (modalRecuperarAberto) {
            e.preventDefault();
            e.stopPropagation();
            modal.hide();
            modalElement.style.display = 'none';
            return false;
        }
    });
    
    // Encontrar o link "Recuperar senha" e adicionar listener
    const linkRecuperarSenha = document.querySelector('a[data-bs-target="#modalRecuperarSenha"]');
    if (linkRecuperarSenha) {
        linkRecuperarSenha.addEventListener('click', function(e) {
            // Verificar se o tipo já foi escolhido
            if (!tipoUsuarioEscolhido) {
                e.preventDefault();
                alert('Por favor, escolha primeiro o tipo de usuário (Pessoa Física ou Pessoa Jurídica) no modal de seleção.');
                modal.show();
                return false;
            }
            
            // Esconder completamente o modal seletor antes de abrir o modal de recuperação
            modal.hide();
            
            // Forçar esconder o modal seletor usando display none
            setTimeout(() => {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                modalElement.setAttribute('aria-hidden', 'true');
                modalElement.setAttribute('aria-modal', 'false');
                
                // Esconder o backdrop do modal seletor
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop && backdrop.getAttribute('data-bs-modal') === 'staticBackdrop1') {
                    backdrop.remove();
                }
            }, 100);
            
            // Configurar o modal de recuperação com o tipo escolhido
            if (tipoCadastroRecuperar) {
                tipoCadastroRecuperar.value = tipoUsuarioEscolhido;
            }
            
            // Mostrar o campo correto baseado no tipo escolhido
            if (tipoUsuarioEscolhido === 'pf') {
                campoCPFRecuperar.classList.remove('d-none');
                inputCPFRecuperar.required = true;
                campoCNPJRecuperar.classList.add('d-none');
                inputCNPJRecuperar.required = false;
                inputCNPJRecuperar.value = '';
            } else if (tipoUsuarioEscolhido === 'pj') {
                campoCNPJRecuperar.classList.remove('d-none');
                inputCNPJRecuperar.required = true;
                campoCPFRecuperar.classList.add('d-none');
                inputCPFRecuperar.required = false;
                inputCPFRecuperar.value = '';
            }
            
            // Botão "Enviar Email" já está visível por padrão
            
            // Desativar o modal seletor completamente
            modalElement.setAttribute('data-bs-backdrop', 'false');
            modalElement.setAttribute('data-bs-keyboard', 'false');
            modalElement.style.pointerEvents = 'none';
            modalElement.style.zIndex = '-1';
            
            // Desabilitar botões do modal seletor
            const btnPF = document.getElementById('btnPF');
            const btnPJ = document.getElementById('btnPJ');
            if (btnPF) {
                btnPF.disabled = true;
                btnPF.style.pointerEvents = 'none';
            }
            if (btnPJ) {
                btnPJ.disabled = true;
                btnPJ.style.pointerEvents = 'none';
            }
            
            modalSeletorMostrado = true; // Marcar como já mostrado
        });
    }
    
    // Quando o modal de recuperação for aberto, configurar com o tipo escolhido
    modalRecuperarSenha.addEventListener('show.bs.modal', function() {
        // Verificar se o tipo foi escolhido
        if (!tipoUsuarioEscolhido) {
            // Se não foi escolhido, fechar o modal e mostrar o seletor
            modalRecuperar.hide();
            modal.show();
            return;
        }
        
        // Configurar o modal de recuperação com o tipo escolhido
        if (tipoCadastroRecuperar) {
            tipoCadastroRecuperar.value = tipoUsuarioEscolhido;
        }
        
        // Mostrar o campo correto baseado no tipo escolhido
        if (tipoUsuarioEscolhido === 'pf') {
            campoCPFRecuperar.classList.remove('d-none');
            inputCPFRecuperar.required = true;
            campoCNPJRecuperar.classList.add('d-none');
            inputCNPJRecuperar.required = false;
            inputCNPJRecuperar.value = '';
        } else if (tipoUsuarioEscolhido === 'pj') {
            campoCNPJRecuperar.classList.remove('d-none');
            inputCNPJRecuperar.required = true;
            campoCPFRecuperar.classList.add('d-none');
            inputCPFRecuperar.required = false;
            inputCPFRecuperar.value = '';
        }
        
        // Botão "Enviar Email" já está visível por padrão
        
        // Esconder completamente o modal seletor
        modal.hide();
        
        // Forçar esconder o modal seletor usando display none
        setTimeout(() => {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            modalElement.setAttribute('aria-hidden', 'true');
            modalElement.setAttribute('aria-modal', 'false');
            
            // Esconder o backdrop do modal seletor se existir
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                if (backdrop.getAttribute('data-bs-modal') === 'staticBackdrop1') {
                    backdrop.remove();
                }
            });
        }, 50);
        
        // Desativar o modal seletor completamente
        modalElement.setAttribute('data-bs-backdrop', 'false');
        modalElement.setAttribute('data-bs-keyboard', 'false');
        modalElement.style.pointerEvents = 'none';
        modalElement.style.zIndex = '-1';
        modalElement.style.display = 'none';
        
        // Desabilitar botões do modal seletor
        const btnPF = document.getElementById('btnPF');
        const btnPJ = document.getElementById('btnPJ');
        if (btnPF) {
            btnPF.disabled = true;
            btnPF.style.pointerEvents = 'none';
        }
        if (btnPJ) {
            btnPJ.disabled = true;
            btnPJ.style.pointerEvents = 'none';
        }
        
        // Marcar como já mostrado para evitar que apareça novamente
        modalSeletorMostrado = true;
    });
    
    // Observer para garantir que o modal seletor permaneça escondido quando o modal de recuperação estiver aberto
    const observer = new MutationObserver(function(mutations) {
        const modalRecuperarAberto = modalRecuperarSenha && 
                                     (modalRecuperarSenha.classList.contains('show') || 
                                      modalRecuperarSenha.getAttribute('aria-hidden') === 'false');
        
        if (modalRecuperarAberto) {
            // Se o modal de recuperação estiver aberto, garantir que o modal seletor esteja escondido
            if (modalElement.classList.contains('show') || modalElement.getAttribute('aria-hidden') === 'false') {
                modal.hide();
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                modalElement.setAttribute('aria-hidden', 'true');
            }
        }
    });
    
    // Observar mudanças no modal de recuperação
    if (modalRecuperarSenha) {
        observer.observe(modalRecuperarSenha, {
            attributes: true,
            attributeFilter: ['class', 'aria-hidden']
        });
    }
    
    // Quando o modal de recuperação for fechado, reativar o modal seletor (se necessário)
    modalRecuperarSenha.addEventListener('hidden.bs.modal', function() {
        // Reativar o modal seletor (mas não mostrar)
        modalElement.setAttribute('data-bs-backdrop', 'static');
        modalElement.setAttribute('data-bs-keyboard', 'false');
        modalElement.style.pointerEvents = '';
        modalElement.style.opacity = '';
        modalElement.style.zIndex = '';
        
        // Reabilitar botões do modal seletor
        const btnPF = document.getElementById('btnPF');
        const btnPJ = document.getElementById('btnPJ');
        if (btnPF) {
            btnPF.disabled = false;
            btnPF.style.pointerEvents = '';
        }
        if (btnPJ) {
            btnPJ.disabled = false;
            btnPJ.style.pointerEvents = '';
        }
        
        // Não mostrar o modal seletor novamente após fechar o modal de recuperação
        // O modal seletor só deve aparecer quando a página carrega
    });
    
    // Aplicar máscaras nos campos de recuperação
    if (window.IMask && inputCPFRecuperar) {
        IMask(inputCPFRecuperar, { mask: '000.000.000-00' });
    }
    if (window.IMask && inputCNPJRecuperar) {
        IMask(inputCNPJRecuperar, { mask: '00.000.000/0000-00' });
    }
    
    // Campo hidden para tipo de cadastro
    const tipoCadastroRecuperar = document.getElementById('tipoCadastroRecuperar');
    
    // Remover funcionalidade do botão "Selecionar Tipo" - não é mais necessário
    // O tipo será definido pelo modal seletor inicial
    
    // Resetar modal quando fechar
    modalRecuperarSenha.addEventListener('hidden.bs.modal', function() {
        // Limpar campos
        formRecuperarSenha.reset();
        if (tipoCadastroRecuperar) {
            tipoCadastroRecuperar.value = '';
        }
        mensagemRecuperar.classList.add('d-none');
        mensagemRecuperar.innerHTML = '';
        
        // Resetar visibilidade dos campos (ambos escondidos inicialmente)
        campoCPFRecuperar.classList.add('d-none');
        campoCNPJRecuperar.classList.add('d-none');
        inputCPFRecuperar.required = false;
        inputCNPJRecuperar.required = false;
        
        // Botão "Enviar Email" permanece visível
        
        // Reabilitar campos e botão
        formRecuperarSenha.querySelectorAll('input').forEach(input => {
            input.disabled = false;
        });
        btnEnviarRecuperar.disabled = false;
        btnEnviarRecuperar.innerHTML = '<i class="bi bi-envelope"></i> Enviar Email';
    });
    
    // Envio do formulário de recuperação via AJAX
    if (formRecuperarSenha) {
        formRecuperarSenha.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Verificar se o tipo foi escolhido
            if (!tipoUsuarioEscolhido) {
                mensagemRecuperar.className = 'alert alert-danger';
                mensagemRecuperar.textContent = 'Por favor, escolha primeiro o tipo de usuário (Pessoa Física ou Pessoa Jurídica).';
                mensagemRecuperar.classList.remove('d-none');
                return;
            }
            
            // Validar se CPF ou CNPJ foi preenchido baseado no tipo escolhido
            const cpfValue = inputCPFRecuperar.value.replace(/\D/g, '');
            const cnpjValue = inputCNPJRecuperar.value.replace(/\D/g, '');
            
            if (tipoUsuarioEscolhido === 'pf') {
                // Verificar se CPF está preenchido e tem 11 dígitos
                if (!cpfValue || cpfValue.length !== 11) {
                    mensagemRecuperar.className = 'alert alert-danger';
                    mensagemRecuperar.textContent = 'Por favor, preencha um CPF válido com 11 dígitos.';
                    mensagemRecuperar.classList.remove('d-none');
                    return;
                }
                // Garantir que o campo CPF esteja visível e seja enviado
                campoCPFRecuperar.classList.remove('d-none');
                inputCPFRecuperar.required = true;
                // Limpar CNPJ para não enviar
                inputCNPJRecuperar.value = '';
            } else if (tipoUsuarioEscolhido === 'pj') {
                // Verificar se CNPJ está preenchido e tem 14 dígitos
                if (!cnpjValue || cnpjValue.length !== 14) {
                    mensagemRecuperar.className = 'alert alert-danger';
                    mensagemRecuperar.textContent = 'Por favor, preencha um CNPJ válido com 14 dígitos.';
                    mensagemRecuperar.classList.remove('d-none');
                    return;
                }
                // Garantir que o campo CNPJ esteja visível e seja enviado
                campoCNPJRecuperar.classList.remove('d-none');
                inputCNPJRecuperar.required = true;
                // Limpar CPF para não enviar
                inputCPFRecuperar.value = '';
            } else {
                mensagemRecuperar.className = 'alert alert-danger';
                mensagemRecuperar.textContent = 'Por favor, escolha primeiro o tipo de usuário.';
                mensagemRecuperar.classList.remove('d-none');
                return;
            }
            
            // Desabilitar botão durante envio
            btnEnviarRecuperar.disabled = true;
            btnEnviarRecuperar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
            
            // Criar FormData
            const formData = new FormData(formRecuperarSenha);
            
            // Enviar via fetch
            fetch('../PHP/recuperar_senha.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Criar elemento temporário para parsear HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Procurar por título e alerta
                const titleElement = tempDiv.querySelector('h1, h2, h3, h4, h5');
                const alertElement = tempDiv.querySelector('.alert');
                const messagesList = tempDiv.querySelector('ul');
                
                // Verificar se é sucesso ou erro
                const isSuccess = alertElement && alertElement.classList.contains('alert-success');
                const isError = alertElement && alertElement.classList.contains('alert-danger');
                
                // Extrair título e mensagens
                const titulo = titleElement ? titleElement.textContent.trim() : '';
                let mensagens = [];
                if (messagesList) {
                    const items = messagesList.querySelectorAll('li');
                    mensagens = Array.from(items).map(item => {
                        // Remover tags HTML e pegar apenas texto
                        const texto = item.textContent.trim();
                        return texto;
                    });
                }
                
                if (isSuccess || (titulo && titulo.toLowerCase().includes('sucesso'))) {
                    // Sucesso
                    const mensagemTexto = mensagens.length > 0 
                        ? mensagens.join('<br>') 
                        : 'Email enviado com sucesso! Verifique sua caixa de entrada.';
                    
                    mensagemRecuperar.className = 'alert alert-success';
                    mensagemRecuperar.innerHTML = '<strong>Sucesso!</strong><br>' + mensagemTexto;
                    mensagemRecuperar.classList.remove('d-none');
                    
                    // Desabilitar formulário
                    formRecuperarSenha.querySelectorAll('input').forEach(input => {
                        input.disabled = true;
                    });
                    btnEnviarRecuperar.disabled = true;
                    
                    // Fechar modal após 4 segundos
                    setTimeout(() => {
                        modalRecuperar.hide();
                    }, 4000);
                } else {
                    // Erro - usar título se disponível, senão usar mensagens
                    let tituloErro = 'Erro';
                    if (titulo && titulo.toLowerCase().includes('email não cadastrado')) {
                        tituloErro = 'Email não cadastrado';
                    } else if (titulo) {
                        tituloErro = titulo;
                    }
                    
                    const mensagemTexto = mensagens.length > 0 
                        ? mensagens.join('<br>') 
                        : 'Erro ao enviar email de recuperação. Tente novamente mais tarde.';
                    
                    mensagemRecuperar.className = 'alert alert-danger';
                    mensagemRecuperar.innerHTML = '<strong>' + tituloErro + '</strong><br>' + mensagemTexto;
                    mensagemRecuperar.classList.remove('d-none');
                    
                    // Reabilitar botão
                    btnEnviarRecuperar.disabled = false;
                    btnEnviarRecuperar.innerHTML = '<i class="bi bi-envelope"></i> Enviar Email';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mensagemRecuperar.className = 'alert alert-danger';
                mensagemRecuperar.innerHTML = '<strong>Erro:</strong> Não foi possível enviar o email. Tente novamente mais tarde.';
                mensagemRecuperar.classList.remove('d-none');
                
                // Reabilitar botão
                btnEnviarRecuperar.disabled = false;
                btnEnviarRecuperar.innerHTML = '<i class="bi bi-envelope"></i> Enviar Email';
            });
        });
    }
});
