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

            // Buscar usuário usando schema normalizado
            $pdo = $db->connect();
            $stmt = $pdo->prepare("SELECT u.senha_hash, u.usuario_id 
                                   FROM usuario u 
                                   WHERE u.usuario_id = ? AND u.ativo = 1
                                   LIMIT 1");
            $stmt->execute([$userId]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$usuario) {
                Response::error('Usuário não encontrado');
            }

            // Verificar senha atual
            if (!Helper::verificarSenha($senhaAtual, $usuario['senha_hash'])) {
                Response::error('Senha atual incorreta');
            }

            // Atualizar senha
            $stmt = $pdo->prepare("UPDATE usuario SET senha_hash = ? WHERE usuario_id = ?");
            $resultado = $stmt->execute([Helper::hashSenha($senhaNova), $userId]);

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
            
            // Verificar senha usando schema normalizado
            $pdo = $db->connect();
            $stmt = $pdo->prepare("SELECT u.senha_hash, u.usuario_id, p.pessoa_id
                                   FROM usuario u
                                   INNER JOIN pessoa p ON p.pessoa_id = u.pessoa_id
                                   WHERE u.usuario_id = ? AND u.ativo = 1 AND p.ativo = 1
                                   LIMIT 1");
            $stmt->execute([$userId]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$usuario) {
                Response::error('Usuário não encontrado');
            }

            if (!Helper::verificarSenha($senha, $usuario['senha_hash'])) {
                Response::error('Senha incorreta');
            }

            // Desativar conta (soft delete) - desativar pessoa e usuário
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE pessoa SET ativo = 0 WHERE pessoa_id = ?");
            $stmt->execute([$usuario['pessoa_id']]);
            
            $stmt = $pdo->prepare("UPDATE usuario SET ativo = 0 WHERE usuario_id = ?");
            $resultado = $stmt->execute([$userId]);
            
            // Se for PJ, desativar empresa também
            if ($userType === 'pj') {
                $empresaId = Session::get('empresa_id');
                if ($empresaId) {
                    $stmt = $pdo->prepare("UPDATE empresa SET ativo = 0 WHERE empresa_id = ?");
                    $stmt->execute([$empresaId]);
                }
            }
            
            $pdo->commit();

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

