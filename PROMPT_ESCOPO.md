# Prompt de Escopo - Projeto Descubra Muriaé

## 📋 Visão Geral do Projeto

O **Descubra Muriaé** é uma plataforma web de emprego desenvolvida como projeto de extensão universitária (EXTUNI) pela FASM 2025. O sistema foi projetado para conectar candidatos (pessoas físicas) e empresas (pessoas jurídicas) na cidade de Muriaé, MG, facilitando o processo de recrutamento e seleção de profissionais.

**Domínio:** https://descubra.muriae.mg.gov.br/

**Objetivo:** Desenvolver uma solução real e funcional para integração de conteúdo desenvolvido na disciplina, unindo alunos com diferentes níveis de conhecimento em desenvolvimento.

---

## 🎯 Escopo Funcional

### 1. Módulo de Autenticação e Usuários

#### 1.1 Tipos de Usuários
- **Pessoa Física (PF):** Candidatos que buscam oportunidades de emprego
  - Login via CPF + senha
  - Cadastro completo com dados pessoais
  - Gerenciamento de perfil e currículo
  
- **Pessoa Jurídica (PJ):** Empresas que publicam vagas
  - Login via CNPJ + senha
  - Cadastro de empresa com dados corporativos
  - Gerenciamento de vagas e candidaturas
  
- **Administrador:** Gestores do sistema
  - Login via email + senha
  - Acesso ao painel administrativo
  - Moderação de conteúdo e relatórios

#### 1.2 Funcionalidades de Usuário
- ✅ Sistema de login diferenciado por tipo de usuário
- ✅ Cadastro de usuários (PF e PJ)
- ✅ Cadastro e edição de currículos (PF)
- ✅ Upload de arquivos (currículos, logos de empresas)
- ✅ Edição de perfil (backend implementado)
- ✅ Configurações de conta (alteração de senha)
- ✅ Recuperação de senha
- ✅ Gerenciamento de sessão com controle de permissões

**Status:** 75% implementado

---

### 2. Módulo de Vagas de Emprego

#### 2.1 Funcionalidades para Empresas
- ✅ CRUD completo de vagas
- ✅ Publicação de vagas com informações detalhadas:
  - Título e descrição
  - Cargo
  - Modalidade de trabalho (presencial, remoto, híbrido)
  - Vínculo contratual (CLT, PJ, estágio, etc.)
  - Categoria profissional
  - Localidade
  - Data de início e fim
  - Requisitos e benefícios
- ✅ Gestão de vagas publicadas
- ✅ Visualização de candidaturas recebidas
- ✅ Avaliação de candidatos

#### 2.2 Funcionalidades para Candidatos
- ✅ Busca pública de vagas
- ✅ Filtros avançados:
  - Por categoria profissional
  - Por localidade
  - Por tipo de vínculo
  - Por modalidade de trabalho
- ✅ Visualização detalhada de vagas
- ✅ Sistema de paginação
- ✅ Contagem de candidatos por vaga

#### 2.3 Funcionalidades Administrativas
- ✅ Moderação de vagas
- ✅ Aprovação/rejeição de publicações
- ✅ Visualização de todas as vagas do sistema

**Status:** 85% implementado

---

### 3. Módulo de Candidaturas

#### 3.1 Funcionalidades para Candidatos
- ✅ Envio de candidatura para vagas
- ✅ Histórico completo de candidaturas
- ✅ Visualização de status das candidaturas:
  - Pendente
  - Em análise
  - Aprovada
  - Rejeitada
- ✅ Detalhes de cada candidatura

#### 3.2 Funcionalidades para Empresas
- ✅ Visualização de candidaturas recebidas
- ✅ Avaliação de candidatos
- ✅ Alteração de status de candidaturas
- ✅ Visualização de currículos dos candidatos

#### 3.3 Funcionalidades Administrativas
- ✅ Visualização de todas as candidaturas do sistema
- ✅ Relatórios de candidaturas

**Status:** 75% implementado

