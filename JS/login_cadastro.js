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

    // Mostra o modal na tela automaticamente
    modal.show();

    // Seleciona o formulário e o input hidden do tipo cadastro
    const form = document.getElementById('formCadastro');
    const tipoInput = document.getElementById('tipoCadastro');

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

    // Botões do modal
    document.getElementById('btnPF').addEventListener('click', function () {
        tipoInput.value = 'pf';

        // mostra CPF, esconde CNPJ
        campoCPF.classList.remove('d-none');
        inputCPF.required = true;

        campoCNPJ.classList.add('d-none');
        inputCNPJ.required = false;
        inputCNPJ.value = '';

        modal.hide();
        form.classList.remove('d-none');
    });

    document.getElementById('btnPJ').addEventListener('click', function () {
        tipoInput.value = 'pj';

        // mostra CNPJ, esconde CPF
        campoCNPJ.classList.remove('d-none');
        inputCNPJ.required = true;

        campoCPF.classList.add('d-none');
        inputCPF.required = false;
        inputCPF.value = '';

        modal.hide();
        form.classList.remove('d-none');
    });
});
