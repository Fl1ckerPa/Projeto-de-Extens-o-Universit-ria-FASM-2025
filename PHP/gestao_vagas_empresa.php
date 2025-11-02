<?php
/**
 * Gestão de vagas da empresa
 * Versão refatorada usando bibliotecas do AtomPHP
 */

require_once __DIR__ . '/../lib/bootstrap.php';

if (!Request::isPost()) {
    Helper::jsonError('Método não permitido', null, 405);
}

$acao = Request::post('acao', '');

if (empty($acao)) {
    Helper::jsonError('Ação não informada');
}

// Verificar se usuário está autenticado
$empresaId = Session::get('empresa_id');
if (!$empresaId) {
    Helper::jsonError('Acesso não autorizado', null, 401);
}

$db = new Database();

switch ($acao) {
    case 'criar_vaga':
        // Validar dados
        $dadosVaga = [
            'titulo' => Request::post('titulo', ''),
            'categoria' => Request::post('categoria', ''),
            'salario' => Request::post('salario', ''),
            'tipoContrato' => Request::post('tipoContrato', ''),
            'dataLimite' => Request::post('dataLimite', ''),
            'status' => Request::post('status', 'Aberta'),
            'descricao' => Request::post('descricao', ''),
            'requisitos' => Request::post('requisitos', ''),
            'beneficios' => Request::post('beneficios', '')
        ];

        $erros = [];

        if (empty($dadosVaga['titulo'])) {
            $erros[] = 'Título da vaga é obrigatório';
        }

        if (empty($dadosVaga['categoria'])) {
            $erros[] = 'Categoria é obrigatória';
        }

        if (empty($dadosVaga['dataLimite'])) {
            $erros[] = 'Data limite é obrigatória';
        } else {
            $dataLimite = new DateTime($dadosVaga['dataLimite']);
            $hoje = new DateTime();
            if ($dataLimite <= $hoje) {
                $erros[] = 'Data limite deve ser futura';
            }
        }

        if (empty($dadosVaga['descricao'])) {
            $erros[] = 'Descrição é obrigatória';
        } elseif (strlen($dadosVaga['descricao']) < 50) {
            $erros[] = 'Descrição deve ter pelo menos 50 caracteres';
        }

        if (!empty($erros)) {
            Helper::jsonError('Erro na validação dos dados', $erros);
        }

        try {
            $dadosInsert = [
                'titulo' => $dadosVaga['titulo'],
                'categoria' => $dadosVaga['categoria'],
                'salario' => $dadosVaga['salario'],
                'tipo_contrato' => $dadosVaga['tipoContrato'],
                'data_publicacao' => date('Y-m-d H:i:s'),
                'data_limite' => $dadosVaga['dataLimite'],
                'status' => $dadosVaga['status'],
                'descricao' => $dadosVaga['descricao'],
                'requisitos' => $dadosVaga['requisitos'],
                'beneficios' => $dadosVaga['beneficios'],
                'empresa_id' => $empresaId,
                'ativo' => 1
            ];

            $vagaId = $db->table('vagas')->insert($dadosInsert);

            if ($vagaId) {
                Helper::jsonSuccess('Vaga criada com sucesso!', ['vaga_id' => $vagaId]);
            } else {
                Helper::jsonError('Erro ao criar vaga');
            }
        } catch (\Exception $e) {
            Helper::jsonError('Erro ao criar vaga: ' . $e->getMessage());
        }
        break;

    case 'editar_vaga':
        $dadosVaga = [
            'id' => Request::post('id', ''),
            'titulo' => Request::post('titulo', ''),
            'categoria' => Request::post('categoria', ''),
            'salario' => Request::post('salario', ''),
            'tipoContrato' => Request::post('tipoContrato', ''),
            'dataLimite' => Request::post('dataLimite', ''),
            'status' => Request::post('status', ''),
            'descricao' => Request::post('descricao', ''),
            'requisitos' => Request::post('requisitos', ''),
            'beneficios' => Request::post('beneficios', '')
        ];

        if (empty($dadosVaga['id'])) {
            Helper::jsonError('ID da vaga é obrigatório');
        }

        // Validar que a vaga pertence à empresa
        $vaga = $db->table('vagas')
            ->where('id', $dadosVaga['id'])
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$vaga) {
            Helper::jsonError('Vaga não encontrada ou não pertence à empresa');
        }

        $erros = [];
        if (empty($dadosVaga['titulo'])) {
            $erros[] = 'Título da vaga é obrigatório';
        }
        if (empty($dadosVaga['categoria'])) {
            $erros[] = 'Categoria é obrigatória';
        }
        if (empty($dadosVaga['dataLimite'])) {
            $erros[] = 'Data limite é obrigatória';
        }
        if (empty($dadosVaga['descricao']) || strlen($dadosVaga['descricao']) < 50) {
            $erros[] = 'Descrição deve ter pelo menos 50 caracteres';
        }

        if (!empty($erros)) {
            Helper::jsonError('Erro na validação dos dados', $erros);
        }

        try {
            $dadosUpdate = [
                'titulo' => $dadosVaga['titulo'],
                'categoria' => $dadosVaga['categoria'],
                'salario' => $dadosVaga['salario'],
                'tipo_contrato' => $dadosVaga['tipoContrato'],
                'data_limite' => $dadosVaga['dataLimite'],
                'status' => $dadosVaga['status'],
                'descricao' => $dadosVaga['descricao'],
                'requisitos' => $dadosVaga['requisitos'],
                'beneficios' => $dadosVaga['beneficios'],
                'data_atualizacao' => date('Y-m-d H:i:s')
            ];

            $resultado = $db->table('vagas')
                ->where('id', $dadosVaga['id'])
                ->where('empresa_id', $empresaId)
                ->update($dadosUpdate);

            if ($resultado) {
                Helper::jsonSuccess('Vaga atualizada com sucesso!');
            } else {
                Helper::jsonError('Erro ao atualizar vaga');
            }
        } catch (\Exception $e) {
            Helper::jsonError('Erro ao atualizar vaga: ' . $e->getMessage());
        }
        break;

    case 'excluir_vaga':
        $vagaId = Request::post('vaga_id', '');

        if (empty($vagaId)) {
            Helper::jsonError('ID da vaga é obrigatório');
        }

        try {
            // Verificar se a vaga pertence à empresa
            $vaga = $db->table('vagas')
                ->where('id', $vagaId)
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$vaga) {
                Helper::jsonError('Vaga não encontrada ou não pertence à empresa');
            }

            // Excluir candidaturas relacionadas
            $db->table('candidaturas')
                ->where('vaga_id', $vagaId)
                ->delete();

            // Excluir vaga
            $resultado = $db->table('vagas')
                ->where('id', $vagaId)
                ->where('empresa_id', $empresaId)
                ->delete();

            if ($resultado) {
                Helper::jsonSuccess('Vaga excluída com sucesso!');
            } else {
                Helper::jsonError('Erro ao excluir vaga');
            }
        } catch (\Exception $e) {
            Helper::jsonError('Erro ao excluir vaga: ' . $e->getMessage());
        }
        break;

    case 'alterar_status':
        $vagaId = Request::post('vaga_id', '');
        $novoStatus = Request::post('status', '');

        if (empty($vagaId) || empty($novoStatus)) {
            Helper::jsonError('ID da vaga e status são obrigatórios');
        }

        $statusValidos = ['Aberta', 'Pausada', 'Fechada'];
        if (!in_array($novoStatus, $statusValidos)) {
            Helper::jsonError('Status inválido');
        }

        try {
            $resultado = $db->table('vagas')
                ->where('id', $vagaId)
                ->where('empresa_id', $empresaId)
                ->update([
                    'status' => $novoStatus,
                    'data_atualizacao' => date('Y-m-d H:i:s')
                ]);

            if ($resultado) {
                Helper::jsonSuccess('Status da vaga alterado com sucesso!');
            } else {
                Helper::jsonError('Erro ao alterar status');
            }
        } catch (\Exception $e) {
            Helper::jsonError('Erro ao alterar status: ' . $e->getMessage());
        }
        break;

    case 'listar_vagas':
        try {
            // Usar SQL direto para JOIN mais complexo
            $sql = "SELECT v.*, 
                    COUNT(c.id) as total_candidatos
                    FROM vagas v 
                    LEFT JOIN candidaturas c ON v.id = c.vaga_id 
                    WHERE v.empresa_id = ? AND v.ativo = 1
                    GROUP BY v.id 
                    ORDER BY v.data_publicacao DESC";
            
            $result = $db->dbSelect($sql, [$empresaId]);
            $vagas = $db->dbBuscaArrayAll($result);

            Helper::jsonSuccess('Vagas listadas com sucesso!', ['vagas' => $vagas]);
        } catch (\Exception $e) {
            Helper::jsonError('Erro ao listar vagas: ' . $e->getMessage());
        }
        break;

    case 'listar_candidatos':
        $vagaId = Request::post('vaga_id', '');

        if (empty($vagaId)) {
            Helper::jsonError('ID da vaga é obrigatório');
        }

        try {
            // Verificar se a vaga pertence à empresa
            $vaga = $db->table('vagas')
                ->where('id', $vagaId)
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$vaga) {
                Helper::jsonError('Vaga não encontrada ou não pertence à empresa');
            }

            // Usar SQL direto para JOIN
            $sql = "SELECT c.*, p.nome, p.email, p.telefone, p.curriculo
                    FROM candidaturas c
                    JOIN pessoas p ON c.pessoa_id = p.id
                    WHERE c.vaga_id = ?
                    ORDER BY c.data_candidatura DESC";
            
            $result = $db->dbSelect($sql, [$vagaId]);
            $candidatos = $db->dbBuscaArrayAll($result);

            Helper::jsonSuccess('Candidatos listados com sucesso!', ['candidatos' => $candidatos]);
        } catch (\Exception $e) {
            Helper::jsonError('Erro ao listar candidatos: ' . $e->getMessage());
        }
        break;

    case 'avaliar_candidato':
        $candidatoId = Request::post('candidato_id', '');
        $status = Request::post('status', '');

        if (empty($candidatoId) || empty($status)) {
            Helper::jsonError('ID do candidato e status são obrigatórios');
        }

        $statusValidos = ['Aprovado', 'Reprovado', 'Pendente'];
        if (!in_array($status, $statusValidos)) {
            Helper::jsonError('Status inválido');
        }

        try {
            // Verificar se a candidatura pertence a uma vaga da empresa
            $sql = "SELECT c.* 
                    FROM candidaturas c
                    JOIN vagas v ON c.vaga_id = v.id
                    WHERE c.id = ? AND v.empresa_id = ?";
            $result = $db->dbSelect($sql, [$candidatoId, $empresaId]);
            $candidatura = $db->dbBuscaArray($result);

            if (!$candidatura) {
                Helper::jsonError('Candidatura não encontrada ou não pertence a uma vaga da empresa');
            }

            $resultado = $db->table('candidaturas')
                ->where('id', $candidatoId)
                ->update([
                    'status' => $status,
                    'data_avaliacao' => date('Y-m-d H:i:s')
                ]);

            if ($resultado) {
                Helper::jsonSuccess('Candidato avaliado com sucesso!');
            } else {
                Helper::jsonError('Erro ao avaliar candidato');
            }
        } catch (\Exception $e) {
            Helper::jsonError('Erro ao avaliar candidato: ' . $e->getMessage());
        }
        break;

    default:
        Helper::jsonError('Ação não reconhecida');
        break;
}
