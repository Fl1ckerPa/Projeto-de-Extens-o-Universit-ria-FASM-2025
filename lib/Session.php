<?php
/**
 * Session - Classe para manipulação de sessão
 * Adaptada do AtomPHP com melhorias de segurança
 */

class Session
{
    /**
     * Configurações de sessão seguras
     */
    public static function startSecure()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurar nome da sessão ANTES de iniciar
            if (session_name() === 'PHPSESSID') {
                session_name('DESCUBRA_SESSION');
            }
            
            // Configurações de segurança
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Lax'); // Lax permite cookies em navegação cross-site
            
            // Configurar caminho do cookie para o diretório raiz
            $cookiePath = '/';
            if (isset($_SERVER['CONTEXT_PREFIX'])) {
                $cookiePath = $_SERVER['CONTEXT_PREFIX'];
            }
            ini_set('session.cookie_path', $cookiePath);
            
            // Tempo de vida do cookie (2 horas)
            ini_set('session.cookie_lifetime', 7200);
            
            session_start();
            
            // Regenerar ID de sessão periodicamente (a cada 5 minutos)
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }

    static public function set($key, $value)
    {
        self::startSecure();
        $_SESSION[$key] = $value;
    }

    static public function get($key, $default = false)
    {
        self::startSecure();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return $default;
        }
    }

    static public function destroy($key)
    {
        self::startSecure();
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

    /**
     * Limpa toda a sessão
     */
    static public function clear()
    {
        self::startSecure();
        $_SESSION = [];
    }

    /**
     * Destrói a sessão completamente
     */
    static public function destroyAll()
    {
        self::startSecure();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Verifica se uma chave existe na sessão
     */
    static public function has($key)
    {
        self::startSecure();
        return isset($_SESSION[$key]);
    }
}

