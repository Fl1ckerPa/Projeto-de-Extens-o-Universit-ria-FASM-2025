<?php
/**
 * Endpoint para verificar tipo de usuário logado
 * Retorna informações básicas do usuário para redirecionamento
 * Retorna null se usuário não estiver autenticado (para permitir acesso público)
 */

require_once __DIR__ . '/../lib/bootstrap.php';

// Verificar se está autenticado
if (!Session::get('user_id')) {
    // Retornar sucesso mas indicar que não está autenticado
    Response::success('Usuário não autenticado', [
        'user_type' => null,
        'user_id' => null,
        'user_nome' => null,
        'user_email' => null,
        'autenticado' => false
    ]);
}

$userType = Session::get('user_type');
$userId = Session::get('user_id');
$userNome = Session::get('user_nome');
$userEmail = Session::get('user_email');

Response::success('Tipo de usuário obtido', [
    'user_type' => $userType,
    'user_id' => $userId,
    'user_nome' => $userNome,
    'user_email' => $userEmail,
    'autenticado' => true
]);

