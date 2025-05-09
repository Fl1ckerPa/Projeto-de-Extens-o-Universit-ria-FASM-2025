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

