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

// Validação (sem verificação de dígitos verificadores - apenas tamanho)
if ($tipo === 'pf') {
    if (empty($cpf)) {
        $erros[] = 'O campo CPF é obrigatório para Pessoa Física.';
    } elseif (strlen($cpf) !== 11) {
        $erros[] = 'CPF deve conter 11 dígitos.';
    }
} elseif ($tipo === 'pj') {
    if (empty($cnpj)) {
        $erros[] = 'O campo CNPJ é obrigatório para Pessoa Jurídica.';
    } elseif (strlen($cnpj) !== 14) {
        $erros[] = 'CNPJ deve conter 14 dígitos.';
    }
} else {
    // Fallback: tentar inferir pelo preenchimento
    if (!empty($cpf) && empty($cnpj)) {
        $tipo = 'pf';
        if (strlen($cpf) !== 11) {
            $erros[] = 'CPF deve conter 11 dígitos.';
        }
    } elseif (!empty($cnpj) && empty($cpf)) {
        $tipo = 'pj';
        if (strlen($cnpj) !== 14) {
            $erros[] = 'CNPJ deve conter 14 dígitos.';
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

// Autenticação no banco
$db = new Database();
$identificador = $tipo === 'pj' ? $cnpj : $cpf; // somente dígitos
$tabela = $tipo === 'pj' ? 'usuarios_pj' : 'usuarios_pf';
$campo = $tipo === 'pj' ? 'cnpj' : 'cpf';

$usuario = null;

try {
    // Tentar com máscara primeiro
    $identificadorMascarado = $tipo === 'pj' ? Helper::formatarCNPJ($identificador) : Helper::formatarCPF($identificador);
    
    $db->dbClear(); // Limpar query builder
    $usuario = $db->table($tabela)
        ->where($campo, $identificadorMascarado)
        ->first();
    
    // Se não encontrou, tentar sem máscara
    if (!$usuario) {
        $db->dbClear(); // Limpar query builder
        $usuario = $db->table($tabela)
            ->where($campo, $identificador)
            ->first();
    }
    
    // Última tentativa: buscar todos e comparar apenas dígitos
    if (!$usuario) {
        $db->dbClear(); // Limpar query builder
        $todos = $db->table($tabela)->findAll();
        foreach ($todos as $u) {
            $valorBanco = preg_replace('/\D+/', '', $u[$campo]);
            if ($valorBanco === $identificador) {
                $usuario = $u;
                break;
            }
        }
    }
} catch (\Exception $e) {
    // Erro na busca - continuar normalmente para mostrar mensagem genérica
    error_log("Erro ao buscar usuário: " . $e->getMessage());
}

if ($usuario) {
    // Verificar senha
    $senhaValida = Helper::verificarSenha($senha, $usuario['senha']);
    
    if ($senhaValida) {
        // Salvar dados na sessão
        Session::set('user_id', $usuario['id']);
        Session::set('user_type', $tipo);
        Session::set('user_nome', $usuario['nome']);
        Session::set('user_email', $usuario['email'] ?? '');
        
        // Redirecionar para página inicial (PF ou PJ)
        if ($tipo === 'pf') {
            header('Location: ../HTML/inicio_pessoa_fisica.html');
        } else {
            // Se tiver página para PJ, redirecionar para ela, senão vai para PF também
            header('Location: ../HTML/inicio_pessoa_fisica.html');
        }
        exit;
    } else {
        // Senha incorreta
        $title = 'Erro no login';
        $messages = ['Senha incorreta.'];
        $type = 'error';
        $links = ['../HTML/login.html' => 'Voltar ao login'];
        include __DIR__ . '/partials/layout.php';
        exit;
    }
} else {
    // Usuário não encontrado
    $title = 'Erro no login';
    $messages = ['CPF/CNPJ não encontrado no sistema.'];
    $type = 'error';
    $links = ['../HTML/login.html' => 'Voltar ao login'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// (sem placeholder)
