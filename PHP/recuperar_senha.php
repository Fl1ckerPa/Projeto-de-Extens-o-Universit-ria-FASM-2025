<?php
/**
 * Processa solicitação de recuperação de senha
 * Envia email com token para reset de senha
 */

require_once __DIR__ . '/../lib/bootstrap.php';

if (!Request::isPost()) {
    Helper::jsonError('Método não permitido', null, 405);
}

$erros = [];

// Coletar dados
$email = trim(Request::post('email', ''));
$cpf = preg_replace('/\D+/', '', Request::post('cpf', ''));
$cnpj = preg_replace('/\D+/', '', Request::post('cnpj', ''));

// Validação
if (empty($email)) {
    $erros[] = 'O campo email é obrigatório.';
} elseif (!Helper::validarEmail($email)) {
    $erros[] = 'Email inválido.';
}

// Determinar tipo de usuário e validar CPF/CNPJ
$tipo = null;
$identificador = null;

if (!empty($cpf)) {
    if (strlen($cpf) !== 11) {
        $erros[] = 'CPF deve conter 11 dígitos.';
    } else {
        $tipo = 'pf';
        $identificador = $cpf;
    }
} elseif (!empty($cnpj)) {
    if (strlen($cnpj) !== 14) {
        $erros[] = 'CNPJ deve conter 14 dígitos.';
    } else {
        $tipo = 'pj';
        $identificador = $cnpj;
    }
} else {
    $erros[] = 'Informe CPF (Pessoa Física) ou CNPJ (Pessoa Jurídica).';
}

