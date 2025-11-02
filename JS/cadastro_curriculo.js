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

    // Validação por etapa ao clicar em Próximo
    function validarCamposDaEtapa(indiceEtapa) {
      const tabIds = ['parte1','parte2','parte3'];
      const atual = document.getElementById(tabIds[indiceEtapa]);
      if (!atual) return true;
      let ok = true;
      const required = atual.querySelectorAll('[required]');
      required.forEach((field) => {
        const value = (field.value || '').toString().trim();
        // Campos file podem estar vazios se opcionais; aqui só checamos required
        if (!value) {
          field.classList.add('is-invalid');
          ok = false;
        } else {
          field.classList.remove('is-invalid');
        }
      });
      if (!ok) {
        // foca no primeiro inválido
        const firstInvalid = atual.querySelector('[required].is-invalid') || atual.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
      }
      return ok;
    }

    btnProximo.addEventListener('click', () => {
      if (validarCamposDaEtapa(etapa)) {
        irPara(etapa + 1);
      }
    });
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
      // garante validação da última etapa antes de enviar
      if (!validarCamposDaEtapa(2)) ok = false;
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

    // Auto-preenchimento de endereço via ViaCEP
    const cepInput = document.getElementById('cepPessoa');
    const ruaInput = document.getElementById('ruaPessoa');
    const bairroInput = document.getElementById('bairroPessoa');
    const cidadeInput = document.getElementById('cidadePessoa');

    if (cepInput) {
      cepInput.addEventListener('input', () => {
        let v = cepInput.value.replace(/\D/g, '');
        if (v.length > 8) v = v.slice(0, 8);
        // formata 00000-000
        cepInput.value = v.replace(/(\d{5})(\d)/, '$1-$2');
      });

      cepInput.addEventListener('blur', async () => {
        const cepNum = cepInput.value.replace(/\D/g, '');
        if (cepNum.length !== 8) return;
        try {
          const resp = await fetch(`https://viacep.com.br/ws/${cepNum}/json/`);
          const data = await resp.json();
          if (data && !data.erro) {
            if (ruaInput && !ruaInput.value) ruaInput.value = data.logradouro || '';
            if (bairroInput && !bairroInput.value) bairroInput.value = data.bairro || '';
            if (cidadeInput && !cidadeInput.value) cidadeInput.value = data.localidade || '';
          }
        } catch (_) {
          // silencioso: mantém campos como estão
        }
      });
    }
  });
  