<?php
/**
 * Dashboard Administrativo
 * Retorna métricas e resumo geral do sistema
 */

require_once __DIR__ . '/../../lib/bootstrap.php';

// Verificar se é admin
verificarAdmin();

$db = new Database();

try {
    // Contar usuários PF
    $totalPF = $db->table('usuarios_pf')->findCount();
    
    // Contar usuários PJ
    $totalPJ = $db->table('usuarios_pj')->findCount();
    
    // Contar vagas (usando estrutura real)
    $sqlVagas = "SELECT COUNT(*) as total FROM vaga";
    $resultVagas = $db->dbSelect($sqlVagas);
    $rowVagas = $db->dbBuscaArray($resultVagas);
    $totalVagas = (int)($rowVagas['total'] ?? 0);
    
    $sqlVagasAbertas = "SELECT COUNT(*) as total FROM vaga WHERE statusVaga = 11";
    $resultVagasAbertas = $db->dbSelect($sqlVagasAbertas);
    $rowVagasAbertas = $db->dbBuscaArray($resultVagasAbertas);
    $vagasAbertas = (int)($rowVagasAbertas['total'] ?? 0);
    
    // Contar candidaturas (vaga_curriculum)
    $sqlCandidaturas = "SELECT COUNT(*) as total FROM vaga_curriculum";
    $resultCandidaturas = $db->dbSelect($sqlCandidaturas);
    $rowCandidaturas = $db->dbBuscaArray($resultCandidaturas);
    $totalCandidaturas = (int)($rowCandidaturas['total'] ?? 0);
    $candidaturasPendentes = $totalCandidaturas; // vaga_curriculum não tem status
    
    // Contar estabelecimentos
    $sqlEstabelecimentos = "SELECT COUNT(*) as total FROM estabelecimento";
    $resultEstabelecimentos = $db->dbSelect($sqlEstabelecimentos);
    $rowEstabelecimentos = $db->dbBuscaArray($resultEstabelecimentos);
    $totalEmpresas = (int)($rowEstabelecimentos['total'] ?? 0);
    
    // Contar currículos
    $totalCurriculos = $db->table('curriculos')->findCount();
    
    // Vagas recentes (últimas 5)
    $sqlVagasRecentes = "SELECT 
                        v.vaga_id as id,
                        v.descricao as titulo,
                        v.dtInicio as data_publicacao,
                        e.nome as empresa_nome
                        FROM vaga v
                        LEFT JOIN estabelecimento e ON v.estabelecimento_id = e.estabelecimento_id
                        ORDER BY v.dtInicio DESC
                        LIMIT 5";
    $resultVagasRecentes = $db->dbSelect($sqlVagasRecentes);
    $vagasRecentes = $db->dbBuscaArrayAll($resultVagasRecentes);

    Response::success('Dados carregados com sucesso!', [
        'metricas' => [
            'usuarios' => [
                'total_pf' => (int)$totalPF,
                'total_pj' => (int)$totalPJ,
                'total' => (int)($totalPF + $totalPJ)
            ],
            'vagas' => [
                'total' => (int)$totalVagas,
                'abertas' => (int)$vagasAbertas,
                'fechadas' => (int)($totalVagas - $vagasAbertas)
            ],
            'candidaturas' => [
                'total' => (int)$totalCandidaturas,
                'pendentes' => (int)$candidaturasPendentes,
                'aprovadas' => 0, // vaga_curriculum não tem status
                'reprovadas' => 0 // vaga_curriculum não tem status
            ],
            'empresas' => (int)$totalEmpresas,
            'curriculos' => (int)$totalCurriculos
        ],
        'vagas_recentes' => $vagasRecentes
    ]);

} catch (\Exception $e) {
    Response::error('Erro ao carregar dados: ' . $e->getMessage());
}

