<?php
/**
 * Helper - Funções auxiliares e validações específicas
 */

class Helper
{
    /**
     * Valida CPF
     */
    public static function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Validação dos dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Valida CNPJ
     */
    public static function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Validação dos dígitos verificadores
        $length = strlen($cnpj) - 2;
        $numbers = substr($cnpj, 0, $length);
        $digits = substr($cnpj, $length);
        $sum = 0;
        $pos = $length - 7;
        
        for ($i = $length; $i >= 1; $i--) {
            $sum += $numbers[$length - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $result = $sum % 11 < 2 ? 0 : 11 - $sum % 11;
        if ($result != $digits[0]) {
            return false;
        }
        
        $length = $length + 1;
        $numbers = substr($cnpj, 0, $length);
        $sum = 0;
        $pos = $length - 7;
        
        for ($i = $length; $i >= 1; $i--) {
            $sum += $numbers[$length - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $result = $sum % 11 < 2 ? 0 : 11 - $sum % 11;
        if ($result != $digits[1]) {
            return false;
        }
        
        return true;
    }

    /**
     * Valida telefone brasileiro
     */
    public static function validarTelefone($telefone)
    {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        return strlen($telefone) >= 10 && strlen($telefone) <= 11;
    }

    /**
     * Valida CEP brasileiro
     */
    public static function validarCEP($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return strlen($cep) == 8;
    }

    /**
     * Formata CPF
     */
    public static function formatarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) == 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
        }
        return $cpf;
    }

    /**
     * Formata CNPJ
     */
    public static function formatarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) == 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
        }
        return $cnpj;
    }

    /**
     * Formata telefone
     */
    public static function formatarTelefone($telefone)
    {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        if (strlen($telefone) == 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        } elseif (strlen($telefone) == 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        }
        return $telefone;
    }

    /**
     * Formata CEP
     */
    public static function formatarCEP($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep) == 8) {
            return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
        }
        return $cep;
    }

    /**
     * Limpa string removendo tags e espaços extras
     */
    public static function limpar($valor)
    {
        return htmlspecialchars(stripslashes(trim((string)$valor)));
    }

    /**
     * Sanitiza array de dados
     */
    public static function sanitizarArray($array)
    {
        return array_map(function($valor) {
            if (is_array($valor)) {
                return Helper::sanitizarArray($valor);
            }
            return Helper::limpar($valor);
        }, $array);
    }

    /**
     * Verifica se string está vazia ou nula
     */
    public static function vazio($valor)
    {
        return empty($valor) || trim($valor) === '';
    }

    /**
     * Gera hash seguro para senhas
     */
    public static function hashSenha($senha)
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    /**
     * Verifica hash de senha
     */
    public static function verificarSenha($senha, $hash)
    {
        return password_verify($senha, $hash);
    }

    /**
     * Gera token aleatório
     */
    public static function gerarToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Valida formato de email
     */
    public static function validarEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Retorna JSON response
     */
    public static function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Retorna resposta de sucesso JSON
     */
    public static function jsonSuccess($mensagem, $dados = null, $statusCode = 200)
    {
        $response = ['sucesso' => true, 'mensagem' => $mensagem];
        if ($dados !== null) {
            $response['dados'] = $dados;
        }
        Helper::jsonResponse($response, $statusCode);
    }

    /**
     * Retorna resposta de erro JSON
     */
    public static function jsonError($mensagem, $erros = null, $statusCode = 400)
    {
        $response = ['sucesso' => false, 'mensagem' => $mensagem];
        if ($erros !== null) {
            $response['erros'] = is_array($erros) ? $erros : [$erros];
        }
        Helper::jsonResponse($response, $statusCode);
    }
}

