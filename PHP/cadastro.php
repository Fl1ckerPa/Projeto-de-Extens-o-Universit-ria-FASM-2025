<?php
/**
 * Processa cadastro de usuário (PF ou PJ)
 * Versão refatorada usando bibliotecas do AtomPHP
 */

require_once __DIR__ . '/../lib/bootstrap.php';

if (!Request::isPost()) {
    Helper::jsonError('Método não permitido', null, 405);
}

$erros = [];

// Coletar dados
$dados = [
    'nome' => Request::post('nome', ''),
    'cpf' => preg_replace('/\D+/', '', Request::post('cpf', '')),
    'cnpj' => preg_replace('/\D+/', '', Request::post('cnpj', '')),
    'tipoCadastro' => Request::post('tipoCadastro', ''),
    'email' => Request::post('email', ''),
    'senha' => Request::post('senha', ''),
    'senhaVerif' => Request::post('senhaverif', '')
];

// Validações
if (empty($dados['nome'])) {
    $erros[] = 'O campo nome é obrigatório!';
}

if ($dados['tipoCadastro'] === 'pf') {
    if (empty($dados['cpf'])) {
        $erros[] = 'CPF obrigatório!';
    } elseif (!Helper::validarCPF($dados['cpf'])) {
        $erros[] = 'CPF inválido.';
    }
} elseif ($dados['tipoCadastro'] === 'pj') {
    if (empty($dados['cnpj'])) {
        $erros[] = 'CNPJ obrigatório!';
    } elseif (!Helper::validarCNPJ($dados['cnpj'])) {
        $erros[] = 'CNPJ inválido.';
    }
} else {
    $erros[] = 'Tipo de cadastro inválido (selecione PF ou PJ).';
}

// Validar email
$rules = [
    'email' => ['label' => 'Email', 'rules' => 'required|email']
];

if (!Validator::make(['email' => $dados['email']], $rules)) {
    $erros = array_merge($erros, array_values(Validator::getErrors()));
}

// Validar senha
if (empty($dados['senha']) || empty($dados['senhaVerif'])) {
    $erros[] = 'Ambos os campos de senha são obrigatórios.';
} elseif ($dados['senha'] !== $dados['senhaVerif']) {
    $erros[] = 'As senhas não são idênticas.';
} elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $dados['senha'])) {
    $erros[] = 'A senha deve ter entre 8 e 20 caracteres, conter letras, números e um caractere especial.';
}

// Se houver erros
if (!empty($erros)) {
    $title = 'Erros no cadastro';
    $messages = $erros;
    $type = 'error';
    $links = ['../HTML/cadastro.html' => 'Voltar ao cadastro'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// Aqui você salvaria no banco
// Exemplo:
/*
$db = new Database();
$tabela = $dados['tipoCadastro'] === 'pj' ? 'empresas' : 'pessoas';
$identificador = $dados['tipoCadastro'] === 'pj' ? 'cnpj' : 'cpf';
$valorIdentificador = $dados['tipoCadastro'] === 'pj' ? $dados['cnpj'] : $dados['cpf'];

// Verificar se já existe
$existe = $db->table($tabela)
    ->where($identificador, $valorIdentificador)
    ->first();

if ($existe) {
    Helper::jsonError('CPF/CNPJ já cadastrado.');
}

// Verificar email
$existeEmail = $db->table($tabela)
    ->where('email', $dados['email'])
    ->first();

if ($existeEmail) {
    Helper::jsonError('Email já cadastrado.');
}

// Inserir
$dadosInsert = [
    'nome' => $dados['nome'],
    $identificador => $valorIdentificador,
    'email' => $dados['email'],
    'senha' => Helper::hashSenha($dados['senha']),
    'created_at' => date('Y-m-d H:i:s')
];

$id = $db->table($tabela)->insert($dadosInsert);

if ($id) {
    Session::set('msgSuccess', 'Cadastro realizado com sucesso!');
    Helper::jsonSuccess('Cadastro realizado com sucesso!', ['id' => $id]);
}
*/

// Sucesso (placeholder)
$title = 'Cadastro validado com sucesso!';
$messages = [
    '<strong>Nome:</strong> ' . htmlspecialchars($dados['nome']),
    ($dados['tipoCadastro'] === 'pf' 
        ? ('<strong>CPF:</strong> ' . Helper::formatarCPF($dados['cpf']))
        : ('<strong>CNPJ:</strong> ' . Helper::formatarCNPJ($dados['cnpj']))),
    '<strong>Email:</strong> ' . htmlspecialchars($dados['email']),
    '<strong>Senha:</strong> (oculta por segurança)'
];
$type = 'success';
$links = ['../HTML/login.html' => 'Ir para login'];
include __DIR__ . '/partials/layout.php';
