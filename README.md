# Projeto de Extensão Universitária FASM 2025

O projeto de extensão universitária (EXTUNI) tem como objetivo unir alunos com diferentes níveis de conhecimento de desenvolvimento para a criação de um projeto real onde foi proposto o desenvolvimento de uma página web para o domínio "https://descubra.muriae.mg.gov.br/" com intuito de integração do conteúdo desenvolvido para uma implementação real do projeto que nos foi proposta em nossa disciplina.

Link para o drive do projeto: (https://drive.google.com/drive/u/6/home)

Email: extensaouniversitaria.2025@gmail.com

![imagem do drive](image.png)

---

## 🚀 Execução (Desenvolvimento)

### 1. Servidor PHP embutido:
```bash
php -S localhost:8000 -t .
```
Acesse `http://localhost:8000/HTML/login.html` ou `http://localhost:8000/HTML/index.html`.

### 2. Requisitos:
- PHP 8.0+
- MySQL 5.7+ / 8.0+
- Extensões PHP: `pdo_mysql`, `mbstring`, `fileinfo`

---

## 🗄️ Banco de Dados

### Configuração:
- Arquivo: `lib/config.php`
- Configurar: host, porta, base, usuário, senha

### Migração e Seeds:
Execute a migração:
```
http://localhost:8000/PHP/migrate.php
```

### Usuários de Teste Criados:
- **Pessoa Física (PF):**
  - CPF: `111.444.777-35`
  - Senha: `Teste@123`

- **Pessoa Jurídica (PJ):**
  - CNPJ: `11.222.333/0001-81`
  - Senha: `Teste@123`

- **Administrador:**
  - Email: `admin@descubramuriae.local`
  - Senha: `Admin@123`

### Teste de Conexão:
```
http://localhost:8000/PHP/db_check.php
```

---

## 📁 Estrutura do Projeto

```
Projeto-de-Extens-o-Universit-ria-FASM-2025/
├── HTML/              # Páginas HTML (frontend)
├── PHP/               # Scripts PHP (backend)
│   ├── admin/         # Módulo administrativo
│   └── partials/      # Partes reutilizáveis
├── JS/                # Scripts JavaScript
├── CSS/               # Estilos CSS
├── lib/               # Bibliotecas PHP (AtomPHP adaptado)
│   ├── Database.php   # Query Builder
│   ├── Session.php    # Gerenciamento de sessão
│   ├── Request.php   # Acesso a dados HTTP
│   ├── Files.php     # Upload de arquivos
│   ├── Validator.php # Validação de dados
│   ├── Helper.php    # Funções auxiliares
│   ├── Response.php  # Respostas JSON padronizadas
│   └── auth.php      # Middleware de autenticação
├── uploads/           # Arquivos enviados (curriculos, logos, vagas)
└── banco de dados/   # Scripts SQL
```

---

## 🔐 Autenticação

### Login de Usuários:
- **PF (Pessoa Física):** CPF + senha → Tabela `usuarios_pf`
- **PJ (Pessoa Jurídica):** CNPJ + senha → Tabela `usuarios_pj`
- **Admin:** Email + senha → Tabela `administradores`

### Sessão:
Após login bem-sucedido, a sessão armazena:
- `user_id` - ID do usuário
- `user_type` - Tipo: 'pf', 'pj' ou 'admin'
- `user_nome` - Nome do usuário
- `user_email` - Email do usuário

---

## 📋 Módulos Implementados

### ✅ 1. Módulo de Usuários (75%)
- Login PF/PJ
- Cadastro de usuários
- Cadastro de empresas
- Cadastro de currículos
- Edição de perfil (backend implementado)
- Configurações (alteração de senha)

### ✅ 2. Módulo de Vagas (85%)
- CRUD completo de vagas (empresas)
- Listagem pública de vagas
- Filtros por categoria, localidade, tipo
- Detalhes de vagas
- Gestão de vagas (empresas)

### ✅ 3. Módulo de Candidaturas (75%)
- Envio de candidatura
- Histórico de candidaturas (candidatos)
- Avaliação de candidatos (empresas)
- Status de candidaturas

### ✅ 4. Módulo Administrativo (90%)
- Login de administrador
- Dashboard com métricas
- Gerenciamento de usuários
- Gerenciamento de vagas (moderação)
- Visualização de candidaturas
- Relatórios básicos

---

## 🔌 Endpoints API

### Vagas (`PHP/vagas.php`):
- `GET ?acao=listar` - Listar vagas públicas (com filtros)
- `GET ?acao=detalhes&id={id}` - Detalhes de uma vaga
- `GET ?acao=categorias` - Listar categorias disponíveis

### Candidaturas (`PHP/candidaturas.php`):
- `POST acao=enviar` - Enviar candidatura (requer autenticação)
- `GET ?acao=minhas` - Listar candidaturas do usuário
- `GET ?acao=detalhes&id={id}` - Detalhes de uma candidatura

### Administrativo (`PHP/admin/`):
- `POST admin/login.php` - Login de administrador
- `GET admin/dashboard.php` - Dashboard com métricas
- `POST admin/usuarios.php` - CRUD de usuários
- `POST admin/vagas.php` - Moderação de vagas
- `GET admin/candidaturas.php` - Visualizar candidaturas
- `GET admin/relatorios.php?tipo={tipo}` - Relatórios

### Perfil (`PHP/perfil.php`):
- `GET ?acao=visualizar` - Visualizar perfil
- `POST acao=atualizar` - Atualizar perfil

### Configurações (`PHP/configuracoes.php`):
- `POST acao=alterar_senha` - Alterar senha
- `POST acao=excluir_conta` - Excluir conta (soft delete)

---

## 🔒 Segurança

- Hash de senhas com `password_hash()` (PASSWORD_DEFAULT)
- Validação de dados no servidor (Validator)
- Sanitização de inputs (Helper::limpar)
- Upload seguro (validação MIME + extensão)
- Prepared statements (PDO) - previne SQL injection
- Middleware de autenticação (auth.php)
- Controle de permissões por tipo de usuário

---

## 📝 Fluxos Integrados

### 1. Login PF/PJ (`PHP/login.php`):
- Valida CPF/CNPJ e senha
- Cria sessão com dados do usuário
- Redireciona para página inicial

### 2. Cadastro de Currículo (`HTML/Cadastro_de_currículo.html` → `PHP/processa.php`):
- Validações cliente/servidor
- Uploads em `uploads/curriculos/`
- Persiste em tabela `curriculos` (experiências em JSON)

### 3. Buscar Vagas (`HTML/buscar_vagas.html`):
- Conectado ao backend via `JS/buscar_vagas.js`
- Endpoint: `PHP/vagas.php`
- Filtros funcionais
- Botão "Candidatar-se" integrado

### 4. Minhas Candidaturas (`HTML/candidaturas.html`):
- Conectado ao backend via `JS/candidaturas.js`
- Endpoint: `PHP/candidaturas.php`
- Histórico completo com status

---

## 🛠️ Tecnologias Utilizadas

- **Front-end:**
  - HTML5
  - CSS3
  - JavaScript (ES6+)
  - Bootstrap 5.3.3
  - Bootstrap Icons

- **Back-end:**
  - PHP 8.0+
  - MySQL 5.7+/8.0+
  - PDO (Prepared Statements)
  - Bibliotecas AtomPHP adaptadas

- **Ferramentas:**
  - Git
  - Composer (se necessário)

---

## 📊 Progresso do Projeto

**Progresso Geral: ~85%**

### Por Módulo:
- ✅ **Usuários:** 75%
- ✅ **Vagas:** 85%
- ✅ **Candidaturas:** 75%
- ✅ **Administrativo:** 90%
- ✅ **Integração Front-Back:** 90%

---

## ⚠️ Observações Importantes

1. **Configuração:**
   - Ajuste `lib/config.php` conforme seu ambiente
   - Configure permissões de escrita em `uploads/`

2. **Produção:**
   - Configure servidor web (Apache/Nginx)
   - Use variáveis de ambiente para credenciais
   - Ative HTTPS
   - Configure backup automático do banco

3. **Segurança:**
   - Não commite credenciais no Git
   - Use `.gitignore` para arquivos sensíveis
   - Valide todos os inputs no servidor

4. **Testes:**
   - Teste todos os fluxos antes de produção
   - Valide permissões de acesso
   - Teste uploads de arquivos

---

## 📞 Suporte

- Email: extensaouniversitaria.2025@gmail.com
- Link do Drive: https://drive.google.com/drive/u/6/home

---

**Desenvolvido com ❤️ para o projeto de extensão universitária FASM 2025**
