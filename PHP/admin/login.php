<?php
/**
 * Login de Administrador
 * Endpoint para autenticação de administradores
 */

require_once __DIR__ . '/../../lib/bootstrap.php';

if (!Request::isPost()) {
    Response::error('Método não permitido', null, 405);
}

$email = Request::post('email', '');
$senha = Request::post('senha', '');

// Validação
$erros = [];

if (empty($email)) {
    $erros[] = 'Email é obrigatório';
} elseif (!Helper::validarEmail($email)) {
    $erros[] = 'Email inválido';
}

if (empty($senha)) {
    $erros[] = 'Senha é obrigatória';
}

if (!empty($erros)) {
    Response::error('Erro na validação', $erros);
}

// Autenticação
$db = new Database();

try {
    $admin = $db->table('administradores')
        ->where('email', $email)
        ->where('ativo', 1)
        ->first();

    if (!$admin) {
        Response::error('Email ou senha incorretos');
    }

    // Verificar senha
    if (!Helper::verificarSenha($senha, $admin['senha'])) {
        Response::error('Email ou senha incorretos');
    }

    // Salvar dados na sessão
    Session::set('user_id', $admin['id']);
    Session::set('user_type', 'admin');
    Session::set('user_nome', $admin['nome']);
    Session::set('user_email', $admin['email']);

    Response::success('Login realizado com sucesso!', [
        'id' => $admin['id'],
        'nome' => $admin['nome'],
        'email' => $admin['email']
    ]);

} catch (\Exception $e) {
    Response::error('Erro ao realizar login: ' . $e->getMessage());
}

