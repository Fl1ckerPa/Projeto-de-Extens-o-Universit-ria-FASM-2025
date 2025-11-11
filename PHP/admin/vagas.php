<?php
/**
 * Gerenciamento de Vagas (Admin)
 * Moderação e gerenciamento de todas as vagas
 */

require_once __DIR__ . '/../../lib/bootstrap.php';

verificarAdmin();

$acao = Request::post('acao', Request::get('acao', ''));

if (empty($acao)) {
    Response::error('Ação não informada');
}

$db = new Database();

switch ($acao) {
    case 'listar':
        // Listar todas as vagas
        try {
            $sql = "SELECT 
                    v.vaga_id as id,
                    v.descricao as titulo,
                    v.sobreaVaga as descricao,
                    v.dtInicio as data_publicacao,
                    v.dtFim as data_limite,
                    v.statusVaga,
                    e.nome as empresa_nome,
                    e.email as empresa_email,
                    c.descricao as cargo_nome,
                    COUNT(vc.vaga_id) as total_candidatos
                    FROM vaga v
                    LEFT JOIN estabelecimento e ON v.estabelecimento_id = e.estabelecimento_id
                    LEFT JOIN cargo c ON v.cargo_id = c.cargo_id
                    LEFT JOIN vaga_curriculum vc ON v.vaga_id = vc.vaga_id
                    GROUP BY v.vaga_id
                    ORDER BY v.dtInicio DESC";
            
            $result = $db->dbSelect($sql);
            $vagas = $db->dbBuscaArrayAll($result);
            
            Response::success('Vagas listadas com sucesso!', ['vagas' => $vagas]);
            
        } catch (\Exception $e) {
            Response::error('Erro ao listar vagas: ' . $e->getMessage());
        }
        break;

    case 'aprovar':
    case 'reprovar':
        // Aprovar/reprovar vaga (moderação)
        try {
            $id = Request::post('id', '');
            $status = $acao === 'aprovar' ? 1 : 0;
            
            if (empty($id)) {
                Response::error('ID da vaga é obrigatório');
            }
            
            // Mapear status: 1 = ativo (statusVaga = 11), 0 = inativo (statusVaga = 91)
            $statusVaga = $status ? 11 : 91;
            $resultado = $db->dbUpdate(
                "UPDATE vaga SET statusVaga = ? WHERE vaga_id = ?",
                [$statusVaga, $id]
            );
            
            if ($resultado) {
                $mensagem = $status ? 'Vaga aprovada com sucesso!' : 'Vaga reprovada com sucesso!';
                Response::success($mensagem);
            } else {
                Response::error('Erro ao alterar status da vaga');
            }
            
        } catch (\Exception $e) {
            Response::error('Erro ao modificar vaga: ' . $e->getMessage());
        }
        break;

    case 'excluir':
        // Excluir vaga
        try {
            $id = Request::post('id', '');
            
            if (empty($id)) {
                Response::error('ID da vaga é obrigatório');
            }
            
            // Excluir candidaturas relacionadas primeiro (vaga_curriculum)
            $db->dbUpdate("DELETE FROM vaga_curriculum WHERE vaga_id = ?", [$id]);
            
            // Excluir vaga
            $resultado = $db->dbUpdate("DELETE FROM vaga WHERE vaga_id = ?", [$id]);
            
            if ($resultado) {
                Response::success('Vaga excluída com sucesso!');
            } else {
                Response::error('Erro ao excluir vaga');
            }
            
        } catch (\Exception $e) {
            Response::error('Erro ao excluir vaga: ' . $e->getMessage());
        }
        break;

    default:
        Response::error('Ação não reconhecida');
        break;
}

