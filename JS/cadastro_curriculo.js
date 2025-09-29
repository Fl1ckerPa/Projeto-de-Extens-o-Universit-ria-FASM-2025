document.addEventListener('DOMContentLoaded', () => {
    // Controle de etapas (tabs)
    const tabs = [
      new bootstrap.Tab(document.getElementById('tab1')),
      new bootstrap.Tab(document.getElementById('tab2')),
      new bootstrap.Tab(document.getElementById('tab3')),
    ];
    let etapa = 0;
    const btnAnterior = document.getElementById('btnAnterior');
    const btnProximo = document.getElementById('btnProximo');
    const btnEnviar = document.getElementById('btnEnviar');
  
    function atualizarControles() {
      btnAnterior.classList.toggle('d-none', etapa === 0);
      btnProximo.classList.toggle('d-none', etapa === tabs.length - 1);
      btnEnviar.classList.toggle('d-none', etapa !== tabs.length - 1);
    }
    function irPara(indice) {
      etapa = Math.max(0, Math.min(indice, tabs.length - 1));
      tabs[etapa].show();
      atualizarControles();
    }
    btnAnterior.addEventListener('click', () => irPara(etapa - 1));
    btnProximo.addEventListener('click', () => irPara(etapa + 1));
    document.getElementById('curriculoModal').addEventListener('shown.bs.modal', () => irPara(0));
  
    document.getElementById('tab1').addEventListener('shown.bs.tab', () => { etapa = 0; atualizarControles(); });
    document.getElementById('tab2').addEventListener('shown.bs.tab', () => { etapa = 1; atualizarControles(); });
    document.getElementById('tab3').addEventListener('shown.bs.tab', () => { etapa = 2; atualizarControles(); });
  
    // Experiências dinâmicas
    const btnAdd = document.getElementById('btnAddExperiencia');
    const container = document.getElementById('experienciasContainer');
    const tpl = document.getElementById('tplExperiencia');
  
    function renumerarExperiencias() {
      const titulos = container.querySelectorAll('.titulo-exp');
      titulos.forEach((el, idx) => {
        const num = String(idx + 1).padStart(2, '0');
        el.textContent = `Experiência ${num}`;
      });
    }
  
    function adicionarExperiencia() {
      const max = Number(container.getAttribute('data-max-exp') || '5');
      if (container.children.length >= max) {
        alert(`Limite máximo de ${max} experiências atingido.`);
        return;
      }
      const clone = tpl.content.firstElementChild.cloneNode(true);
      clone.querySelector('.btnRemoveExp').addEventListener('click', () => {
        clone.remove();
        renumerarExperiencias();
      });
      container.appendChild(clone);
      renumerarExperiencias();
    }
  
    adicionarExperiencia();
    btnAdd.addEventListener('click', adicionarExperiencia);
  
    // Validações de arquivo (client-side)
    const inputFoto = document.getElementById('foto');
    const inputCert = document.getElementById('certificado');
    const inputCurriculo = document.getElementById('curriculo');
    const form = document.getElementById('formCurriculo');
  
    function validarTamanho(input, maxBytes) {
      if (!input || !input.files || !input.files[0]) return true;
      return input.files[0].size <= maxBytes;
    }
    function validarExtensao(input, exts) {
      if (!input || !input.files || !input.files[0]) return true;
      const nome = input.files[0].name.toLowerCase();
      return exts.some(ext => nome.endsWith(ext));
    }
  
    form.addEventListener('submit', (e) => {
      let ok = true;
      if (!validarTamanho(inputFoto, 1 * 1024 * 1024) || !validarExtensao(inputFoto, ['.jpg','.jpeg','.png','.gif'])) {
        ok = false;
        alert('A foto deve ter até 1MB e ser JPG, JPEG, PNG ou GIF.');
      }
      if (!validarTamanho(inputCert, 5 * 1024 * 1024) || !validarExtensao(inputCert, ['.pdf','.jpg','.jpeg','.png'])) {
        ok = false;
        alert('O certificado deve ter até 5MB e ser PDF, JPG, JPEG ou PNG.');
      }
      if (!validarTamanho(inputCurriculo, 10 * 1024 * 1024) || !validarExtensao(inputCurriculo, ['.pdf','.doc','.docx','.txt'])) {
        ok = false;
        alert('O currículo deve ter até 10MB e ser PDF, DOC, DOCX ou TXT.');
      }
      if (!ok) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  });
  