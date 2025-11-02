# Bibliotecas Adaptadas do AtomPHP

Esta pasta contém bibliotecas adaptadas do framework AtomPHP para uso sem padrão MVC.

## Estrutura

- **config.php** - Configurações do projeto (banco de dados, uploads, etc)
- **bootstrap.php** - Inicialização do sistema (carrega todas as bibliotecas)
- **Database.php** - Classe para manipulação de banco de dados (Query Builder)
- **Session.php** - Classe para manipulação de sessão
- **Request.php** - Classe para manipulação de requisições HTTP
- **Files.php** - Classe para upload de arquivos
- **Validator.php** - Classe para validação de dados

## Como Usar

### 1. Incluir o Bootstrap

No início de cada arquivo PHP que precisar usar as bibliotecas:

```php
<?php
require_once __DIR__ . '/../lib/bootstrap.php';

// Agora você pode usar todas as classes
```

### 2. Database (Banco de Dados)

#### Query Builder
```php
$db = new Database();

// SELECT - Buscar todos
$produtos = $db->table('produtos')->findAll();

// SELECT - Buscar um
$produto = $db->table('produtos')->where('id', 1)->first();

// SELECT - Com condições
$produtos = $db->table('produtos')
    ->where('ativo', 1)
    ->whereLike('nome', 'notebook')
    ->orderBy('nome', 'ASC')
    ->findAll();

// INSERT
$id = $db->table('produtos')->insert([
    'nome' => 'Produto',
    'preco' => 99.90
]);

// UPDATE
$db->table('produtos')
    ->where('id', 1)
    ->update(['preco' => 149.90]);

// DELETE
$db->table('produtos')
    ->where('id', 1)
    ->delete();
```

#### SQL Direto
```php
$db = new Database();

// SELECT
$sql = "SELECT * FROM produtos WHERE ativo = ?";
$result = $db->dbSelect($sql, [1]);
$produtos = $db->dbBuscaArrayAll($result);

// INSERT
$sql = "INSERT INTO produtos (nome, preco) VALUES (?, ?)";
$id = $db->dbInsert($sql, ['Produto', 99.90]);

// UPDATE
$sql = "UPDATE produtos SET preco = ? WHERE id = ?";
$linhas = $db->dbUpdate($sql, [149.90, 1]);

// DELETE
$sql = "DELETE FROM produtos WHERE id = ?";
$linhas = $db->dbDelete($sql, [1]);
```

### 3. Session (Sessão)

```php
// Definir
Session::set('userId', 123);
Session::set('userName', 'João');

// Recuperar
$userId = Session::get('userId');

// Recuperar e remover
$userId = Session::getDestroy('userId');

// Remover
Session::destroy('userId');
```

### 4. Request (Requisições)

```php
// GET
$id = Request::get('id');
$id = Request::get('id', 0); // com valor padrão

// POST
$nome = Request::post('nome');
$email = Request::post('email', ''); // com valor padrão

// Verificar método
if (Request::isPost()) {
    // Processar POST
}

// Todos os dados POST
$dados = Request::all();

// JSON
$json = Request::getJson();
```

### 5. Files (Upload)

```php
$files = new Files();

// Upload único
$resultado = $files->upload(
    $_FILES['arquivo'],
    'produtos',  // pasta de destino
    'imagem'     // prefixo (opcional)
);

if ($resultado['status']) {
    $caminho = $resultado['path'];
    $nome = $resultado['nome'];
} else {
    $erro = $resultado['message'];
}

// Deletar arquivo
$files->delete($nomeArquivo, 'produtos');
```

### 6. Validator (Validação)

```php
$rules = [
    'nome' => [
        'label' => 'Nome',
        'rules' => 'required|min:3|max:100'
    ],
    'email' => [
        'label' => 'Email',
        'rules' => 'required|email'
    ],
    'preco' => [
        'label' => 'Preço',
        'rules' => 'required|float'
    ]
];

$dados = Request::all();

if (Validator::make($dados, $rules)) {
    // Validação passou
    // Processar dados
} else {
    // Validação falhou
    $erros = Validator::getErrors();
    // Os erros também estão em Session::get('formErrors')
}
```

## Regras de Validação Disponíveis

- `required` - Campo obrigatório
- `email` - Email válido
- `int` - Número inteiro
- `float` - Número decimal
- `min:X` - Mínimo de X caracteres
- `max:X` - Máximo de X caracteres
- `date` - Data no formato Y-m-d
- `datetime` - Data e hora no formato Y-m-d H:i:s

## Configuração

Edite o arquivo `config.php` para ajustar as configurações do banco de dados e outros parâmetros.

