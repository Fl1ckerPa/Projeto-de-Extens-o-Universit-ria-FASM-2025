<?php
/**
 * Relatórios Administrativos
 * Gera relatórios e métricas do sistema
 */

require_once __DIR__ . '/../../lib/bootstrap.php';

verificarAdmin();

$tipo = Request::get('tipo', 'geral');

$db = new Database();

try {
    switch ($tipo) {
        case 'geral':
            // Relatório geral
            $totalUsuarios = $db->table('usuarios_pf')->findCount() + $db->table('usuarios_pj')->findCount();
            $sqlVagas = "SELECT COUNT(*) as total FROM vaga";
            $resultVagas = $db->dbSelect($sqlVagas);
            $rowVagas = $db->dbBuscaArray($resultVagas);
            $totalVagas = (int)($rowVagas['total'] ?? 0);
            
            $sqlCandidaturas = "SELECT COUNT(*) as total FROM vaga_curriculum";
            $resultCandidaturas = $db->dbSelect($sqlCandidaturas);
            $rowCandidaturas = $db->dbBuscaArray($resultCandidaturas);
            $totalCandidaturas = (int)($rowCandidaturas['total'] ?? 0);
            
            $sqlEmpresas = "SELECT COUNT(*) as total FROM estabelecimento";
            $resultEmpresas = $db->dbSelect($sqlEmpresas);
            $rowEmpresas = $db->dbBuscaArray($resultEmpresas);
            $totalEmpresas = (int)($rowEmpresas['total'] ?? 0);
            
            Response::success('Relatório geral gerado!', [
                'total_usuarios' => (int)$totalUsuarios,
                'total_vagas' => (int)$totalVagas,
                'total_candidaturas' => (int)$totalCandidaturas,
                'total_empresas' => (int)$totalEmpresas,
                'data_geracao' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'vagas':
            // Relatório de vagas por categoria
            $sql = "SELECT 
                    c.descricao as categoria,
                    COUNT(*) as total,
                    SUM(CASE WHEN v.statusVaga = 11 THEN 1 ELSE 0 END) as abertas,
                    SUM(CASE WHEN v.statusVaga = 99 THEN 1 ELSE 0 END) as fechadas
                    FROM vaga v
                    INNER JOIN cargo c ON v.cargo_id = c.cargo_id
                    WHERE v.statusVaga IN (11, 91, 99)
                    GROUP BY c.descricao
                    ORDER BY total DESC";
            
            $result = $db->dbSelect($sql);
            $dados = $db->dbBuscaArrayAll($result);
            
            Response::success('Relatório de vagas gerado!', ['vagas_por_categoria' => $dados]);
            break;

        case 'candidaturas':
            // Relatório de candidaturas por status
            // vaga_curriculum não tem campo status
            // Retornar apenas total de candidaturas
            $sql = "SELECT 
                    'Pendente' as status,
                    COUNT(*) as total
                    FROM vaga_curriculum";
            
            $result = $db->dbSelect($sql);
            $dados = $db->dbBuscaArrayAll($result);
            
            Response::success('Relatório de candidaturas gerado!', ['candidaturas_por_status' => $dados]);
            break;

        default:
            Response::error('Tipo de relatório não reconhecido');
            break;
    }
    
} catch (\Exception $e) {
    Response::error('Erro ao gerar relatório: ' . $e->getMessage());
}

