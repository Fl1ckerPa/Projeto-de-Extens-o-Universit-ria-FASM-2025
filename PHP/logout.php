<?php
/**
 * Endpoint para logout do usuário
 * Destroi a sessão e redireciona para a página de login
 */

require_once __DIR__ . '/../lib/bootstrap.php';

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se é desejado matar a sessão, também delete o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir a sessão
session_destroy();

// Se for uma requisição AJAX, retornar JSON
if (Request::isPost() || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
    Response::success('Logout realizado com sucesso');
} else {
    // Redirecionar para a página de login
    header('Location: ../HTML/login.html');
    exit;
}

