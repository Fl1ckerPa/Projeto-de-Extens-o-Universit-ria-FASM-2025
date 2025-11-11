# 📚 Documentação Técnica e Plano de Testes - Conectando Talentos

**Projeto:** Sistema de Intermediação de Vagas de Emprego  
**Versão:** 3.0  
**Data:** 2025-01-XX  
**Tecnologias:** PHP 8+, MySQL 5.7+/8.0+, Bootstrap 5.3.3, JavaScript ES6+

---

## 📋 Sumário

1. [Visão Geral do Sistema](#1-visão-geral-do-sistema)
2. [Arquitetura e Fluxos](#2-arquitetura-e-fluxos)
3. [Estrutura do Banco de Dados](#3-estrutura-do-banco-de-dados)
4. [Módulo: Usuários](#4-módulo-usuários)
5. [Módulo: Vagas de Emprego](#5-módulo-vagas-de-emprego)
6. [Módulo: Candidaturas](#6-módulo-candidaturas)
7. [Módulo: Administrativo](#7-módulo-administrativo)
8. [Testes de Integração E2E](#8-testes-de-integração-e2e)
9. [Testes Não-Funcionais](#9-testes-não-funcionais)
10. [Scripts e Automação](#10-scripts-e-automação)
11. [Checklist de Aceitação](#11-checklist-de-aceitação)
12. [Matriz de Riscos](#12-matriz-de-riscos)
13. [Os 10 Testes Prioritários](#13-os-10-testes-prioritários)

---

## 1. Visão Geral do Sistema

### 1.1 Objetivo
Sistema web para intermediação entre candidatos (Pessoa Física) e empresas (Pessoa Jurídica) na cidade de Muriaé/MG, permitindo:
- Cadastro e autenticação de usuários (PF/PJ)
- Publicação e busca de vagas de emprego
- Envio e gestão de candidaturas
- Painel administrativo para moderação

### 1.2 Arquitetura
- **Front-end:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3.3
- **Back-end:** PHP 8.0+ (procedural, sem MVC completo)
- **Banco de Dados:** MySQL 5.7+/8.0+
- **Autenticação:** Sessões PHP (Session-based)
- **Bibliotecas:** AtomPHP adaptado (Database, Session, Request, Files, Validator, Helper)

### 1.3 Estrutura de Diretórios
```
Projeto-de-Extens-o-Universit-ria-FASM-2025/
├── HTML/              # Front-end (páginas HTML)
├── PHP/               # Back-end (endpoints e lógica)
│   ├── admin/         # Módulo administrativo
│   └── partials/      # Templates reutilizáveis
├── JS/                # Scripts JavaScript
├── CSS/               # Estilos
├── lib/               # Bibliotecas PHP
├── uploads/           # Arquivos enviados
└── banco de dados/    # Scripts SQL
```

---

## 2. Arquitetura e Fluxos

### 2.1 Fluxo de Autenticação
```
Usuário → HTML/login.html → POST PHP/login.php → Validação → Sessão PHP → Redirecionamento
```

### 2.2 Fluxo de Candidatura (E2E)
```
Candidato (PF) → Login → Buscar Vagas → Selecionar Vaga → Candidatar-se → 
Empresa visualiza → Empresa avalia → Status atualizado
```

### 2.3 Fluxo Administrativo
```
Admin → Login Admin → Dashboard → Gerenciar Usuários/Vagas/Candidaturas → Relatórios
```

### 2.4 Tipos de Usuário
- **PF (Pessoa Física):** Candidatos que buscam vagas
- **PJ (Pessoa Jurídica):** Empresas que publicam vagas
- **Admin:** Administradores do sistema

---

## 3. Estrutura do Banco de Dados

### 3.1 Tabelas Principais

#### `usuarios_pf` (Pessoas Físicas)
```sql
CREATE TABLE usuarios_pf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(160) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### `usuarios_pj` (Pessoas Jurídicas)
```sql
CREATE TABLE usuarios_pj (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(160) NOT NULL,
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(160) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### `administradores`
```sql
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(160) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `empresas`
```sql
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cnpj VARCHAR(14) NOT NULL UNIQUE,
    nome_social VARCHAR(255) NOT NULL,
    segmento VARCHAR(100) NOT NULL,
    endereco VARCHAR(500) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    logo VARCHAR(500),
    ativo TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `vagas`
```sql
CREATE TABLE vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    salario VARCHAR(100),
    tipo_contrato VARCHAR(50),
    data_publicacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_limite DATE NOT NULL,
    status ENUM('Aberta', 'Pausada', 'Fechada') DEFAULT 'Aberta',
    descricao TEXT NOT NULL,
    requisitos TEXT,
    beneficios TEXT,
    empresa_id INT NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);
```

#### `pessoas` (Candidatos)
```sql
CREATE TABLE pessoas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    endereco TEXT,
    ativo TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `candidaturas`
```sql
CREATE TABLE candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vaga_id INT NOT NULL,
    pessoa_id INT NOT NULL,
    status ENUM('Pendente', 'Aprovado', 'Reprovado') DEFAULT 'Pendente',
    data_candidatura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_avaliacao TIMESTAMP NULL,
    observacoes TEXT,
    FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE CASCADE,
    FOREIGN KEY (pessoa_id) REFERENCES pessoas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidatura (vaga_id, pessoa_id)
);
```

#### `curriculos`
```sql
CREATE TABLE curriculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(160) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    telefone VARCHAR(32) NOT NULL,
    email VARCHAR(160) NOT NULL,
    genero VARCHAR(20) NOT NULL,
    nascimento DATE NOT NULL,
    escolaridade VARCHAR(100) NOT NULL,
    foto VARCHAR(255) NULL,
    certificado VARCHAR(255) NULL,
    curriculo VARCHAR(255) NULL,
    experiencias JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 3.2 Queries de Verificação

#### Verificar usuários criados
```sql
SELECT id, nome, cpf, email, created_at FROM usuarios_pf;
SELECT id, nome, cnpj, email, created_at FROM usuarios_pj;
SELECT id, nome, email, ativo FROM administradores;
```

#### Verificar vagas ativas
```sql
SELECT v.id, v.titulo, v.categoria, v.status, e.nome_social as empresa
FROM vagas v
JOIN empresas e ON v.empresa_id = e.id
WHERE v.ativo = 1
ORDER BY v.data_publicacao DESC;
```

#### Verificar candidaturas
```sql
SELECT c.id, c.status, v.titulo as vaga, p.nome as candidato, c.data_candidatura
FROM candidaturas c
JOIN vagas v ON c.vaga_id = v.id
JOIN pessoas p ON c.pessoa_id = p.id
ORDER BY c.data_candidatura DESC;
```

---

## 4. Módulo: Usuários

### 4.1 Objetivo
Gerenciar cadastro, autenticação e perfil de usuários (PF, PJ, Admin).

### 4.2 Endpoints

#### 4.2.1 Login de Usuário (PF/PJ)
**Rota:** `POST /PHP/login.php`

**Headers:**
```
Content-Type: application/x-www-form-urlencoded
Cookie: PHPSESSID=<session_id> (gerado automaticamente)
```

**Body (Form Data):**
```
tipoCadastro=pf
cpf=11144477735
senha=Teste@123
```

**Resposta Esperada (Sucesso - Redirecionamento):**
- Status: 302 (Redirect)
- Location: `../HTML/index.html`
- Cookie: `PHPSESSID` definido

**Resposta Esperada (Erro - HTML):**
- Status: 200
- Body: Página HTML com mensagens de erro

**Exemplo cURL:**
```bash
curl -X POST http://localhost:8000/PHP/login.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "tipoCadastro=pf&cpf=11144477735&senha=Teste@123" \
  -c cookies.txt \
  -v
```

**Verificação SQL:**
```sql
-- Verificar sessão (não há tabela de sessões, apenas verificar se usuário existe)
SELECT id, nome, cpf FROM usuarios_pf WHERE cpf = '111.444.777-35';
```

---

#### 4.2.2 Cadastro de Usuário PF
**Rota:** `POST /PHP/cadastro.php`

**Body (Form Data):**
```
nome=João da Silva
tipoCadastro=pf
cpf=12345678909
email=joao.silva@test.local
senha=Senha@123
senhaverif=Senha@123
```

**Resposta Esperada:**
- Status: 200
- Body: HTML com mensagem de sucesso

**Exemplo cURL:**
```bash
curl -X POST http://localhost:8000/PHP/cadastro.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "nome=João+da+Silva&tipoCadastro=pf&cpf=12345678909&email=joao.silva@test.local&senha=Senha@123&senhaverif=Senha@123" \
  -v
```

**Verificação SQL:**
```sql
SELECT id, nome, cpf, email, created_at FROM usuarios_pf WHERE email = 'joao.silva@test.local';
-- Verificar se senha está hasheada (não deve aparecer em texto plano)
SELECT id, nome, senha FROM usuarios_pf WHERE email = 'joao.silva@test.local';
```

---

#### 4.2.3 Cadastro de Empresa (PJ)
**Rota:** `POST /PHP/cadastro_empresa.php`

**Body (Form Data):**
```
cnpj=12345678000195
nome_social=Empresa Teste LTDA
segmento=tecnologia
endereco=Rua Teste, 123
cidade=Muriaé
estado=MG
email=contato@empresateste.com
telefone=32999999999
sobre=Empresa de teste para demonstração
logo=<arquivo>
```

**Resposta Esperada:**
- Status: 200
- Body: JSON `{"sucesso": true, "mensagem": "...", "dados": {...}}`

**Exemplo cURL:**
```bash
curl -X POST http://localhost:8000/PHP/cadastro_empresa.php \
  -F "cnpj=12345678000195" \
  -F "nome_social=Empresa Teste LTDA" \
  -F "segmento=tecnologia" \
  -F "endereco=Rua Teste, 123" \
  -F "cidade=Muriaé" \
  -F "estado=MG" \
  -F "email=contato@empresateste.com" \
  -F "telefone=32999999999" \
  -F "sobre=Empresa de teste" \
  -F "logo=@logo.png" \
  -v
```

---

#### 4.2.4 Visualizar Perfil
**Rota:** `GET /PHP/perfil.php?acao=visualizar`

**Headers:**
```
Cookie: PHPSESSID=<session_id> (requer autenticação)
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Perfil carregado!",
  "dados": {
    "usuario": {
      "id": 1,
      "nome": "João da Silva",
      "cpf": "123.456.789-09",
      "email": "joao.silva@test.local"
    },
    "curriculo": {...}
  }
}
```

**Exemplo cURL:**
```bash
curl -X GET "http://localhost:8000/PHP/perfil.php?acao=visualizar" \
  -H "Cookie: PHPSESSID=<session_id>" \
  -v
```

---

#### 4.2.5 Atualizar Perfil
**Rota:** `POST /PHP/perfil.php`

**Body (Form Data):**
```
acao=atualizar
nome=João Silva Atualizado
email=joao.silva.novo@test.local
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Perfil atualizado com sucesso!"
}
```

---

#### 4.2.6 Alterar Senha
**Rota:** `POST /PHP/configuracoes.php`

**Body (Form Data):**
```
acao=alterar_senha
senha_atual=Senha@123
senha_nova=NovaSenha@456
senha_nova_confirm=NovaSenha@456
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Senha alterada com sucesso!"
}
```

---

### 4.3 Casos de Teste Manuais

#### Caso 1: Cadastro PF Válido ✅
**Dados de Teste:**
- Nome: `Usuário Teste PF`
- CPF: `11144477735` (válido)
- Email: `user.pf.test@local.test`
- Senha: `Teste@123`
- Confirmação: `Teste@123`

**Passos:**
1. Acessar `HTML/cadastro.html`
2. Selecionar "Pessoa Física"
3. Preencher todos os campos
4. Submeter formulário

**Resultado Esperado:**
- ✅ Mensagem de sucesso exibida
- ✅ Registro criado em `usuarios_pf`
- ✅ Senha hasheada no banco
- ✅ Email validado

**Verificação SQL:**
```sql
SELECT id, nome, cpf, email, created_at 
FROM usuarios_pf 
WHERE email = 'user.pf.test@local.test';
-- Verificar que senha está hasheada
SELECT id, nome, LEFT(senha, 20) as senha_hash_preview 
FROM usuarios_pf 
WHERE email = 'user.pf.test@local.test';
```

---

#### Caso 2: Cadastro PF com CPF Inválido ❌
**Dados de Teste:**
- CPF: `123456789` (inválido - menos de 11 dígitos)

**Passos:**
1. Preencher formulário com CPF inválido
2. Submeter

**Resultado Esperado:**
- ❌ Mensagem de erro: "CPF inválido"
- ❌ Nenhum registro criado no banco

**Verificação SQL:**
```sql
-- Não deve existir registro
SELECT COUNT(*) as total FROM usuarios_pf WHERE cpf LIKE '%123456789%';
-- Esperado: 0
```

---

#### Caso 3: Login com Senha Incorreta ❌
**Dados de Teste:**
- CPF: `11144477735`
- Senha: `SenhaErrada@123`

**Passos:**
1. Acessar `HTML/login.html`
2. Preencher CPF e senha incorreta
3. Submeter

**Resultado Esperado:**
- ❌ Status: 200 (HTML de erro)
- ❌ Mensagem: "Senha incorreta"
- ❌ Nenhuma sessão criada

**Verificação:**
- Verificar que não há cookie `PHPSESSID` com dados de usuário

---

#### Caso 4: Login Válido ✅
**Dados de Teste:**
- CPF: `11144477735`
- Senha: `Teste@123`

**Passos:**
1. Acessar `HTML/login.html`
2. Preencher dados corretos
3. Submeter

**Resultado Esperado:**
- ✅ Status: 302 (Redirect)
- ✅ Redirecionamento para `HTML/index.html`
- ✅ Cookie `PHPSESSID` definido
- ✅ Sessão criada com `user_id`, `user_type`, `user_nome`

**Verificação:**
```sql
-- Verificar que usuário existe
SELECT id, nome, cpf FROM usuarios_pf WHERE cpf = '111.444.777-35';
```

---

#### Caso 5: Teste de Injeção SQL 🔒
**Dados de Teste:**
- Email: `' OR '1'='1`
- Senha: `qualquer`

**Passos:**
1. Tentar login com payload SQL injection
2. Verificar resposta

**Resultado Esperado:**
- ✅ Erro de validação (não executa SQL malicioso)
- ✅ Prepared statements devem prevenir

**Verificação:**
```sql
-- Não deve haver login bem-sucedido
-- Verificar logs de erro (se houver)
```

---

#### Caso 6: Upload de Arquivo Malicioso 🔒
**Dados de Teste:**
- Arquivo: `malware.php` renomeado para `malware.jpg`

**Passos:**
1. Tentar upload em cadastro de empresa
2. Verificar validação

**Resultado Esperado:**
- ✅ Validação de extensão: bloqueia `.php`
- ✅ Validação MIME type: bloqueia `application/x-php`
- ✅ Arquivo não é salvo

---

### 4.4 Critérios de Aceitação do Módulo

- [x] Cadastro PF funcional com validação de CPF
- [x] Cadastro PJ funcional com validação de CNPJ
- [x] Login PF/PJ funcional
- [x] Senhas hasheadas (password_hash)
- [x] Sessão PHP funcional
- [x] Validação de dados no servidor
- [x] Upload seguro de arquivos
- [x] Edição de perfil implementada
- [x] Alteração de senha implementada
- [ ] Logout implementado (verificar se existe)

---

## 5. Módulo: Vagas de Emprego

### 5.1 Objetivo
Permitir que empresas publiquem vagas e candidatos busquem e visualizem vagas disponíveis.

### 5.2 Endpoints

#### 5.2.1 Listar Vagas Públicas
**Rota:** `GET /PHP/vagas.php?acao=listar`

**Parâmetros Query:**
- `categoria` (opcional): `tecnologia`, `comercio`, `saude`, etc.
- `localidade` (opcional): `Muriaé`, `MG`, etc.
- `tipo` (opcional): `CLT`, `PJ`, `Estágio`, etc.
- `salario_min` (opcional): valor mínimo
- `pagina` (opcional): número da página (padrão: 1)
- `por_pagina` (opcional): itens por página (padrão: 12)

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Vagas listadas com sucesso!",
  "dados": [
    {
      "id": 1,
      "titulo": "Desenvolvedor Full Stack",
      "categoria": "tecnologia",
      "salario": "R$ 5.000,00",
      "tipo_contrato": "CLT",
      "empresa_nome": "Tech Solutions",
      "empresa_cidade": "Muriaé",
      "empresa_estado": "MG",
      "total_candidatos": 5,
      "data_publicacao": "2025-01-15 10:30:00",
      "data_limite": "2025-02-15"
    }
  ],
  "paginacao": {
    "total": 25,
    "pagina": 1,
    "por_pagina": 12,
    "total_paginas": 3
  }
}
```

**Exemplo cURL:**
```bash
curl -X GET "http://localhost:8000/PHP/vagas.php?acao=listar&categoria=tecnologia&pagina=1" \
  -H "Accept: application/json" \
  -v
```

---

#### 5.2.2 Detalhes de Vaga
**Rota:** `GET /PHP/vagas.php?acao=detalhes&id={vaga_id}`

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Vaga encontrada!",
  "dados": {
    "vaga": {
      "id": 1,
      "titulo": "Desenvolvedor Full Stack",
      "descricao": "...",
      "requisitos": "...",
      "beneficios": "...",
      "empresa_nome": "Tech Solutions",
      "ja_candidatou": false
    }
  }
}
```

**Exemplo cURL:**
```bash
curl -X GET "http://localhost:8000/PHP/vagas.php?acao=detalhes&id=1" \
  -H "Accept: application/json" \
  -v
```

---

#### 5.2.3 Criar Vaga (Empresa)
**Rota:** `POST /PHP/gestao_vagas_empresa.php`

**Headers:**
```
Content-Type: application/x-www-form-urlencoded
Cookie: PHPSESSID=<session_id> (empresa logada)
```

**Body (Form Data):**
```
acao=criar_vaga
titulo=Desenvolvedor Frontend
categoria=tecnologia
salario=R$ 4.000,00
tipoContrato=CLT
dataLimite=2025-03-01
descricao=Desenvolvedor para trabalhar com React e TypeScript. Experiência mínima de 2 anos.
requisitos=Conhecimento em React, TypeScript, Git
beneficios=Vale refeição, plano de saúde
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Vaga criada com sucesso!",
  "dados": {
    "vaga_id": 5
  }
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost:8000/PHP/gestao_vagas_empresa.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Cookie: PHPSESSID=<session_id>" \
  -d "acao=criar_vaga&titulo=Desenvolvedor+Frontend&categoria=tecnologia&salario=R$+4.000,00&tipoContrato=CLT&dataLimite=2025-03-01&descricao=Desenvolvedor+para+trabalhar+com+React&requisitos=React+TypeScript&beneficios=Vale+refeição" \
  -v
```

**Verificação SQL:**
```sql
SELECT id, titulo, categoria, status, empresa_id, data_publicacao
FROM vagas
WHERE empresa_id = (SELECT id FROM empresas WHERE cnpj = '12345678000195')
ORDER BY data_publicacao DESC
LIMIT 1;
```

---

#### 5.2.4 Editar Vaga
**Rota:** `POST /PHP/gestao_vagas_empresa.php`

**Body:**
```
acao=editar_vaga
id=5
titulo=Desenvolvedor Frontend Sênior
salario=R$ 5.000,00
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Vaga atualizada com sucesso!"
}
```

---

#### 5.2.5 Excluir Vaga
**Rota:** `POST /PHP/gestao_vagas_empresa.php`

**Body:**
```
acao=excluir_vaga
id=5
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Vaga excluída com sucesso!"
}
```

**Verificação SQL:**
```sql
-- Verificar que vaga foi marcada como inativa ou excluída
SELECT id, titulo, ativo FROM vagas WHERE id = 5;
-- Esperado: ativo = 0 ou registro não existe
```

---

#### 5.2.6 Listar Candidatos de uma Vaga (Empresa)
**Rota:** `POST /PHP/gestao_vagas_empresa.php`

**Body:**
```
acao=listar_candidatos
vaga_id=1
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Candidatos listados com sucesso!",
  "dados": {
    "candidatos": [
      {
        "id": 1,
        "nome": "João Silva",
        "email": "joao.silva@email.com",
        "telefone": "(32) 99999-9999",
        "status": "Pendente",
        "data_candidatura": "2025-01-16 10:30:00"
      }
    ]
  }
}
```

---

### 5.3 Casos de Teste Manuais

#### Caso 1: Criar Vaga Válida ✅
**Dados de Teste:**
- Título: `Vaga Teste Desenvolvedor`
- Categoria: `tecnologia`
- Salário: `R$ 5.000,00`
- Tipo: `CLT`
- Data Limite: `2025-03-01`
- Descrição: `Vaga de teste para desenvolvedor com experiência em PHP e MySQL.`

**Passos:**
1. Fazer login como empresa (PJ)
2. Acessar `HTML/gestao_vagas_empresa.html`
3. Preencher formulário de nova vaga
4. Submeter

**Resultado Esperado:**
- ✅ Vaga criada com sucesso
- ✅ Status: "Aberta"
- ✅ `ativo = 1`
- ✅ `empresa_id` vinculado à empresa logada

**Verificação SQL:**
```sql
SELECT v.id, v.titulo, v.status, v.ativo, e.nome_social as empresa
FROM vagas v
JOIN empresas e ON v.empresa_id = e.id
WHERE v.titulo = 'Vaga Teste Desenvolvedor';
```

---

#### Caso 2: Criar Vaga com Data Limite Passada ❌
**Dados de Teste:**
- Data Limite: `2024-01-01` (data passada)

**Resultado Esperado:**
- ❌ Erro: "Data limite deve ser futura"
- ❌ Vaga não criada

---

#### Caso 3: Buscar Vagas com Filtro ✅
**Dados de Teste:**
- Categoria: `tecnologia`
- Localidade: `Muriaé`

**Passos:**
1. Acessar `HTML/buscar_vagas.html`
2. Aplicar filtros
3. Verificar resultados

**Resultado Esperado:**
- ✅ Apenas vagas de tecnologia em Muriaé
- ✅ JSON retornado com dados corretos

**Verificação SQL:**
```sql
SELECT v.id, v.titulo, v.categoria, e.cidade
FROM vagas v
JOIN empresas e ON v.empresa_id = e.id
WHERE v.categoria = 'tecnologia' 
  AND e.cidade = 'Muriaé'
  AND v.status = 'Aberta'
  AND v.ativo = 1;
```

---

### 5.4 Critérios de Aceitação do Módulo

- [x] Criar vaga (empresa)
- [x] Editar vaga (empresa)
- [x] Excluir vaga (empresa)
- [x] Listar vagas públicas (candidatos)
- [x] Detalhes de vaga
- [x] Filtros funcionais (categoria, localidade, tipo)
- [x] Paginação implementada
- [x] Validação de propriedade (empresa só gerencia suas vagas)
- [ ] Busca por texto (se implementada)

---

## 6. Módulo: Candidaturas

### 6.1 Objetivo
Permitir que candidatos se candidatem a vagas e empresas gerenciem candidaturas.

### 6.2 Endpoints

#### 6.2.1 Enviar Candidatura
**Rota:** `POST /PHP/candidaturas.php`

**Headers:**
```
Content-Type: application/x-www-form-urlencoded
Cookie: PHPSESSID=<session_id> (candidato logado)
```

**Body (Form Data):**
```
acao=enviar
vaga_id=1
```

**Resposta Esperada (Sucesso):**
```json
{
  "sucesso": true,
  "mensagem": "Candidatura enviada com sucesso!",
  "dados": {
    "candidatura_id": 5,
    "vaga_id": 1
  }
}
```

**Resposta Esperada (Erro - Já Candidatou):**
```json
{
  "sucesso": false,
  "mensagem": "Você já se candidatou a esta vaga"
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost:8000/PHP/candidaturas.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Cookie: PHPSESSID=<session_id>" \
  -d "acao=enviar&vaga_id=1" \
  -v
```

**Verificação SQL:**
```sql
SELECT c.id, c.status, v.titulo as vaga, p.nome as candidato, c.data_candidatura
FROM candidaturas c
JOIN vagas v ON c.vaga_id = v.id
JOIN pessoas p ON c.pessoa_id = p.id
WHERE c.vaga_id = 1
ORDER BY c.data_candidatura DESC;
```

---

#### 6.2.2 Listar Minhas Candidaturas
**Rota:** `GET /PHP/candidaturas.php?acao=minhas`

**Parâmetros Query:**
- `status` (opcional): `todas`, `pendentes`, `aprovadas`, `reprovadas`

**Headers:**
```
Cookie: PHPSESSID=<session_id> (candidato logado)
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Candidaturas listadas com sucesso!",
  "dados": {
    "candidaturas": [
      {
        "id": 1,
        "vaga_id": 1,
        "vaga_titulo": "Desenvolvedor Full Stack",
        "empresa_nome": "Tech Solutions",
        "status": "Pendente",
        "data_candidatura": "2025-01-16 10:30:00"
      }
    ]
  }
}
```

**Exemplo cURL:**
```bash
curl -X GET "http://localhost:8000/PHP/candidaturas.php?acao=minhas&status=pendentes" \
  -H "Cookie: PHPSESSID=<session_id>" \
  -H "Accept: application/json" \
  -v
```

---

#### 6.2.3 Detalhes de Candidatura
**Rota:** `GET /PHP/candidaturas.php?acao=detalhes&id={candidatura_id}`

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Candidatura encontrada!",
  "dados": {
    "candidatura": {
      "id": 1,
      "vaga_titulo": "Desenvolvedor Full Stack",
      "status": "Pendente",
      "data_candidatura": "2025-01-16 10:30:00",
      "empresa_nome": "Tech Solutions"
    }
  }
}
```

---

#### 6.2.4 Avaliar Candidato (Empresa)
**Rota:** `POST /PHP/gestao_vagas_empresa.php`

**Body:**
```
acao=avaliar_candidato
candidatura_id=1
status=Aprovado
observacoes=Candidato aprovado para próxima etapa
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Candidato avaliado com sucesso!"
}
```

**Verificação SQL:**
```sql
SELECT id, status, data_avaliacao, observacoes
FROM candidaturas
WHERE id = 1;
-- Verificar que status = 'Aprovado' e data_avaliacao foi preenchida
```

---

### 6.3 Casos de Teste Manuais

#### Caso 1: Enviar Candidatura Válida ✅
**Dados de Teste:**
- Vaga ID: `1`
- Candidato: Logado como PF

**Passos:**
1. Fazer login como candidato (PF)
2. Acessar `HTML/buscar_vagas.html`
3. Selecionar uma vaga
4. Clicar em "Candidatar-se"

**Resultado Esperado:**
- ✅ Candidatura criada com status "Pendente"
- ✅ `data_candidatura` preenchida
- ✅ `vaga_id` e `pessoa_id` vinculados

**Verificação SQL:**
```sql
SELECT c.*, v.titulo, p.nome
FROM candidaturas c
JOIN vagas v ON c.vaga_id = v.id
JOIN pessoas p ON c.pessoa_id = p.id
WHERE c.vaga_id = 1 AND c.pessoa_id = (SELECT id FROM usuarios_pf WHERE cpf = '111.444.777-35');
```

---

#### Caso 2: Tentar Candidatar-se Duas Vezes ❌
**Passos:**
1. Candidatar-se à vaga 1
2. Tentar candidatar-se novamente à mesma vaga

**Resultado Esperado:**
- ❌ Erro: "Você já se candidatou a esta vaga"
- ❌ Apenas uma candidatura no banco

**Verificação SQL:**
```sql
-- Deve haver apenas 1 registro
SELECT COUNT(*) as total
FROM candidaturas
WHERE vaga_id = 1 AND pessoa_id = <pessoa_id>;
-- Esperado: 1
```

---

#### Caso 3: Avaliar Candidato (Empresa) ✅
**Passos:**
1. Login como empresa
2. Acessar gestão de vagas
3. Visualizar candidatos de uma vaga
4. Avaliar candidato como "Aprovado"

**Resultado Esperado:**
- ✅ Status atualizado para "Aprovado"
- ✅ `data_avaliacao` preenchida
- ✅ Observações salvas

---

### 6.4 Critérios de Aceitação do Módulo

- [x] Enviar candidatura
- [x] Listar minhas candidaturas (candidato)
- [x] Detalhes de candidatura
- [x] Avaliar candidato (empresa)
- [x] Status de candidatura (Pendente/Aprovado/Reprovado)
- [x] Prevenção de candidatura duplicada
- [x] Validação de propriedade (empresa só vê candidatos de suas vagas)

---

## 7. Módulo: Administrativo

### 7.1 Objetivo
Painel administrativo para gerenciar usuários, vagas, candidaturas e visualizar relatórios.

### 7.2 Endpoints

#### 7.2.1 Login Admin
**Rota:** `POST /PHP/admin/login.php`

**Body (Form Data):**
```
email=admin@descubramuriae.local
senha=Admin@123
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Login realizado com sucesso!",
  "dados": {
    "id": 1,
    "nome": "Administrador Demo",
    "email": "admin@descubramuriae.local"
  }
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost:8000/PHP/admin/login.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=admin@descubramuriae.local&senha=Admin@123" \
  -c admin_cookies.txt \
  -v
```

---

#### 7.2.2 Dashboard Admin
**Rota:** `GET /PHP/admin/dashboard.php`

**Headers:**
```
Cookie: PHPSESSID=<session_id> (admin logado)
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Dados carregados com sucesso!",
  "dados": {
    "metricas": {
      "usuarios": {
        "total_pf": 10,
        "total_pj": 5,
        "total": 15
      },
      "vagas": {
        "total": 25,
        "abertas": 20,
        "fechadas": 5
      },
      "candidaturas": {
        "total": 50,
        "pendentes": 30,
        "aprovadas": 15,
        "reprovadas": 5
      },
      "empresas": 5,
      "curriculos": 8
    },
    "vagas_recentes": [...]
  }
}
```

**Exemplo cURL:**
```bash
curl -X GET http://localhost:8000/PHP/admin/dashboard.php \
  -H "Cookie: PHPSESSID=<admin_session_id>" \
  -H "Accept: application/json" \
  -v
```

---

#### 7.2.3 Listar Usuários (Admin)
**Rota:** `GET /PHP/admin/usuarios.php?acao=listar&tipo=todos`

**Parâmetros Query:**
- `tipo`: `pf`, `pj`, `todos`

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Usuários listados com sucesso!",
  "dados": {
    "usuarios": [
      {
        "id": 1,
        "nome": "João Silva",
        "cpf": "111.444.777-35",
        "email": "joao@test.local",
        "tipo": "pf",
        "created_at": "2025-01-15 10:30:00"
      }
    ]
  }
}
```

---

#### 7.2.4 Moderação de Vagas
**Rota:** `POST /PHP/admin/vagas.php`

**Body:**
```
acao=aprovar
id=5
```

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Vaga aprovada com sucesso!"
}
```

---

#### 7.2.5 Relatórios
**Rota:** `GET /PHP/admin/relatorios.php?tipo=geral`

**Parâmetros Query:**
- `tipo`: `geral`, `vagas`, `candidaturas`

**Resposta Esperada:**
```json
{
  "sucesso": true,
  "mensagem": "Relatório geral gerado!",
  "dados": {
    "total_usuarios": 15,
    "total_vagas": 25,
    "total_candidaturas": 50,
    "total_empresas": 5,
    "data_geracao": "2025-01-20 14:30:00"
  }
}
```

---

### 7.3 Casos de Teste Manuais

#### Caso 1: Login Admin ✅
**Dados de Teste:**
- Email: `admin@descubramuriae.local`
- Senha: `Admin@123`

**Passos:**
1. Acessar endpoint de login admin
2. Enviar credenciais
3. Verificar resposta

**Resultado Esperado:**
- ✅ Login bem-sucedido
- ✅ Sessão criada com `user_type = 'admin'`
- ✅ JSON de sucesso retornado

---

#### Caso 2: Acesso Não Autorizado ❌
**Passos:**
1. Tentar acessar `PHP/admin/dashboard.php` sem autenticação

**Resultado Esperado:**
- ❌ Status: 401 ou 403
- ❌ Mensagem: "Acesso não autorizado"

---

#### Caso 3: Aprovar Vaga (Admin) ✅
**Passos:**
1. Login como admin
2. Acessar moderação de vagas
3. Aprovar uma vaga pendente

**Resultado Esperado:**
- ✅ Vaga marcada como `ativo = 1`
- ✅ Status atualizado

---

### 7.4 Critérios de Aceitação do Módulo

- [x] Login admin funcional
- [x] Dashboard com métricas
- [x] Listar usuários (PF/PJ)
- [x] Moderação de vagas
- [x] Visualizar candidaturas
- [x] Relatórios básicos
- [x] Controle de acesso (apenas admin)

---

## 8. Testes de Integração E2E

### 8.1 Teste E2E 1: Fluxo Completo de Candidatura

**Cenário:** Candidato se registra, busca vaga, se candidata, empresa visualiza e avalia.

**Passos:**

1. **Cadastro de Candidato:**
   ```bash
   curl -X POST http://localhost:8000/PHP/cadastro.php \
     -d "nome=Candidato Teste&tipoCadastro=pf&cpf=99988877766&email=candidato.test@local&senha=Teste@123&senhaverif=Teste@123"
   ```
   **Verificação SQL:**
   ```sql
   SELECT id, nome, cpf, email FROM usuarios_pf WHERE cpf = '999.888.777-66';
   ```

2. **Login de Candidato:**
   ```bash
   curl -X POST http://localhost:8000/PHP/login.php \
     -d "tipoCadastro=pf&cpf=99988877766&senha=Teste@123" \
     -c candidato_cookies.txt
   ```

3. **Buscar Vagas:**
   ```bash
   curl -X GET "http://localhost:8000/PHP/vagas.php?acao=listar" \
     -b candidato_cookies.txt
   ```

4. **Enviar Candidatura:**
   ```bash
   curl -X POST http://localhost:8000/PHP/candidaturas.php \
     -b candidato_cookies.txt \
     -d "acao=enviar&vaga_id=1"
   ```
   **Verificação SQL:**
   ```sql
   SELECT c.*, v.titulo, p.nome
   FROM candidaturas c
   JOIN vagas v ON c.vaga_id = v.id
   JOIN pessoas p ON c.pessoa_id = p.id
   WHERE c.vaga_id = 1 AND p.email = 'candidato.test@local';
   ```

5. **Login Empresa:**
   ```bash
   curl -X POST http://localhost:8000/PHP/login.php \
     -d "tipoCadastro=pj&cnpj=11222333000181&senha=Teste@123" \
     -c empresa_cookies.txt
   ```

6. **Visualizar Candidatos:**
   ```bash
   curl -X POST http://localhost:8000/PHP/gestao_vagas_empresa.php \
     -b empresa_cookies.txt \
     -d "acao=listar_candidatos&vaga_id=1"
   ```

7. **Avaliar Candidato:**
   ```bash
   curl -X POST http://localhost:8000/PHP/gestao_vagas_empresa.php \
     -b empresa_cookies.txt \
     -d "acao=avaliar_candidato&candidatura_id=<id>&status=Aprovado&observacoes=Aprovado"
   ```
   **Verificação SQL:**
   ```sql
   SELECT status, data_avaliacao, observacoes
   FROM candidaturas
   WHERE id = <candidatura_id>;
   ```

**Resultado Esperado:**
- ✅ Todos os passos executados com sucesso
- ✅ Dados persistidos corretamente
- ✅ Status atualizado

---

### 8.2 Teste E2E 2: Empresa Publica Vaga → Admin Visualiza

**Passos:**

1. **Empresa cria vaga** (ver 5.2.3)
2. **Admin visualiza no dashboard:**
   ```bash
   curl -X GET http://localhost:8000/PHP/admin/dashboard.php \
     -b admin_cookies.txt
   ```
3. **Admin visualiza relatório:**
   ```bash
   curl -X GET "http://localhost:8000/PHP/admin/relatorios.php?tipo=vagas" \
     -b admin_cookies.txt
   ```

**Resultado Esperado:**
- ✅ Vaga aparece nas métricas
- ✅ Relatório inclui a vaga

---

## 9. Testes Não-Funcionais

### 9.1 Performance

#### Teste de Carga Leve
```bash
# Instalar Apache Bench (ab)
# Ubuntu/Debian: sudo apt-get install apache2-utils
# Windows: baixar de https://www.apachehaus.com/cgi-bin/download.plx

# Teste de listagem de vagas (50 requisições, 10 concorrentes)
ab -n 50 -c 10 http://localhost:8000/PHP/vagas.php?acao=listar

# Resultado esperado:
# - Tempo médio de resposta < 500ms
# - Taxa de sucesso > 95%
```

### 9.2 Segurança

#### Verificar Headers HTTP
```bash
curl -I http://localhost:8000/HTML/login.html
```

**Verificações:**
- ✅ Não expor versão do PHP (`X-Powered-By`)
- ✅ Headers de segurança (se configurados): `X-Frame-Options`, `X-Content-Type-Options`

#### Teste de Injeção SQL
```bash
# Tentar login com payload SQL
curl -X POST http://localhost:8000/PHP/login.php \
  -d "tipoCadastro=pf&cpf=' OR '1'='1&senha=qualquer"
```

**Resultado Esperado:**
- ✅ Erro de validação (não executa SQL malicioso)
- ✅ Prepared statements devem prevenir

#### Teste de Upload Malicioso
```bash
# Criar arquivo PHP disfarçado
echo "<?php phpinfo(); ?>" > malware.jpg

# Tentar upload
curl -X POST http://localhost:8000/PHP/cadastro_empresa.php \
  -F "logo=@malware.jpg" \
  -F "cnpj=12345678000195" \
  -F "nome_social=Teste"
```

**Resultado Esperado:**
- ✅ Validação de extensão bloqueia
- ✅ Validação MIME type bloqueia
- ✅ Arquivo não é salvo

### 9.3 Backup e Restore

#### Backup do Banco
```bash
mysqldump -u root -p descubra_muriae > backup_$(date +%Y%m%d).sql
```

#### Restore
```bash
mysql -u root -p descubra_muriae < backup_20250120.sql
```

### 9.4 Logs

**Localização:**
- PHP errors: `php.ini` → `error_log`
- Apache/Nginx: `/var/log/apache2/` ou `/var/log/nginx/`
- Application: Verificar se há logs customizados

**O que procurar:**
- Erros de SQL
- Falhas de autenticação
- Uploads falhados
- Erros 500

---

## 10. Scripts e Automação

### 10.1 Script de Popular Banco de Dados

**Arquivo:** `scripts/popular_banco_teste.sh`

```bash
#!/bin/bash

# Configurações
DB_HOST="localhost"
DB_USER="root"
DB_PASS=""
DB_NAME="descubra_muriae"

# Executar migrate
php PHP/migrate.php

# Inserir dados adicionais de teste
mysql -u $DB_USER -p$DB_PASS $DB_NAME <<EOF

-- Usuários PF de teste
INSERT IGNORE INTO usuarios_pf (nome, cpf, senha, email) VALUES
('Candidato 01', '11111111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'candidato01@test.local'),
('Candidato 02', '22222222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'candidato02@test.local');

-- Empresas de teste
INSERT IGNORE INTO empresas (cnpj, nome_social, segmento, endereco, cidade, estado, email, telefone, sobre, ativo) VALUES
('11111111000111', 'Empresa Teste 01', 'tecnologia', 'Rua Teste, 111', 'Muriaé', 'MG', 'empresa01@test.local', '32999991111', 'Empresa de teste', 1),
('22222222000122', 'Empresa Teste 02', 'comercio', 'Rua Teste, 222', 'Muriaé', 'MG', 'empresa02@test.local', '32999992222', 'Empresa de teste', 1);

-- Vagas de teste
INSERT IGNORE INTO vagas (titulo, categoria, salario, tipo_contrato, data_limite, status, descricao, requisitos, empresa_id, ativo) VALUES
('Vaga Teste 01', 'tecnologia', 'R$ 5.000,00', 'CLT', '2025-03-01', 'Aberta', 'Descrição da vaga teste 01', 'Requisitos da vaga', 1, 1),
('Vaga Teste 02', 'comercio', 'R$ 2.500,00', 'CLT', '2025-02-15', 'Aberta', 'Descrição da vaga teste 02', 'Requisitos da vaga', 2, 1);

EOF

echo "Banco de dados populado com sucesso!"
```

### 10.2 Collection Postman (Resumo)

**Endpoints Prioritários:**

1. **Autenticação:**
   - POST `/PHP/login.php` (PF/PJ)
   - POST `/PHP/admin/login.php` (Admin)

2. **Vagas:**
   - GET `/PHP/vagas.php?acao=listar`
   - POST `/PHP/gestao_vagas_empresa.php` (criar)

3. **Candidaturas:**
   - POST `/PHP/candidaturas.php` (enviar)
   - GET `/PHP/candidaturas.php?acao=minhas`

4. **Admin:**
   - GET `/PHP/admin/dashboard.php`
   - GET `/PHP/admin/usuarios.php?acao=listar`

**Variáveis de Ambiente Postman:**
```
base_url: http://localhost:8000
session_cookie: PHPSESSID=<valor>
```

---

## 11. Checklist de Aceitação

### 11.1 Pré-requisitos
- [ ] PHP 8.0+ instalado
- [ ] MySQL 5.7+/8.0+ instalado e rodando
- [ ] Extensões PHP: `pdo_mysql`, `mbstring`, `fileinfo`
- [ ] Servidor web configurado (Apache/Nginx) ou PHP built-in server

### 11.2 Configuração Inicial
- [ ] Configurar `lib/config.php` (DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE)
- [ ] Criar banco de dados: `descubra_muriae`
- [ ] Executar `PHP/migrate.php`
- [ ] Verificar criação de tabelas
- [ ] Verificar usuários de teste criados

### 11.3 Testes Funcionais
- [ ] **Módulo Usuários:**
  - [ ] Cadastro PF funciona
  - [ ] Cadastro PJ funciona
  - [ ] Login PF funciona
  - [ ] Login PJ funciona
  - [ ] Login Admin funciona
  - [ ] Edição de perfil funciona
  - [ ] Alteração de senha funciona

- [ ] **Módulo Vagas:**
  - [ ] Criar vaga (empresa) funciona
  - [ ] Editar vaga funciona
  - [ ] Excluir vaga funciona
  - [ ] Listar vagas públicas funciona
  - [ ] Filtros funcionam
  - [ ] Detalhes de vaga funcionam

- [ ] **Módulo Candidaturas:**
  - [ ] Enviar candidatura funciona
  - [ ] Listar minhas candidaturas funciona
  - [ ] Avaliar candidato (empresa) funciona
  - [ ] Prevenção de duplicata funciona

- [ ] **Módulo Admin:**
  - [ ] Dashboard carrega métricas
  - [ ] Listar usuários funciona
  - [ ] Moderação de vagas funciona
  - [ ] Relatórios funcionam

### 11.4 Testes de Segurança
- [ ] Senhas hasheadas (não em texto plano)
- [ ] Prepared statements (proteção SQL injection)
- [ ] Validação de upload (extensão e MIME)
- [ ] Controle de acesso (middleware)
- [ ] Sessões seguras

### 11.5 Testes de Integração
- [ ] Fluxo E2E 1 (candidato → empresa) funciona
- [ ] Fluxo E2E 2 (empresa → admin) funciona

---

## 12. Matriz de Riscos

### 12.1 Riscos Críticos

| Risco | Impacto | Probabilidade | Mitigação | Prioridade |
|-------|---------|---------------|-----------|------------|
| **SQL Injection** | Alto | Baixa | ✅ Prepared statements implementados | ✅ Mitigado |
| **Sessão não expira** | Médio | Média | Implementar timeout de sessão | 🔴 Alta |
| **Upload de arquivos maliciosos** | Alto | Média | ✅ Validação MIME + extensão | ⚠️ Revisar |
| **Falta de CSRF protection** | Médio | Média | Implementar tokens CSRF | 🔴 Alta |
| **Senhas em texto plano** | Crítico | Baixa | ✅ password_hash implementado | ✅ Mitigado |

### 12.2 Riscos Altos

| Risco | Impacto | Probabilidade | Mitigação | Prioridade |
|-------|---------|---------------|-----------|------------|
| **Falta de validação de permissões** | Médio | Média | ✅ Middleware implementado | ⚠️ Revisar |
| **Rate limiting não implementado** | Baixo | Baixa | Implementar rate limiting | 🟡 Média |
| **Logs de erro expostos** | Médio | Baixa | Configurar produção (display_errors=off) | 🟡 Média |

### 12.3 Plano de Mitigação

#### 1. Sessão não expira
**Ação:** Implementar timeout de sessão em `lib/Session.php`
```php
// Adicionar verificação de expiração
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    session_destroy();
    header('Location: /HTML/login.html');
    exit;
}
$_SESSION['last_activity'] = time();
```

#### 2. CSRF Protection
**Ação:** Implementar tokens CSRF
- Gerar token na sessão
- Incluir token em formulários
- Validar token em POST

#### 3. Rate Limiting
**Ação:** Implementar limitação de requisições
- Usar Redis ou arquivo para contagem
- Limitar login: 5 tentativas por IP/hora

---

## 13. Os 10 Testes Prioritários

### Teste 1: Login PF Válido ✅
```bash
curl -X POST http://localhost:8000/PHP/login.php \
  -d "tipoCadastro=pf&cpf=11144477735&senha=Teste@123" \
  -c cookies.txt -v
```
**SQL Verificação:**
```sql
SELECT id, nome, cpf FROM usuarios_pf WHERE cpf = '111.444.777-35';
```

---

### Teste 2: Criar Vaga (Empresa) ✅
```bash
# Primeiro fazer login como empresa e salvar cookie
curl -X POST http://localhost:8000/PHP/login.php \
  -d "tipoCadastro=pj&cnpj=11222333000181&senha=Teste@123" \
  -c empresa_cookies.txt

# Criar vaga
curl -X POST http://localhost:8000/PHP/gestao_vagas_empresa.php \
  -b empresa_cookies.txt \
  -d "acao=criar_vaga&titulo=Vaga Teste&categoria=tecnologia&salario=R$ 5000&tipoContrato=CLT&dataLimite=2025-03-01&descricao=Descrição da vaga teste com mais de 50 caracteres para passar na validação"
```
**SQL Verificação:**
```sql
SELECT id, titulo, categoria, status, empresa_id FROM vagas WHERE titulo = 'Vaga Teste';
```

---

### Teste 3: Listar Vagas Públicas ✅
```bash
curl -X GET "http://localhost:8000/PHP/vagas.php?acao=listar&categoria=tecnologia" \
  -H "Accept: application/json" -v
```

---

### Teste 4: Enviar Candidatura ✅
```bash
# Login como candidato
curl -X POST http://localhost:8000/PHP/login.php \
  -d "tipoCadastro=pf&cpf=11144477735&senha=Teste@123" \
  -c candidato_cookies.txt

# Enviar candidatura
curl -X POST http://localhost:8000/PHP/candidaturas.php \
  -b candidato_cookies.txt \
  -d "acao=enviar&vaga_id=1"
```
**SQL Verificação:**
```sql
SELECT c.id, c.status, v.titulo, p.nome 
FROM candidaturas c
JOIN vagas v ON c.vaga_id = v.id
JOIN pessoas p ON c.pessoa_id = p.id
WHERE c.vaga_id = 1;
```

---

### Teste 5: Listar Minhas Candidaturas ✅
```bash
curl -X GET "http://localhost:8000/PHP/candidaturas.php?acao=minhas" \
  -b candidato_cookies.txt \
  -H "Accept: application/json"
```

---

### Teste 6: Login Admin ✅
```bash
curl -X POST http://localhost:8000/PHP/admin/login.php \
  -d "email=admin@descubramuriae.local&senha=Admin@123" \
  -c admin_cookies.txt -v
```
**SQL Verificação:**
```sql
SELECT id, nome, email, ativo FROM administradores WHERE email = 'admin@descubramuriae.local';
```

---

### Teste 7: Dashboard Admin ✅
```bash
curl -X GET http://localhost:8000/PHP/admin/dashboard.php \
  -b admin_cookies.txt \
  -H "Accept: application/json"
```

---

### Teste 8: Cadastro PF ✅
```bash
curl -X POST http://localhost:8000/PHP/cadastro.php \
  -d "nome=Novo Usuário&tipoCadastro=pf&cpf=98765432100&email=novo@test.local&senha=Teste@123&senhaverif=Teste@123" \
  -v
```
**SQL Verificação:**
```sql
SELECT id, nome, cpf, email, LEFT(senha, 20) as hash_preview 
FROM usuarios_pf 
WHERE email = 'novo@test.local';
```

---

### Teste 9: Validação SQL Injection 🔒
```bash
curl -X POST http://localhost:8000/PHP/login.php \
  -d "tipoCadastro=pf&cpf=' OR '1'='1&senha=qualquer" \
  -v
```
**Resultado Esperado:** Erro de validação, não executa SQL

---

### Teste 10: Filtros de Vagas ✅
```bash
curl -X GET "http://localhost:8000/PHP/vagas.php?acao=listar&categoria=tecnologia&localidade=Muriaé&tipo=CLT" \
  -H "Accept: application/json"
```
**SQL Verificação:**
```sql
SELECT v.id, v.titulo, v.categoria, e.cidade, v.tipo_contrato
FROM vagas v
JOIN empresas e ON v.empresa_id = e.id
WHERE v.categoria = 'tecnologia' 
  AND e.cidade = 'Muriaé'
  AND v.tipo_contrato = 'CLT'
  AND v.status = 'Aberta'
  AND v.ativo = 1;
```

---

## 📝 Comandos de Execução Local

### 1. Configurar Ambiente
```bash
# Editar lib/config.php
# Ajustar: DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE
```

### 2. Criar Banco e Migrar
```bash
# Criar banco
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS descubra_muriae;"

# Executar migração
php PHP/migrate.php
```

### 3. Iniciar Servidor
```bash
# PHP built-in server
php -S localhost:8000 -t .
```

### 4. Acessar
```
http://localhost:8000/HTML/login.html
```

---

## 📊 Relatório de Execução (Modelo)

### Data: _______________
### Testador: _______________

| # | Teste | Status | Observações |
|---|-------|--------|-------------|
| 1 | Login PF | ☐ Pass ☐ Fail | |
| 2 | Criar Vaga | ☐ Pass ☐ Fail | |
| 3 | Listar Vagas | ☐ Pass ☐ Fail | |
| 4 | Enviar Candidatura | ☐ Pass ☐ Fail | |
| 5 | Listar Candidaturas | ☐ Pass ☐ Fail | |
| 6 | Login Admin | ☐ Pass ☐ Fail | |
| 7 | Dashboard Admin | ☐ Pass ☐ Fail | |
| 8 | Cadastro PF | ☐ Pass ☐ Fail | |
| 9 | SQL Injection | ☐ Pass ☐ Fail | |
| 10 | Filtros Vagas | ☐ Pass ☐ Fail | |

**Problemas Encontrados:**
1. ________________________________
2. ________________________________

**Observações Finais:**
________________________________

---

## 🎯 Conclusão

Este documento fornece:
- ✅ Documentação técnica completa
- ✅ Plano de testes detalhado
- ✅ Exemplos de curl e SQL
- ✅ Checklist de aceitação
- ✅ Matriz de riscos
- ✅ 10 testes prioritários para começar

**Próximos Passos:**
1. Executar os 10 testes prioritários
2. Preencher relatório de execução
3. Corrigir problemas encontrados
4. Executar testes E2E completos
5. Validar segurança

---

**Documento gerado em:** 2025-01-XX  
**Versão do Projeto:** 3.0

