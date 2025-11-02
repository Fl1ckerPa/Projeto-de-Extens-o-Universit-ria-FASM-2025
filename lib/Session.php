<?php
/**
 * Session - Classe para manipulação de sessão
 * Adaptada do AtomPHP
 */

class Session
{
    static public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    static public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return false;
        }
    }

    static public function destroy($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    static public function getDestroy($key)
    {
        $valor = Session::get($key);
        Session::destroy($key);
        return $valor;
    }
}

