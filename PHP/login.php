<?php
/**
 * Processa login (PF ou PJ) utilizando o esquema normalizado.
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

// Validação de identificadores
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
$pdo = $db->connect();

try {
    Schema::ensureNormalizedSchema($pdo);

    if ($tipo === 'pf') {
        // Login PF: busca por CPF na tabela pessoa
        $stmt = $pdo->prepare('SELECT u.usuario_id, u.senha_hash, p.nome, p.email, p.pessoa_id, ut.codigo AS role_codigo
                               FROM pessoa p
                               INNER JOIN usuario u ON u.pessoa_id = p.pessoa_id
                               INNER JOIN usuario_tipo ut ON ut.usuario_tipo_id = u.usuario_tipo_id
                               WHERE p.cpf = ? AND u.ativo = 1 AND p.ativo = 1
                               LIMIT 1');
        $stmt->execute([$cpf]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
        $empresaId = null;
    } else {
        // Login PJ: busca empresa por CNPJ e depois o usuário associado
        $stmt = $pdo->prepare('SELECT empresa_id, email, nome_social FROM empresa WHERE cnpj = ? AND ativo = 1 LIMIT 1');
        $stmt->execute([$cnpj]);
        $empresa = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$empresa) {
            $usuario = false;
            $empresaId = null;
        } else {
            $empresaId = (int)$empresa['empresa_id'];
            // Buscar usuário pelo email da empresa (login)
            $stmt = $pdo->prepare('SELECT u.usuario_id, u.senha_hash, p.nome, p.email, p.pessoa_id, ut.codigo AS role_codigo
                                   FROM usuario u
                                   INNER JOIN pessoa p ON p.pessoa_id = u.pessoa_id
                                   INNER JOIN usuario_tipo ut ON ut.usuario_tipo_id = u.usuario_tipo_id
                                   WHERE u.login = ? AND u.ativo = 1 AND p.ativo = 1
                                   LIMIT 1');
            $stmt->execute([$empresa['email']]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Se não encontrou usuário, buscar pessoa pelo email e criar usuário se necessário
            if (!$usuario) {
                // Buscar pessoa pelo email
                $stmt = $pdo->prepare('SELECT pessoa_id, nome, email FROM pessoa WHERE email = ? AND ativo = 1 LIMIT 1');
                $stmt->execute([$empresa['email']]);
                $pessoa = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($pessoa) {
                    // Buscar senha_hash de algum usuário antigo ou usar hash padrão (não recomendado)
                    // Por segurança, não permitir login sem usuário válido
                    $usuario = false;
                } else {
                    // Empresa sem pessoa/usuário - não pode fazer login
                    $usuario = false;
                }
            }
        }
    }
} catch (\Exception $e) {
    error_log('Erro ao realizar login: ' . $e->getMessage());
    $usuario = false;
}

if (!$usuario) {
    $title = 'Usuário não encontrado';
    $messages = [
        'O identificador informado (CPF/CNPJ) não está cadastrado.',
        'Realize o cadastro antes de tentar fazer login.'
    ];
    $type = 'error';
    $links = [
        '../HTML/cadastro.html' => 'Fazer cadastro',
        '../HTML/login.html' => 'Voltar ao login'
    ];
    include __DIR__ . '/partials/layout.php';
    exit;
}

if (!Helper::verificarSenha($senha, $usuario['senha_hash'])) {
    $title = 'Erro no login';
    $messages = ['Senha incorreta.'];
    $type = 'error';
    $links = ['../HTML/login.html' => 'Voltar ao login'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// Garantir que a sessão está iniciada
Session::startSecure();

// Salvar dados na sessão
Session::set('user_id', $usuario['usuario_id']);
Session::set('user_type', $tipo);
Session::set('user_nome', $usuario['nome']);
Session::set('user_email', $usuario['email']);
Session::set('pessoa_id', $usuario['pessoa_id']);
Session::set('last_activity', time()); // Atualizar última atividade

if (!empty($usuario['role_codigo'])) {
    Session::set('role_code', $usuario['role_codigo']); // e.g., ANUNC, GEST, CONT, ADMIN
}
if (isset($empresaId) && $empresaId) {
    Session::set('empresa_id', $empresaId);
}

// Garantir que a sessão foi salva (PHP salva automaticamente ao final do script)

$redirect = Request::post('redirect', Request::get('redirect', ''));
if (!empty($redirect)) {
    header('Location: ' . urldecode($redirect));
} else {
    header('Location: ../HTML/index.html');
}
exit;
