<?php
/**
 * Processa login (PF ou PJ)
 * Versão refatorada usando bibliotecas do AtomPHP
 */

require_once __DIR__ . '/../lib/bootstrap.php';

if (!Request::isPost()) {
    Helper::jsonError('Método não permitido', null, 405);
}

$erros = [];

// Coletar dados
$tipo = strtolower(limpar(Request::post('tipoCadastro', '')));
$cpf = preg_replace('/\D+/', '', Request::post('cpf', ''));
$cnpj = preg_replace('/\D+/', '', Request::post('cnpj', ''));
$senha = Request::post('senha', '');

// Validação
if ($tipo === 'pf') {
    if (empty($cpf)) {
        $erros[] = 'O campo CPF é obrigatório para Pessoa Física.';
    } elseif (!Helper::validarCPF($cpf)) {
        $erros[] = 'CPF inválido.';
    }
} elseif ($tipo === 'pj') {
    if (empty($cnpj)) {
        $erros[] = 'O campo CNPJ é obrigatório para Pessoa Jurídica.';
    } elseif (!Helper::validarCNPJ($cnpj)) {
        $erros[] = 'CNPJ inválido.';
    }
} else {
    // Fallback: tentar inferir pelo preenchimento
    if (!empty($cpf) && empty($cnpj)) {
        $tipo = 'pf';
        if (!Helper::validarCPF($cpf)) {
            $erros[] = 'CPF inválido.';
        }
    } elseif (!empty($cnpj) && empty($cpf)) {
        $tipo = 'pj';
        if (!Helper::validarCNPJ($cnpj)) {
            $erros[] = 'CNPJ inválido.';
        }
    } else {
        $erros[] = 'Selecione o tipo de cadastro (PF ou PJ) e preencha CPF ou CNPJ.';
    }
}

// Validar senha
$rules = [
    'senha' => ['label' => 'Senha', 'rules' => 'required|min:8|max:20']
];

$dadosValidacao = ['senha' => $senha];
if (!Validator::make($dadosValidacao, $rules)) {
    $erros = array_merge($erros, array_values(Validator::getErrors()));
}

if (!empty($senha) && !preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $senha)) {
    $erros[] = 'A senha deve conter entre 8 e 20 caracteres, letras, números e ao menos um caractere especial.';
}

// Se houver erros
if (!empty($erros)) {
    $title = 'Erros no login';
    $messages = $erros;
    $type = 'error';
    $links = ['../HTML/login.html' => 'Voltar ao login'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// Aqui você faria a autenticação no banco
// Exemplo:
/*
$db = new Database();
$identificador = $tipo === 'pj' ? $cnpj : $cpf;
$tabela = $tipo === 'pj' ? 'empresas' : 'pessoas';

$usuario = $db->table($tabela)
    ->where($tipo === 'pj' ? 'cnpj' : 'cpf', $identificador)
    ->first();

if ($usuario && Helper::verificarSenha($senha, $usuario['senha'])) {
    Session::set('user_id', $usuario['id']);
    Session::set('user_type', $tipo);
    Session::set('user_nome', $usuario['nome']);
    
    Helper::jsonSuccess('Login realizado com sucesso!', [
        'tipo' => strtoupper($tipo),
        'id' => $usuario['id']
    ]);
} else {
    Helper::jsonError('CPF/CNPJ ou senha incorretos.');
}
*/

// Sucesso (placeholder)
$idLabel = $tipo === 'pj' ? 'CNPJ' : 'CPF';
$idValor = $tipo === 'pj' ? $cnpj : $cpf;
$mascarado = str_repeat('•', max(0, strlen($idValor) - 4)) . substr($idValor, -4);

$title = 'Login validado com sucesso!';
$messages = [
    '<strong>Tipo:</strong> ' . strtoupper($tipo),
    '<strong>' . $idLabel . ':</strong> ' . $mascarado,
    '<strong>Senha:</strong> (oculta)'
];
$type = 'success';
$links = ['../HTML/dashboard.html' => 'Ir para o painel'];
include __DIR__ . '/partials/layout.php';