---

### 4. Módulo Administrativo

#### 4.1 Dashboard
- ✅ Métricas gerais do sistema:
  - Total de usuários (PF e PJ)
  - Total de vagas ativas
  - Total de candidaturas
  - Estatísticas por período
- ✅ Gráficos e visualizações

#### 4.2 Gerenciamento de Usuários
- ✅ Listagem de todos os usuários
- ✅ Visualização de perfis
- ✅ Ativação/desativação de contas
- ✅ Edição de dados (se necessário)

#### 4.3 Gerenciamento de Vagas
- ✅ Moderação de vagas publicadas
- ✅ Aprovação/rejeição de vagas
- ✅ Edição de vagas (se necessário)
- ✅ Controle de status das vagas

#### 4.4 Relatórios
- ✅ Relatórios de usuários
- ✅ Relatórios de vagas
- ✅ Relatórios de candidaturas
- ✅ Estatísticas gerais

**Status:** 90% implementado

---

## 🏗️ Arquitetura Técnica

### Stack Tecnológica

#### Front-end
- **HTML5** - Estrutura das páginas
- **CSS3** - Estilização (arquivos modulares por página)
- **JavaScript (ES6+)** - Interatividade e integração com API
- **Bootstrap 5.3.3** - Framework CSS responsivo
- **Bootstrap Icons** - Ícones

#### Back-end
- **PHP 8.0+** - Linguagem servidor
- **MySQL 5.7+/8.0+** - Banco de dados relacional
- **PDO** - Camada de abstração de banco (Prepared Statements)
- **Bibliotecas AtomPHP adaptadas** - Framework customizado:
  - `Database.php` - Query Builder
  - `Session.php` - Gerenciamento de sessão
  - `Request.php` - Acesso a dados HTTP
  - `Files.php` - Upload de arquivos
  - `Validator.php` - Validação de dados
  - `Helper.php` - Funções auxiliares
  - `Response.php` - Respostas JSON padronizadas
  - `auth.php` - Middleware de autenticação

### Estrutura de Diretórios

```
descubra/
├── HTML/                    # Páginas HTML (frontend)
│   ├── index.html          # Página inicial
│   ├── login.html          # Login
│   ├── cadastro.html       # Cadastro de usuários
│   ├── cadastro_empresa.html
│   ├── Cadastro_de_currículo.html
│   ├── buscar_vagas.html
│   ├── candidaturas.html
│   ├── perfil.html
│   ├── perfil_pj.html
│   ├── gestao_vagas_empresa.html
│   ├── configuracoes.html
│   ├── recuperar_senha.html
│   ├── reset_senha.html
│   └── sobre_nos.html
│
├── PHP/                     # Scripts PHP (backend)
│   ├── admin/              # Módulo administrativo
│   │   ├── login.php
│   │   ├── dashboard.php
│   │   ├── usuarios.php
│   │   ├── vagas.php
│   │   ├── candidaturas.php
│   │   └── relatorios.php
│   ├── partials/           # Partes reutilizáveis
│   ├── login.php
│   ├── cadastro.php
│   ├── cadastro_empresa.php
│   ├── processa.php        # Processamento de currículos
│   ├── vagas.php           # API de vagas
│   ├── candidaturas.php    # API de candidaturas
│   ├── perfil.php
│   ├── configuracoes.php
│   ├── gestao_vagas_empresa.php
│   ├── recuperar_senha.php
│   ├── reset_senha.php
│   ├── logout.php
│   ├── migrate.php         # Migração do banco
│   └── db_check.php        # Teste de conexão
│
├── JS/                      # Scripts JavaScript
│   ├── auth.js
│   ├── login_cadastro.js
│   ├── loginPessoaFisica.js
│   ├── buscar_vagas.js
│   ├── candidaturas.js
│   ├── cadastro_curriculo.js
│   ├── cadastro_empresa.js
│   ├── perfil.js
│   ├── perfil_pj.js
│   ├── gestao_vagas_empresa.js
│   ├── configuracoes.js
│   ├── dashboard.js
│   ├── index.js
│   ├── protect-page.js     # Proteção de páginas
│   └── restricted-nav.js   # Navegação restrita
│
├── CSS/                     # Estilos CSS
│   ├── index.css
│   ├── login.css
│   ├── cadastro_empresa.css
│   ├── dashboard.css
│   ├── gestao_vagas_empresa.css
│   ├── inicio_pagina_pessoa_fisica.css
│   └── sobre_nos.css
│
├── lib/                     # Bibliotecas PHP
│   ├── bootstrap.php       # Inicialização
│   ├── config.php          # Configurações
│   ├── Database.php
│   ├── Session.php
│   ├── Request.php
│   ├── Files.php
│   ├── Validator.php
│   ├── Helper.php
│   ├── Response.php
│   ├── auth.php
│   └── Schema.php
│
├── uploads/                 # Arquivos enviados
│   ├── curriculos/
│   ├── logos/
│   └── vagas/
│
├── IMG/                     # Imagens estáticas
│   ├── logo_descubra_short.png
│   ├── logo_muriae_170_anos.png
│   ├── logo_sebrae.png
│   ├── logo_sm_tech.png
│   ├── panoramica_muriaé.jpg
│   └── categorias_trabalho/
│
└── banco de dados/          # Scripts SQL
    └── descubramuriae.sql
```

