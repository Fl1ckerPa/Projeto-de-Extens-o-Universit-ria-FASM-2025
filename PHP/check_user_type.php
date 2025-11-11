<?php
/**
 * Endpoint para verificar tipo de usuário logado
 * Retorna informações básicas do usuário para redirecionamento
 * Retorna null se usuário não estiver autenticado (para permitir acesso público)
 */

require_once __DIR__ . '/../lib/bootstrap.php';

// Iniciar sessão segura
Session::startSecure();

// Debug: verificar se sessão está funcionando
$sessionId = session_id();
$userId = Session::get('user_id');

// Se não tem user_id na sessão, retornar não autenticado
if (!$userId) {
    // Retornar sucesso mas indicar que não está autenticado (compatível com auth.js)
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'status' => 'success',
        'sucesso' => true,
        'mensagem' => 'Usuário não autenticado',
        'dados' => [
            'user_type' => null,
            'user_id' => null,
            'user_nome' => null,
            'user_email' => null,
            'autenticado' => false,
            'session_id' => $sessionId // Debug
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userType = Session::get('user_type');
$userId = Session::get('user_id');
$userNome = Session::get('user_nome');
$userEmail = Session::get('user_email');
$roleCode = Session::get('role_code');
$empresaId = Session::get('empresa_id');

// Retornar formato compatível com auth.js
http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'sucesso' => true,
    'mensagem' => 'Tipo de usuário obtido',
    'dados' => [
        'user_type' => $userType,
        'user_id' => $userId,
        'user_nome' => $userNome,
        'user_email' => $userEmail,
        'role_code' => $roleCode,
        'empresa_id' => $empresaId,
        'autenticado' => true
    ]
], JSON_UNESCAPED_UNICODE);
exit;

