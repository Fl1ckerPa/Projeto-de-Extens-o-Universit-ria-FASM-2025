<?php
/**
 * Request - Classe para manipulação de requisições HTTP
 * Adaptada do AtomPHP para uso sem MVC
 */

class Request
{
    /**
     * Retorna valor de GET
     */
    public static function get($key, $default = null)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Retorna valor de POST
     */
    public static function post($key, $default = null)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Verifica se é POST
     */
    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Verifica se é GET
     */
    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Retorna todos os dados POST
     */
    public static function all()
    {
        return $_POST;
    }

    /**
     * Lê dados JSON do corpo da requisição
     */
    public static function getJson()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }
}