---

## 🔌 API e Endpoints

### Endpoints de Vagas (`PHP/vagas.php`)
- `GET ?acao=listar` - Listar vagas públicas (com filtros: categoria, localidade, tipo, paginação)
- `GET ?acao=detalhes&id={id}` - Detalhes de uma vaga específica
- `GET ?acao=categorias` - Listar categorias disponíveis

### Endpoints de Candidaturas (`PHP/candidaturas.php`)
- `POST acao=enviar` - Enviar candidatura (requer autenticação PF)
- `GET ?acao=minhas` - Listar candidaturas do usuário logado
- `GET ?acao=detalhes&id={id}` - Detalhes de uma candidatura
- `GET ?acao=por_vaga&vaga_id={id}` - Candidaturas de uma vaga (requer autenticação PJ)

### Endpoints Administrativos (`PHP/admin/`)
- `POST admin/login.php` - Login de administrador
- `GET admin/dashboard.php` - Dashboard com métricas (requer autenticação admin)
- `POST admin/usuarios.php` - CRUD de usuários (requer autenticação admin)
- `POST admin/vagas.php` - Moderação de vagas (requer autenticação admin)
- `GET admin/candidaturas.php` - Visualizar candidaturas (requer autenticação admin)
- `GET admin/relatorios.php?tipo={tipo}` - Relatórios (requer autenticação admin)

### Endpoints de Perfil (`PHP/perfil.php`)
- `GET ?acao=visualizar` - Visualizar perfil do usuário logado
- `POST acao=atualizar` - Atualizar perfil (requer autenticação)

### Endpoints de Configurações (`PHP/configuracoes.php`)
- `POST acao=alterar_senha` - Alterar senha (requer autenticação)
- `POST acao=excluir_conta` - Excluir conta (soft delete, requer autenticação)

### Endpoints de Autenticação
- `POST PHP/login.php` - Login de usuários (PF/PJ)
- `POST PHP/logout.php` - Logout
- `POST PHP/recuperar_senha.php` - Solicitar recuperação de senha
- `POST PHP/reset_senha.php` - Redefinir senha com token

---

## 🗄️ Modelo de Dados

### Principais Entidades

#### Usuários
- `usuarios_pf` - Usuários pessoa física
- `usuarios_pj` - Usuários pessoa jurídica
- `administradores` - Administradores do sistema
- `pessoa_fisica` - Dados complementares de PF
- `empresa` - Dados de empresas

