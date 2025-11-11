<?php
/**
 * Gerenciamento de Perfil
 * Permite candidatos visualizarem e editarem seus perfis
 */

require_once __DIR__ . '/../lib/bootstrap.php';

verificarAutenticacao();

$acao = Request::post('acao', Request::get('acao', 'visualizar'));
$userId = getUserId();
$userType = getUserType();

$db = new Database();

switch ($acao) {
    case 'visualizar':
        // Buscar dados do perfil
        try {
            if ($userType === 'pf') {
                $usuario = $db->table('usuarios_pf')
                    ->where('id', $userId)
                    ->first();
                
                // Buscar currículo se existir
                $curriculo = $db->table('curriculos')
                    ->where('email', $usuario['email'] ?? '')
                    ->first();
                
                Response::success('Perfil carregado!', [
                    'usuario' => $usuario,
                    'curriculo' => $curriculo
                ]);
            } elseif ($userType === 'pj') {
                $usuario = $db->table('usuarios_pj')
                    ->where('id', $userId)
                    ->first();
                
                // Buscar estabelecimento se existir
                $sqlEstab = "SELECT * FROM estabelecimento WHERE email = ? LIMIT 1";
                $resultEstab = $db->dbSelect($sqlEstab, [$usuario['email'] ?? '']);
                $empresa = $db->dbBuscaArray($resultEstab);
                
                // Tentar buscar dados completos da tabela empresas se existir
                $empresaCompleta = null;
                try {
                    $sqlEmpresa = "SELECT * FROM empresas WHERE email = ? LIMIT 1";
                    $resultEmpresa = $db->dbSelect($sqlEmpresa, [$usuario['email'] ?? '']);
                    $empresaCompleta = $db->dbBuscaArray($resultEmpresa);
                } catch (\Exception $e) {
                    // Tabela empresas pode não existir, usar apenas estabelecimento
                }
                
                // Usar dados completos se disponível, senão usar estabelecimento
                $dadosEmpresa = $empresaCompleta ? $empresaCompleta : $empresa;
                
                Response::success('Perfil carregado!', [
                    'usuario' => $usuario,
                    'empresa' => $dadosEmpresa,
                    'tem_empresa' => !empty($dadosEmpresa)
                ]);
            } else {
                Response::error('Tipo de usuário não reconhecido');
            }
        } catch (\Exception $e) {
            Response::error('Erro ao carregar perfil: ' . $e->getMessage());
        }
        break;

    case 'atualizar':
        // Atualizar dados do perfil
        try {
            $dados = [
                'nome' => Request::post('nome', ''),
                'email' => Request::post('email', '')
            ];

            // Validação
            if (empty($dados['nome'])) {
                Response::error('Nome é obrigatório');
            }

            if (!Helper::validarEmail($dados['email'])) {
                Response::error('Email inválido');
            }

            // Atualizar senha se fornecida
            $senha = Request::post('senha', '');
            $senhaNova = Request::post('senha_nova', '');
            
            if (!empty($senha) && !empty($senhaNova)) {
                // Verificar senha atual
                $tabela = $userType === 'pf' ? 'usuarios_pf' : 'usuarios_pj';
                $usuario = $db->table($tabela)
                    ->where('id', $userId)
                    ->first();
                
                if (!Helper::verificarSenha($senha, $usuario['senha'])) {
                    Response::error('Senha atual incorreta');
                }
                
                // Validar nova senha
                if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $senhaNova)) {
                    Response::error('A nova senha deve ter entre 8 e 20 caracteres, letras, números e um caractere especial.');
                }
                
                $dados['senha'] = Helper::hashSenha($senhaNova);
            }

            // Atualizar no banco
            $tabela = $userType === 'pf' ? 'usuarios_pf' : 'usuarios_pj';
            $resultado = $db->table($tabela)
                ->where('id', $userId)
                ->update($dados);

            if ($resultado) {
                // Atualizar sessão
                Session::set('user_nome', $dados['nome']);
                Session::set('user_email', $dados['email']);
                
                Response::success('Perfil atualizado com sucesso!');
            } else {
                Response::error('Erro ao atualizar perfil');
            }

        } catch (\Exception $e) {
            Response::error('Erro ao atualizar perfil: ' . $e->getMessage());
        }
        break;

    default:
        Response::error('Ação não reconhecida');
        break;
}

