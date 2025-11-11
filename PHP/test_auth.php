<?php
/**
 * Script de teste de autenticação
 * Testa se a sessão está funcionando corretamente
 */

require_once __DIR__ . '/../lib/bootstrap.php';

Session::startSecure();

header('Content-Type: application/json; charset=utf-8');

$dados = [
    'session_id' => session_id(),
    'session_status' => session_status(),
    'user_id' => Session::get('user_id'),
    'user_type' => Session::get('user_type'),
    'user_nome' => Session::get('user_nome'),
    'user_email' => Session::get('user_email'),
    'pessoa_id' => Session::get('pessoa_id'),
    'role_code' => Session::get('role_code'),
    'empresa_id' => Session::get('empresa_id'),
    'all_session' => $_SESSION
];

echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

