/* Exibe a dica para inserir a senha */
/* FIXME | Exibir senha somente na pagina de cadastro não sendo necessária a exibição no login| */
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

