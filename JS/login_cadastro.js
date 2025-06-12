/* Exibe a dica para inserir a senha */
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
/* --------------------------------------------------------------------------------------------- */

/*  | Modal seletor de tipo de cadastro / login |
    | Inclusao de campos CPF e CNPJ |
    | Exibe o modal automaticamente ao carregar a página |
    | Inclui máscaras para CPF e CNPJ |
*/
document.addEventListener('DOMContentLoaded', function () {
    // Inicializa o modal
    var modalElement = document.getElementById('staticBackdrop1');
    var modal = new bootstrap.Modal(modalElement);

    // Mostra o modal na tela automaticamente
    modal.show();

    // Seleciona o formulário e o input hidden do tipo cadastro
    var form = document.getElementById('formCadastro');
    var tipoInput = document.getElementById('tipoCadastro');

    // Seleciona os campos de CPF e CNPJ
    var campoCPF = document.getElementById('campoCPF');
    var campoCNPJ = document.getElementById('campoCNPJ');        

    // Máscaras aplicadas quando o DOM estiver pronto
    $(document).ready(function () {
        $('#cpf').mask('000.000.000-00');
        $('#cnpj').mask('00.000.000/0000-00');
    });

    // Botões do modal
    document.getElementById('btnPF').addEventListener('click', function () {
    tipoInput.value = 'pf';  // seta tipo cadastro como pessoa física
    
    $('#campoCPF').removeClass('d-none'); // mostra o campo CPF
    $('#cpf').prop('require' , true); // torna o campo CPF obrigatório')

    $('#campoCNPJ').addClass('d-none'); // esconde o campo CNPJ
    $('#cnpj').prop('require' , false); // torna o campo CNPJ não obrigatório
    $('#cnpj').val(''); // limpa o campo CNPJ
    
    modal.hide();            // fecha o modal
    form.classList.remove('d-none'); // mostra o formulário
    });

    document.getElementById('btnPJ').addEventListener('click', function () {
    tipoInput.value = 'pj';  // seta tipo cadastro como pessoa jurídica


    $('#campoCNPJ').removeClass('d-none'); // mostra o campo CNPJ
    $('#cnpj').prop('require' , true); // torna o campo CNPJ obrigatório
       
    $('#campoCPF').addClass('d-none'); // esconde o campo CPF
    $('#cpf').prop('require' , false); // torna o campo CPF não obrigatório
    $('#cpf').val(''); // limpa o campo CPF

    modal.hide();            // fecha o modal
    form.classList.remove('d-none'); // mostra o formulário
    });
});
