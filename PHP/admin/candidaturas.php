<?php
/**
 * Gerenciamento de Candidaturas (Admin)
 * Visualização e controle de todas as candidaturas
 */

require_once __DIR__ . '/../../lib/bootstrap.php';

verificarAdmin();

$acao = Request::post('acao', Request::get('acao', 'listar'));

$db = new Database();

switch ($acao) {
    case 'listar':
        // Listar todas as candidaturas
        try {
            $filtro = Request::get('filtro', 'todas'); // 'todas', 'pendentes', 'aprovadas', 'reprovadas'
            
            $sql = "SELECT 
                    vc.vaga_id,
                    vc.curriculum_id,
                    vc.dateCandidatura as data_candidatura,
                    v.descricao as vaga_titulo,
                    c.descricao as vaga_categoria,
                    e.nome as empresa_nome,
                    cur.nome as pessoa_nome,
                    cur.email as pessoa_email,
                    cur.telefone as pessoa_telefone
                    FROM vaga_curriculum vc
                    INNER JOIN vaga v ON vc.vaga_id = v.vaga_id
                    INNER JOIN estabelecimento e ON v.estabelecimento_id = e.estabelecimento_id
                    INNER JOIN cargo c ON v.cargo_id = c.cargo_id
                    INNER JOIN curriculos cur ON vc.curriculum_id = cur.id";
            
            // vaga_curriculum não tem campo status, então não podemos filtrar
            // Se precisar filtrar, seria necessário criar uma tabela adicional
            
            $sql .= " ORDER BY vc.dateCandidatura DESC";
            
            $result = $db->dbSelect($sql);
            $candidaturas = $db->dbBuscaArrayAll($result);
            
            Response::success('Candidaturas listadas com sucesso!', ['candidaturas' => $candidaturas]);
            
        } catch (\Exception $e) {
            Response::error('Erro ao listar candidaturas: ' . $e->getMessage());
        }
        break;

    case 'estatisticas':
        // Estatísticas de candidaturas
        try {
            $sqlTotal = "SELECT COUNT(*) as total FROM vaga_curriculum";
            $resultTotal = $db->dbSelect($sqlTotal);
            $rowTotal = $db->dbBuscaArray($resultTotal);
            $total = (int)($rowTotal['total'] ?? 0);
            
            // vaga_curriculum não tem status, então todos são "pendentes"
            $pendentes = $total;
            $aprovadas = 0;
            $reprovadas = 0;
            
            Response::success('Estatísticas carregadas!', [
                'total' => (int)$total,
                'pendentes' => (int)$pendentes,
                'aprovadas' => (int)$aprovadas,
                'reprovadas' => (int)$reprovadas
            ]);
            
        } catch (\Exception $e) {
            Response::error('Erro ao carregar estatísticas: ' . $e->getMessage());
        }
        break;

    default:
        Response::error('Ação não reconhecida');
        break;
}