#### Vagas
- `vaga` - Vagas de emprego
- `categoria_vaga` - Categorias profissionais
- `cargo` - Cargos disponíveis
- `modalidade_trabalho` - Modalidades (presencial, remoto, híbrido)
- `vinculo_contratual` - Tipos de vínculo (CLT, PJ, estágio)
- `status_vaga` - Status das vagas (aberta, fechada, etc.)

#### Candidaturas
- `candidatura` - Candidaturas de usuários a vagas
- `status_candidatura` - Status (pendente, aprovada, rejeitada)

#### Currículos
- `curriculo` - Currículos dos candidatos
- Experiências profissionais armazenadas em JSON

#### Outros
- `cidade` - Cidades cadastradas
- `estabelecimento` - Estabelecimentos (integração com sistema de turismo)
- `categoria_estabelecimento` - Categorias de estabelecimentos

---

## 🔒 Segurança

### Medidas Implementadas
- ✅ Hash de senhas com `password_hash()` (PASSWORD_DEFAULT)
- ✅ Validação de dados no servidor (Validator)
- ✅ Sanitização de inputs (Helper::limpar)
- ✅ Upload seguro com validação MIME + extensão
- ✅ Prepared statements (PDO) - previne SQL injection
- ✅ Middleware de autenticação (auth.php)
- ✅ Controle de permissões por tipo de usuário
- ✅ Proteção de rotas por tipo de usuário
- ✅ Gerenciamento de sessão seguro

### Controles de Acesso
- **Pessoa Física:** Acesso a busca de vagas, candidaturas, perfil próprio
- **Pessoa Jurídica:** Acesso a gestão de vagas, visualização de candidaturas, perfil da empresa
- **Administrador:** Acesso total ao sistema, moderação, relatórios

---

## 📊 Status de Implementação

### Progresso Geral: ~85%

#### Por Módulo:
- ✅ **Módulo de Usuários:** 75%
  - Pendente: Interface completa de edição de perfil no frontend
  
- ✅ **Módulo de Vagas:** 85%
  - Pendente: Melhorias na interface de gestão
  
- ✅ **Módulo de Candidaturas:** 75%
  - Pendente: Notificações de status
  
- ✅ **Módulo Administrativo:** 90%
  - Pendente: Exportação de relatórios
  
- ✅ **Integração Front-Back:** 90%
  - Pendente: Tratamento de erros mais robusto

---

## 🚀 Requisitos de Execução

### Ambiente de Desenvolvimento
- **PHP:** 8.0 ou superior
- **MySQL:** 5.7+ ou 8.0+
- **Extensões PHP necessárias:**
  - `pdo_mysql`
  - `mbstring`
  - `fileinfo`

### Configuração
1. Configurar `lib/config.php` com credenciais do banco
2. Executar migração: `http://localhost:8000/PHP/migrate.php`
3. Testar conexão: `http://localhost:8000/PHP/db_check.php`
4. Configurar permissões de escrita em `uploads/`

### Execução
```bash
php -S localhost:8000 -t .
```

Acessar:
- `http://localhost:8000/HTML/login.html`
- `http://localhost:8000/HTML/index.html`

### Usuários de Teste
- **PF:** CPF `111.444.777-35` / Senha `Teste@123`
- **PJ:** CNPJ `11.222.333/0001-81` / Senha `Teste@123`
- **Admin:** Email `admin@descubramuriae.local` / Senha `Admin@123`

---

## 📝 Fluxos Principais

### 1. Fluxo de Cadastro e Login
1. Usuário acessa página de cadastro
2. Escolhe tipo (PF ou PJ)
3. Preenche dados e envia
4. Sistema valida e cria conta
5. Usuário faz login com CPF/CNPJ e senha
6. Sistema cria sessão e redireciona

### 2. Fluxo de Busca e Candidatura
1. Candidato (PF) faz login
2. Acessa página de busca de vagas
3. Aplica filtros (categoria, localidade, tipo)
4. Visualiza lista de vagas
5. Clica em vaga para ver detalhes
6. Envia candidatura
7. Sistema registra candidatura com status "pendente"

