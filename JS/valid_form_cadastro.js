document.addEventListener('DOMContentLoaded', function () {
    // Detecta qual formulário está presente
    const cadastroForm = document.querySelector('form');
    if (!cadastroForm) return;

    cadastroForm.addEventListener('submit', function (e) {
        let valid = true;
        let messages = [];

        // Validação para cadastro.html
        const nome = document.getElementById('nome');
        const cpf = document.getElementById('cpf');
        const email = document.getElementById('email');
        const senha = document.getElementById('senha');
        const senhaverif = document.getElementById('senhaverif');

        if (nome && nome.value.trim().length < 3) {
            valid = false;
            messages.push('Nome deve ter pelo menos 3 caracteres.');
        }

        if (cpf && !/^\d{11}$/.test(cpf.value)) {
            valid = false;
            messages.push('CPF deve conter 11 dígitos numéricos.');
        }

        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            valid = false;
            messages.push('Email inválido.');
        }

        if (senha) {
            if (senha.value.length < 8 || senha.value.length > 20) {
                valid = false;
                messages.push('Senha deve ter entre 8 e 20 caracteres.');
            }
            if (!/[A-Za-z]/.test(senha.value) || !/\d/.test(senha.value)) {
                valid = false;
                messages.push('Senha deve conter letras e números.');
            }
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(senha.value)) {
                valid = false;
                messages.push('Senha deve conter um caractere especial.');
            }
        }

        if (senhaverif && senha && senhaverif.value !== senha.value) {
            valid = false;
            messages.push('As senhas não coincidem.');
        }

        // Validação para login.html (apenas email e senha)
        if (!nome && (!email.value || !senha.value)) {
            valid = false;
            messages.push('Preencha todos os campos.');
        }

        if (!valid) {
            e.preventDefault();
            alert(messages.join('\n'));
        }
    });
});