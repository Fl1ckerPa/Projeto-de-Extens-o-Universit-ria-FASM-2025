<?php
/**
 * Response - Classe para padronização de respostas JSON
 * Garante formato consistente em todas as APIs
 */

class Response
{
    /**
     * Retorna resposta de sucesso padronizada
     */
    public static function success($mensagem, $dados = null, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'sucesso' => true,
            'status' => 'success', // Compatibilidade com código JavaScript existente
            'mensagem' => $mensagem
        ];
        
        if ($dados !== null) {
            $response['dados'] = $dados;
            $response['data'] = $dados; // Compatibilidade adicional
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Retorna resposta de erro padronizada
     */
    public static function error($mensagem, $erros = null, $statusCode = 400)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'sucesso' => false,
            'mensagem' => $mensagem
        ];
        
        if ($erros !== null) {
            $response['erros'] = is_array($erros) ? $erros : [$erros];
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Retorna dados paginados
     */
    public static function paginated($dados, $total, $pagina = 1, $porPagina = 10, $mensagem = 'Dados retornados com sucesso')
    {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'sucesso' => true,
            'mensagem' => $mensagem,
            'dados' => $dados,
            'paginacao' => [
                'total' => (int)$total,
                'pagina' => (int)$pagina,
                'por_pagina' => (int)$porPagina,
                'total_paginas' => (int)ceil($total / $porPagina)
            ]
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

