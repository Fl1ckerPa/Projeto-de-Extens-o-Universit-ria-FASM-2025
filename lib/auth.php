<?php
/**
 * Middleware de Autenticação
 * Controla acesso e permissões de usuários
 */

/**
 * Verifica se usuário está autenticado
 */
function verificarAutenticacao()
{
    if (!Session::get('user_id')) {
        if (Request::isPost() || strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
            Response::error('Acesso não autorizado. Faça login para continuar.', null, 401);
        } else {
            header('Location: ../HTML/login.html');
            exit;
        }
    }
}

/**
 * Verifica se usuário é administrador
 */
function verificarAdmin()
{
    verificarAutenticacao();
    
    // Admin moderno via tabela de domínio: role_code = 'GEST' ou 'ADMIN'
    $role = Session::get('role_code');
    if ($role === 'GEST' || $role === 'ADMIN') {
        return;
    }
    // Compatibilidade legada: user_type = 'admin'
    if (Session::get('user_type') !== 'admin') {
        Response::error('Acesso negado. Apenas administradores podem acessar esta área.', null, 403);
    }
}

/**
 * Verifica se usuário é empresa
 */
function verificarEmpresa()
{
    verificarAutenticacao();
    
    // Admin sempre pode
    $role = Session::get('role_code');
    if ($role === 'GEST' || $role === 'ADMIN') {
        return;
    }
    if (Session::get('user_type') !== 'pj') {
        Response::error('Acesso negado. Apenas empresas podem acessar esta área.', null, 403);
    }
}

/**
 * Verifica se usuário é candidato (pessoa física)
 */
function verificarCandidato()
{
    verificarAutenticacao();
    
    // Admin sempre pode
    $role = Session::get('role_code');
    if ($role === 'GEST' || $role === 'ADMIN') {
        return;
    }
    if (Session::get('user_type') !== 'pf') {
        Response::error('Acesso negado. Apenas candidatos podem acessar esta área.', null, 403);
    }
}

/**
 * Obtém ID do usuário logado
 */
function getUserId()
{
    return Session::get('user_id');
}

/**
 * Obtém tipo do usuário logado
 */
function getUserType()
{
    return Session::get('user_type');
}

/**
 * Verifica se usuário pode acessar recurso (proprietário ou admin)
 */
function verificarPermissaoRecurso($recursoId, $campoId = 'user_id', $tabela = null)
{
    $userId = getUserId();
    $userType = getUserType();
    $role = Session::get('role_code');
    
    // Admin tem acesso total
    if ($userType === 'admin' || $role === 'GEST' || $role === 'ADMIN') {
        return true;
    }
    
    // Se não é admin, verifica se é o dono do recurso
    if ($tabela && $recursoId) {
        $db = new Database();
        $recurso = $db->table($tabela)
            ->where('id', $recursoId)
            ->where($campoId, $userId)
            ->first();
        
        return !empty($recurso);
    }
    
    return false;
}

