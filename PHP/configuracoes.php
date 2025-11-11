<?php
/**
 * Configurações do Usuário
 * Permite alterar configurações pessoais (senha, preferências, etc)
 */

require_once __DIR__ . '/../lib/bootstrap.php';

verificarAutenticacao();

$acao = Request::post('acao', 'alterar_senha');
$userId = getUserId();
$userType = getUserType();

$db = new Database();

switch ($acao) {
    case 'alterar_senha':
        // Alterar senha do usuário
        try {
            $senhaAtual = Request::post('senha_atual', '');
            $senhaNova = Request::post('senha_nova', '');
            $senhaNovaConfirm = Request::post('senha_nova_confirm', '');

            // Validação
            if (empty($senhaAtual)) {
                Response::error('Senha atual é obrigatória');
            }

            if (empty($senhaNova)) {
                Response::error('Nova senha é obrigatória');
            }

            if ($senhaNova !== $senhaNovaConfirm) {
                Response::error('As novas senhas não coincidem');
            }

            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,20}$/', $senhaNova)) {
                Response::error('A senha deve ter entre 8 e 20 caracteres, letras, números e um caractere especial.');
            }

            // Buscar usuário
            $tabela = $userType === 'pf' ? 'usuarios_pf' : 'usuarios_pj';
            $usuario = $db->table($tabela)
                ->where('id', $userId)
                ->first();

            if (!$usuario) {
                Response::error('Usuário não encontrado');
            }

            // Verificar senha atual
            if (!Helper::verificarSenha($senhaAtual, $usuario['senha'])) {
                Response::error('Senha atual incorreta');
            }

            // Atualizar senha
            $resultado = $db->table($tabela)
                ->where('id', $userId)
                ->update(['senha' => Helper::hashSenha($senhaNova)]);

            if ($resultado) {
                Response::success('Senha alterada com sucesso!');
            } else {
                Response::error('Erro ao alterar senha');
            }

        } catch (\Exception $e) {
            Response::error('Erro ao alterar senha: ' . $e->getMessage());
        }
        break;

    case 'excluir_conta':
        // Excluir conta do usuário (soft delete)
        try {
            $confirmacao = Request::post('confirmar', '');
            
            if ($confirmacao !== 'CONFIRMAR') {
                Response::error('Confirmação inválida. Digite CONFIRMAR para excluir sua conta.');
            }

            $senha = Request::post('senha', '');
            
            // Verificar senha
            $tabela = $userType === 'pf' ? 'usuarios_pf' : 'usuarios_pj';
            $usuario = $db->table($tabela)
                ->where('id', $userId)
                ->first();

            if (!Helper::verificarSenha($senha, $usuario['senha'])) {
                Response::error('Senha incorreta');
            }

            // Desativar conta (soft delete)
            $resultado = $db->table($tabela)
                ->where('id', $userId)
                ->update(['ativo' => 0]);

            if ($resultado) {
                // Limpar sessão
                Session::destroy('user_id');
                Session::destroy('user_type');
                Session::destroy('user_nome');
                Session::destroy('user_email');

                Response::success('Conta excluída com sucesso!');
            } else {
                Response::error('Erro ao excluir conta');
            }

        } catch (\Exception $e) {
            Response::error('Erro ao excluir conta: ' . $e->getMessage());
        }
        break;

    default:
        Response::error('Ação não reconhecida');
        break;
}