if (!empty($erros)) {
    $title = 'Erros na solicitação';
    $messages = $erros;
    $type = 'error';
    $links = ['../HTML/login.html' => 'Voltar ao login'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// Buscar usuário no banco
$db = new Database();
$tabela = $tipo === 'pj' ? 'usuarios_pj' : 'usuarios_pf';
$campo = $tipo === 'pj' ? 'cnpj' : 'cpf';

// Formatar identificador
$identificadorFormatado = $tipo === 'pj' 
    ? Helper::formatarCNPJ($identificador) 
    : Helper::formatarCPF($identificador);

$usuario = null;
$emailExiste = false;

try {
    // Primeiro, verificar se o email existe em qualquer tabela
    $db->dbClear();
    $emailPF = $db->table('usuarios_pf')->where('email', $email)->first();
    $db->dbClear();
    $emailPJ = $db->table('usuarios_pj')->where('email', $email)->first();
    
    $emailExiste = ($emailPF !== null || $emailPJ !== null);
    
    // Se o email não existe, retornar erro específico
    if (!$emailExiste) {
        $title = 'Email não cadastrado';
        $messages = [
            'O email informado não está cadastrado no sistema.',
            'Verifique se o email está correto ou faça um cadastro primeiro.'
        ];
        $type = 'error';
        $links = [
            '../HTML/cadastro.html' => 'Fazer cadastro',
            '../HTML/login.html' => 'Voltar ao login'
        ];
        include __DIR__ . '/partials/layout.php';
        exit;
    }
    
    // Buscar usuário por CPF/CNPJ e email
    $db->dbClear();
    $usuario = $db->table($tabela)
        ->where($campo, $identificadorFormatado)
        ->where('email', $email)
        ->first();
    
    // Se não encontrou, tentar sem máscara
    if (!$usuario) {
        $db->dbClear();
        $todos = $db->table($tabela)->where('email', $email)->findAll();
        foreach ($todos as $u) {
            $valorBanco = preg_replace('/\D+/', '', $u[$campo]);
            if ($valorBanco === $identificador) {
                $usuario = $u;
                break;
            }
        }
    }
} catch (\Exception $e) {
    error_log("Erro ao buscar usuário: " . $e->getMessage());
}

if (!$usuario) {
    $title = 'Dados não conferem';
    $messages = [
        'O email está cadastrado, mas o CPF/CNPJ informado não corresponde a este email.',
        'Verifique se o CPF/CNPJ está correto.',
        'Se você esqueceu seus dados, entre em contato com o suporte.'
    ];
    $type = 'error';
    $links = [
        '../HTML/login.html' => 'Voltar ao login'
    ];
    include __DIR__ . '/partials/layout.php';
    exit;
}

// Gerar token de reset
$token = Helper::gerarToken(32);
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token válido por 1 hora

try {
    // Invalidar tokens anteriores do mesmo email
    $db->dbUpdate("UPDATE reset_tokens SET used = 1 WHERE email = ? AND tipo_usuario = ?", [$email, $tipo]);
    
    // Inserir novo token
    $db->dbInsert(
        "INSERT INTO reset_tokens (email, token, tipo_usuario, expires_at) VALUES (?, ?, ?, ?)",
        [$email, $token, $tipo, $expiresAt]
    );
    
    // Preparar URL de reset
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
        . '://' . $_SERVER['HTTP_HOST'] 
        . dirname(dirname($_SERVER['SCRIPT_NAME']));
    
    $resetUrl = $baseUrl . '/HTML/reset_senha.html?token=' . $token;
    
    // Preparar email
    $assunto = 'Recuperação de Senha - Descubra Muriaé';
    $mensagem = "
    <!DOCTYPE html>
    <html lang='pt-br'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #0d6efd;'>Descubra Muriaé</h1>
            </div>
            
            <h2 style='color: #333;'>Recuperação de Senha</h2>
            
            <p>Olá, <strong>" . htmlspecialchars($usuario['nome']) . "</strong>!</p>
            
            <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
            
            <p>Clique no botão abaixo para redefinir sua senha:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . htmlspecialchars($resetUrl) . "' 
                   style='display: inline-block; padding: 12px 30px; background-color: #0d6efd; 
                          color: #ffffff; text-decoration: none; border-radius: 5px; 
                          font-weight: bold;'>Redefinir Senha</a>
            </div>
            
            <p>Ou copie e cole o link abaixo no seu navegador:</p>
            <p style='word-break: break-all; color: #666; font-size: 12px;'>" . htmlspecialchars($resetUrl) . "</p>
            
            <p><strong>Este link expira em 1 hora.</strong></p>
            
            <p>Se você não solicitou esta recuperação de senha, ignore este email.</p>
            
            <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
            
            <p style='font-size: 12px; color: #666;'>
                Este é um email automático, por favor não responda.<br>
                Descubra Muriaé - Sistema de Vagas de Emprego
            </p>
        </div>
    </body>
    </html>
    ";
    
    // Enviar email
    $emailEnviado = Helper::enviarEmail($email, $assunto, $mensagem);
    
    if ($emailEnviado) {
        $title = 'Email enviado com sucesso!';
        $messages = [
            'Um email com instruções para redefinir sua senha foi enviado para:',
            '<strong>' . htmlspecialchars($email) . '</strong>',
            'Verifique sua caixa de entrada e siga as instruções.',
            'O link de recuperação expira em 1 hora.'
        ];
        $type = 'success';
        $links = ['../HTML/login.html' => 'Voltar ao login'];
        include __DIR__ . '/partials/layout.php';
    } else {
        $title = 'Erro ao enviar email';
        $messages = [
            'Não foi possível enviar o email de recuperação.',
            'Por favor, tente novamente mais tarde ou entre em contato com o suporte.'
        ];
        $type = 'error';
        $links = ['../HTML/login.html' => 'Voltar ao login'];
        include __DIR__ . '/partials/layout.php';
    }
    
} catch (\Exception $e) {
    error_log("Erro ao processar recuperação de senha: " . $e->getMessage());
    $title = 'Erro ao processar solicitação';
    $messages = ['Erro ao processar solicitação de recuperação de senha. Tente novamente mais tarde.'];
    $type = 'error';
    $links = ['../HTML/login.html' => 'Voltar ao login'];
    include __DIR__ . '/partials/layout.php';
    exit;
}

