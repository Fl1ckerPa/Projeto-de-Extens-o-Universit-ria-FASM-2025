document.addEventListener('DOMContentLoaded', function() {
    // Elementos do formulário
    const form = document.getElementById('formCadastroEmpresa');
    const cnpjInput = document.getElementById('cnpj');
    const telefoneInput = document.getElementById('telefone');
    const cepInput = document.getElementById('cep');
    const logoInput = document.getElementById('logo');
    const sobreTextarea = document.getElementById('sobre');
    const emailInput = document.getElementById('email');
    const siteInput = document.getElementById('site');
    const linkedinInput = document.getElementById('linkedin');

    // Máscaras de entrada
    initializeMasks();
    
    // Validações em tempo real
    initializeValidations();
    
    // Preview da logo
    initializeLogoPreview();
    
    // Validação do CEP
    initializeCepValidation();
    
    // Validação do formulário
    initializeFormValidation();

    function initializeMasks() {
        // Máscara do CNPJ
        if (cnpjInput) {
            const cnpjMask = IMask(cnpjInput, {
                mask: '00.000.000/0000-00',
                placeholder: '00.000.000/0000-00'
            });
        }

        // Máscara do telefone
        if (telefoneInput) {
            const telefoneMask = IMask(telefoneInput, {
                mask: '(00) 00000-0000',
                placeholder: '(32) 99999-9999'
            });
        }

        // Máscara do CEP
        if (cepInput) {
            const cepMask = IMask(cepInput, {
                mask: '00000-000',
                placeholder: '00000-000'
            });
        }
    }

    function initializeValidations() {
        // Validação do CNPJ
        if (cnpjInput) {
            cnpjInput.addEventListener('blur', function() {
                validateCNPJ(this);
            });
        }

        // Validação do e-mail
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });
        }

        // Validação do telefone
        if (telefoneInput) {
            telefoneInput.addEventListener('blur', function() {
                validateTelefone(this);
            });
        }

        // Validação do site
        if (siteInput) {
            siteInput.addEventListener('blur', function() {
                validateURL(this, 'Site');
            });
        }

        // Validação do LinkedIn
        if (linkedinInput) {
            linkedinInput.addEventListener('blur', function() {
                validateURL(this, 'LinkedIn');
            });
        }

        // Validação do campo "Sobre"
        if (sobreTextarea) {
            sobreTextarea.addEventListener('input', function() {
                validateSobre(this);
            });
        }
    }

    function initializeLogoPreview() {
        if (logoInput) {
            logoInput.addEventListener('change', function() {
                previewLogo(this);
            });
        }
    }

    function initializeCepValidation() {
        if (cepInput) {
            cepInput.addEventListener('blur', function() {
                if (this.value.length === 9) { // CEP com máscara
                    buscarCep(this.value.replace(/\D/g, ''));
                }
            });
        }
    }

    function initializeFormValidation() {
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validateForm()) {
                    submitForm();
                }
            });
        }
    }
    
    // Validação gate para botões Próximo (se houver etapas no futuro)
    window.validarCamposEmpresaObrigatorios = function(container){
        const scope = container || form;
        let isValid = true;
        const requiredFields = scope.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if ((field.value || '').toString().trim() === '') {
                setFieldValid(field, false, 'Este campo é obrigatório');
                isValid = false;
            } else {
                setFieldValid(field, true);
            }
        });
        return isValid;
    }

    // Funções de validação
    function validateCNPJ(input) {
        const cnpj = input.value.replace(/\D/g, '');
        
        if (cnpj.length === 0) {
            setFieldValid(input, false, 'CNPJ é obrigatório');
            return false;
        }
        
        if (cnpj.length !== 14) {
            setFieldValid(input, false, 'CNPJ deve ter 14 dígitos');
            return false;
        }
        
        if (!isValidCNPJ(cnpj)) {
            setFieldValid(input, false, 'CNPJ inválido');
            return false;
        }
        
        setFieldValid(input, true);
        return true;
    }

    function validateEmail(input) {
        const email = input.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email.length === 0) {
            setFieldValid(input, false, 'E-mail é obrigatório');
            return false;
        }
        
        if (!emailRegex.test(email)) {
            setFieldValid(input, false, 'E-mail inválido');
            return false;
        }
        
        setFieldValid(input, true);
        return true;
    }

    function validateTelefone(input) {
        const telefone = input.value.replace(/\D/g, '');
        
        if (telefone.length === 0) {
            setFieldValid(input, false, 'Telefone é obrigatório');
            return false;
        }
        
        if (telefone.length < 10) {
            setFieldValid(input, false, 'Telefone deve ter pelo menos 10 dígitos');
            return false;
        }
        
        setFieldValid(input, true);
        return true;
    }

    function validateURL(input, fieldName) {
        const url = input.value.trim();
        
        if (url.length === 0) {
            setFieldValid(input, true); // URL é opcional
            return true;
        }
        
        const urlRegex = /^https?:\/\/.+/;
        
        if (!urlRegex.test(url)) {
            setFieldValid(input, false, `${fieldName} deve começar com http:// ou https://`);
            return false;
        }
        
        setFieldValid(input, true);
        return true;
    }

    function validateSobre(input) {
        const texto = input.value.trim();
        
        if (texto.length === 0) {
            setFieldValid(input, false, 'Descrição é obrigatória');
            return false;
        }
        
        if (texto.length < 50) {
            setFieldValid(input, false, 'Descrição deve ter pelo menos 50 caracteres');
            return false;
        }
        
        setFieldValid(input, true);
        return true;
    }

    function setFieldValid(input, isValid, message = '') {
        input.classList.remove('is-valid', 'is-invalid');
        
        if (isValid) {
            input.classList.add('is-valid');
        } else {
            input.classList.add('is-invalid');
        }
        
        // Atualizar mensagem de feedback
        const feedback = input.parentNode.querySelector('.invalid-feedback');
        if (feedback && message) {
            feedback.textContent = message;
        }
    }

    function validateForm() {
        let isValid = true;
        
        // Validar todos os campos obrigatórios
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (field.type === 'file') {
                // Validação especial para arquivos
                if (field.id === 'logo') {
                    validateLogo(field);
                }
            } else if (field.type === 'textarea') {
                if (field.id === 'sobre') {
                    if (!validateSobre(field)) isValid = false;
                }
            } else if (field.type === 'email') {
                if (!validateEmail(field)) isValid = false;
            } else if (field.id === 'cnpj') {
                if (!validateCNPJ(field)) isValid = false;
            } else if (field.id === 'telefone') {
                if (!validateTelefone(field)) isValid = false;
            } else {
                // Validação padrão
                if (field.value.trim() === '') {
                    setFieldValid(field, false, 'Este campo é obrigatório');
                    isValid = false;
                } else {
                    setFieldValid(field, true);
                }
            }
        });
        
        // Validar URLs opcionais
        if (siteInput && siteInput.value.trim() !== '') {
            if (!validateURL(siteInput, 'Site')) isValid = false;
        }
        
        if (linkedinInput && linkedinInput.value.trim() !== '') {
            if (!validateURL(linkedinInput, 'LinkedIn')) isValid = false;
        }
        
        return isValid;
    }

    function validateLogo(input) {
        if (!input.files || input.files.length === 0) {
            setFieldValid(input, true); // Logo é opcional
            return true;
        }
        
        const file = input.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        
        if (file.size > maxSize) {
            setFieldValid(input, false, 'Logo deve ter no máximo 2MB');
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            setFieldValid(input, false, 'Logo deve ser JPG, PNG ou GIF');
            return false;
        }
        
        setFieldValid(input, true);
        return true;
    }

    function previewLogo(input) {
        const preview = document.getElementById('logoPreview');
        const previewImg = document.getElementById('previewImg');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }

    function buscarCep(cep) {
        if (cep.length !== 8) return;
        
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('endereco').value = data.logradouro;
                    document.getElementById('cidade').value = data.localidade;
                    document.getElementById('estado').value = data.uf;
                }
            })
            .catch(error => {
                console.log('Erro ao buscar CEP:', error);
            });
    }

    function submitForm() {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        const modal = bootstrap.Modal.getInstance(document.getElementById('empresaModal'));
        
        // Mostrar loading
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Preparar dados do formulário
        const formData = new FormData(form);
        
        // Enviar dados via AJAX
        fetch('../PHP/cadastro_empresa.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                // Sucesso - mostrar mensagem e fechar modal
                alert('Empresa cadastrada com sucesso!');
                limparFormulario();
                if (modal) {
                    modal.hide();
                }
            } else {
                // Erro - mostrar mensagem mas manter modal aberto
                let mensagem = data.mensagem || 'Erro desconhecido';
                if (data.erros && data.erros.length > 0) {
                    mensagem += '\n\nErros:\n' + data.erros.join('\n');
                }
                alert(mensagem);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao enviar formulário. Tente novamente.');
        })
        .finally(() => {
            // Resetar botão
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // Eventos do modal
    const modal = document.getElementById('empresaModal');
    if (modal) {
        // Limpar formulário quando modal é fechado
        modal.addEventListener('hidden.bs.modal', function() {
            limparFormulario();
        });
        
        // Focar no primeiro campo quando modal é aberto
        modal.addEventListener('shown.bs.modal', function() {
            cnpjInput.focus();
        });
    }

    // Função para limpar formulário
    window.limparFormulario = function() {
        form.reset();
        
        // Limpar classes de validação
        const inputs = form.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
        
        // Esconder preview da logo
        document.getElementById('logoPreview').style.display = 'none';
        
        // Focar no primeiro campo
        cnpjInput.focus();
    };

    // Função para validar CNPJ
    function isValidCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g, '');
        
        if (cnpj.length !== 14) return false;
        if (/^(\d)\1+$/.test(cnpj)) return false;
        
        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
        
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        
        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0)) return false;
        
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        return resultado == digitos.charAt(1);
    }
});
