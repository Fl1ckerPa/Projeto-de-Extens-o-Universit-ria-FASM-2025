<?php
/**
 * Processa reset de senha usando token
 */

require_once __DIR__ . '/../lib/bootstrap.php';

if (!Request::isPost()) {
    Helper::jsonError('Método não permitido', null, 405);
}

$erros = [];

// Coletar dados
$token = trim(Request::post('token', ''));
$senha = Request::post('senha', '');
$senhaVerif = Request::post('senhaverif', '');

// Validação
if (empty($token)) {
    $erros[] = 'Token de recuperação não informado.';
}

if (empty($senha) || empty($senhaVerif)) {
    $erros[] = 'Ambos os campos de senha são obrigatórios.';
} elseif ($senha !== $senhaVerif) {
    $erros[] = 'As senhas não são idênticas.';
} elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $senha)) {
    $erros[] = 'A senha deve ter entre 8 e 20 caracteres, conter letras, números e um caractere especial.';
}

if (!empty($erros)) {
    $title = 'Erros no reset de senha';
    $messages = $erros;
    $type = 'error';
    $links = ['../HTML/reset_senha.html?token=' . urlencode($token) => 'Tentar novamente'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// Buscar token no banco
$db = new Database();

try {
    $tokenData = $db->table('reset_tokens')
        ->where('token', $token)
        ->where('used', 0)
        ->first();
    
    if (!$tokenData) {
        $title = 'Token inválido';
        $messages = [
            'O token de recuperação é inválido ou já foi utilizado.',
            'Solicite uma nova recuperação de senha.'
        ];
        $type = 'error';
        $links = ['../HTML/login.html' => 'Voltar ao login'];
        include __DIR__ . '/partials/layout.php';
        exit;
    }
    
    // Verificar se token expirou
    if (strtotime($tokenData['expires_at']) < time()) {
        $title = 'Token expirado';
        $messages = [
            'O token de recuperação expirou.',
            'Solicite uma nova recuperação de senha.'
        ];
        $type = 'error';
        $links = ['../HTML/login.html' => 'Voltar ao login'];
        include __DIR__ . '/partials/layout.php';
        exit;
    }
    
    // Buscar usuário
    $tabela = $tokenData['tipo_usuario'] === 'pj' ? 'usuarios_pj' : 'usuarios_pf';
    $email = $tokenData['email'];
    
    $usuario = $db->table($tabela)
        ->where('email', $email)
        ->first();
    
    if (!$usuario) {
        $title = 'Usuário não encontrado';
        $messages = ['Usuário não encontrado no sistema.'];
        $type = 'error';
        $links = ['../HTML/login.html' => 'Voltar ao login'];
        include __DIR__ . '/partials/layout.php';
        exit;
    }
    
    // Atualizar senha
    $novaSenhaHash = Helper::hashSenha($senha);
    
    $db->dbUpdate(
        "UPDATE {$tabela} SET senha = ? WHERE id = ?",
        [$novaSenhaHash, $usuario['id']]
    );
    
    // Marcar token como usado
    $db->dbUpdate(
        "UPDATE reset_tokens SET used = 1 WHERE token = ?",
        [$token]
    );
    
    $title = 'Senha redefinida com sucesso!';
    $messages = [
        'Sua senha foi redefinida com sucesso!',
        'Agora você pode fazer login com sua nova senha.'
    ];
    $type = 'success';
    $links = ['../HTML/login.html' => 'Fazer login'];
    include __DIR__ . '/partials/layout.php';
    
} catch (\Exception $e) {
    error_log("Erro ao resetar senha: " . $e->getMessage());
    $title = 'Erro ao processar';
    $messages = ['Erro ao processar redefinição de senha. Tente novamente mais tarde.'];
    $type = 'error';
    $links = ['../HTML/login.html' => 'Voltar ao login'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

