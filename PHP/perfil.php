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
        // Buscar dados do perfil usando schema normalizado
        try {
            $pdo = $db->connect();
            
            // Buscar pessoa e usuário
            $stmt = $pdo->prepare("SELECT p.pessoa_id, p.nome, p.cpf, p.email, p.nascimento, p.sexo, 
                                          u.login, u.usuario_id, ut.codigo as role_code
                                   FROM pessoa p
                                   INNER JOIN usuario u ON u.pessoa_id = p.pessoa_id
                                   INNER JOIN usuario_tipo ut ON ut.usuario_tipo_id = u.usuario_tipo_id
                                   WHERE u.usuario_id = ? AND u.ativo = 1 AND p.ativo = 1
                                   LIMIT 1");
            $stmt->execute([$userId]);
            $pessoa = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$pessoa) {
                Response::error('Usuário não encontrado');
            }
            
            $dadosPerfil = [
                'nome' => $pessoa['nome'],
                'email' => $pessoa['email'],
                'user_nome' => $pessoa['nome'],
                'user_email' => $pessoa['email'],
                'cpf' => $pessoa['cpf'],
                'nascimento' => $pessoa['nascimento'],
                'sexo' => $pessoa['sexo'],
                'pessoa_id' => $pessoa['pessoa_id'],
                'usuario_id' => $pessoa['usuario_id']
            ];
            
            if ($userType === 'pf') {
                // Buscar currículo se existir
                $stmt = $pdo->prepare("SELECT * FROM curriculo WHERE pessoa_id = ? LIMIT 1");
                $stmt->execute([$pessoa['pessoa_id']]);
                $curriculo = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($curriculo) {
                    // Foto pode estar em curriculo.foto
                    if (!empty($curriculo['foto'])) {
                        $dadosPerfil['foto'] = $curriculo['foto'];
                    }
                    $dadosPerfil['curriculo'] = $curriculo;
                }
                
                Response::success('Perfil carregado!', $dadosPerfil);
            } elseif ($userType === 'pj') {
                // Buscar empresa
                $empresaId = Session::get('empresa_id');
                if ($empresaId) {
                    $stmt = $pdo->prepare("SELECT * FROM empresa WHERE empresa_id = ? LIMIT 1");
                    $stmt->execute([$empresaId]);
                    $empresa = $stmt->fetch(\PDO::FETCH_ASSOC);
                    
                    if ($empresa) {
                        $dadosPerfil['empresa'] = $empresa;
                        $dadosPerfil['nome_social'] = $empresa['nome_social'];
                        $dadosPerfil['cnpj'] = $empresa['cnpj'];
                        // Logo pode ser usado como foto também
                        if (!empty($empresa['logo'])) {
                            $dadosPerfil['logo'] = $empresa['logo'];
                            $dadosPerfil['foto'] = $empresa['logo'];
                        }
                    }
                }
                
                Response::success('Perfil carregado!', $dadosPerfil);
            } else {
                Response::success('Perfil carregado!', $dadosPerfil);
            }
        } catch (\Exception $e) {
            Response::error('Erro ao carregar perfil: ' . $e->getMessage());
        }
        break;

    case 'atualizar':
        // Atualizar dados do perfil usando schema normalizado
        try {
            $pdo = $db->connect();
            $pdo->beginTransaction();
            
            // Buscar pessoa_id do usuário
            $stmt = $pdo->prepare("SELECT pessoa_id FROM usuario WHERE usuario_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $usuarioRow = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$usuarioRow) {
                throw new \Exception('Usuário não encontrado');
            }
            
            $pessoaId = (int)$usuarioRow['pessoa_id'];
            
            $nome = Request::post('nome', '');
            $email = Request::post('email', '');

            // Validação
            if (!empty($nome) && strlen($nome) < 3) {
                Response::error('Nome deve ter pelo menos 3 caracteres');
            }

            if (!empty($email) && !Helper::validarEmail($email)) {
                Response::error('Email inválido');
            }

            // Atualizar pessoa
            $camposUpdate = [];
            $valoresUpdate = [];
            
            if (!empty($nome)) {
                $camposUpdate[] = "nome = ?";
                $valoresUpdate[] = $nome;
            }
            
            if (!empty($email)) {
                // Verificar se email já existe (exceto para este usuário)
                $stmt = $pdo->prepare("SELECT pessoa_id FROM pessoa WHERE email = ? AND pessoa_id != ? LIMIT 1");
                $stmt->execute([$email, $pessoaId]);
                if ($stmt->fetch()) {
                    throw new \Exception('Email já está em uso por outro usuário');
                }
                
                $camposUpdate[] = "email = ?";
                $valoresUpdate[] = $email;
            }
            
            if (!empty($camposUpdate)) {
                $valoresUpdate[] = $pessoaId;
                $sql = "UPDATE pessoa SET " . implode(', ', $camposUpdate) . " WHERE pessoa_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($valoresUpdate);
            }
            
            // Atualizar login se email foi alterado
            if (!empty($email)) {
                $stmt = $pdo->prepare("UPDATE usuario SET login = ? WHERE usuario_id = ?");
                $stmt->execute([$email, $userId]);
            }

            $pdo->commit();
            
            // Atualizar sessão
            if (!empty($nome)) {
                Session::set('user_nome', $nome);
            }
            if (!empty($email)) {
                Session::set('user_email', $email);
            }
            
            Response::success('Perfil atualizado com sucesso!');

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Response::error('Erro ao atualizar perfil: ' . $e->getMessage());
        }
        break;

    default:
        Response::error('Ação não reconhecida');
        break;
}