### 3. Fluxo de Publicação de Vaga
1. Empresa (PJ) faz login
2. Acessa gestão de vagas
3. Cria nova vaga com todos os dados
4. Sistema valida e salva
5. Vaga fica pendente de aprovação (se moderação ativa)
6. Após aprovação, vaga fica visível publicamente

### 4. Fluxo de Avaliação de Candidatos
1. Empresa (PJ) acessa gestão de vagas
2. Visualiza candidaturas recebidas
3. Acessa detalhes do candidato e currículo
4. Altera status da candidatura (aprovada/rejeitada)
5. Candidato visualiza atualização em "Minhas Candidaturas"

### 5. Fluxo Administrativo
1. Administrador faz login
2. Acessa dashboard com métricas
3. Pode moderar vagas, gerenciar usuários
4. Visualiza relatórios e estatísticas
5. Tem acesso total ao sistema

---

## 🎨 Interface e UX

### Características
- Design responsivo com Bootstrap 5.3.3
- Interface moderna e intuitiva
- Navegação clara entre módulos
- Feedback visual para ações do usuário
- Validação em tempo real nos formulários
- Mensagens de erro e sucesso padronizadas

### Páginas Principais
- **Página Inicial:** Apresentação do projeto e funcionalidades
- **Login:** Interface unificada para PF/PJ/Admin
- **Busca de Vagas:** Interface de busca com filtros e cards de vagas
- **Dashboard Admin:** Painel com métricas e gráficos
- **Perfil:** Visualização e edição de dados do usuário
- **Gestão de Vagas:** Interface para empresas gerenciarem vagas

---

## 🔄 Integrações e Extensões Futuras

### Possíveis Melhorias
- Sistema de notificações por email
- Chat entre empresa e candidato
- Sistema de favoritos de vagas
- Recomendações personalizadas
- Integração com redes sociais
- API REST completa e documentada
- Aplicativo mobile
- Sistema de avaliações e feedback
- Exportação de relatórios em PDF/Excel

---

## 📞 Informações do Projeto

- **Email:** extensaouniversitaria.2025@gmail.com
- **Drive:** https://drive.google.com/drive/u/6/home
- **Instituição:** FASM (Faculdade de Saúde de Minas Gerais)
- **Ano:** 2025
- **Tipo:** Projeto de Extensão Universitária (EXTUNI)

---

## ✅ Checklist de Funcionalidades

### Autenticação
- [x] Login PF (CPF + senha)
- [x] Login PJ (CNPJ + senha)
- [x] Login Admin (email + senha)
- [x] Cadastro PF
- [x] Cadastro PJ
- [x] Recuperação de senha
- [x] Logout
- [x] Gerenciamento de sessão

### Perfil e Currículo
- [x] Cadastro de currículo
- [x] Upload de arquivo de currículo
- [x] Visualização de perfil
- [x] Edição de perfil (backend)
- [ ] Edição de perfil (frontend completo)
- [x] Alteração de senha
- [x] Exclusão de conta

### Vagas
- [x] Publicação de vagas (PJ)
- [x] Listagem pública de vagas
- [x] Filtros de busca
- [x] Detalhes de vagas
- [x] Gestão de vagas (PJ)
- [x] Moderação de vagas (Admin)
- [x] Paginação de resultados

### Candidaturas
- [x] Envio de candidatura
- [x] Histórico de candidaturas (PF)
- [x] Visualização de candidaturas (PJ)
- [x] Alteração de status (PJ)
- [x] Visualização de candidaturas (Admin)
- [ ] Notificações de status

### Administrativo
- [x] Dashboard com métricas
- [x] Gerenciamento de usuários
- [x] Moderação de vagas
- [x] Visualização de candidaturas
- [x] Relatórios básicos
- [ ] Exportação de relatórios

---

**Este documento descreve o escopo completo do projeto Descubra Muriaé, servindo como referência para desenvolvimento, manutenção e evolução do sistema.**

