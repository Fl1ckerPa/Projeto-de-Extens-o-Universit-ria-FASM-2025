<?php
/**
 * Endpoint para buscar vagas do banco de dados
 * Conecta com a tabela vaga do schema descubra_muriae
 */
require_once __DIR__ . '/../lib/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$db = new Database();

try {
    $segmento = Request::get('categoria', '');

    // Query base usando o esquema normalizado
    $sql = "SELECT 
                v.vaga_id,
                v.titulo,
                v.descricao,
                v.dt_inicio,
                v.dt_fim,
                e.nome_social as empresa_nome,
                cgo.descricao as cargo_nome,
                mt.descricao as modalidade,
                vcx.descricao as vinculo,
                COALESCE(cv.nome, 'outros') as categoria,
                COUNT(cd.candidatura_id) as total_candidatos
            FROM vaga v
            INNER JOIN empresa e ON v.empresa_id = e.empresa_id
            LEFT JOIN cargo cgo ON v.cargo_id = cgo.cargo_id
            INNER JOIN modalidade_trabalho mt ON v.modalidade_trabalho_id = mt.modalidade_trabalho_id
            INNER JOIN vinculo_contratual vcx ON v.vinculo_contratual_id = vcx.vinculo_contratual_id
            INNER JOIN status_vaga sv ON v.status_vaga_id = sv.status_vaga_id
            LEFT JOIN categoria_vaga cv ON v.categoria_vaga_id = cv.categoria_vaga_id
            LEFT JOIN candidatura cd ON cd.vaga_id = v.vaga_id
            WHERE sv.codigo = 'ABERTA'";

    $params = [];

    // Filtro por categoria (categoria_vaga.nome)
    if (!empty($segmento) && $segmento !== 'todos') {
        $sql .= " AND cv.nome = ?";
        $params[] = $segmento;
    }

    $sql .= " GROUP BY v.vaga_id";
    $sql .= " ORDER BY v.dt_inicio DESC";
    $sql .= " LIMIT 50"; // Limite de resultados

    $result = $db->dbSelect($sql, $params);
    $vagas = $db->dbBuscaArrayAll($result);

    // Formatar dados para o frontend
    $vagasFormatadas = array_map(function($v) {
        return [
            'id' => $v['vaga_id'],
            'titulo' => $v['titulo'] ?? 'Vaga sem título',
            'descricao' => $v['descricao'] ?? '',
            'empresa_nome' => $v['empresa_nome'] ?? 'Empresa não informada',
            'cargo_nome' => $v['cargo_nome'] ?? '',
            'modalidade' => $v['modalidade'] ?? '',
            'vinculo' => $v['vinculo'] ?? '',
            'data_inicio' => $v['dt_inicio'] ?? '',
            'data_fim' => $v['dt_fim'] ?? '',
            'total_candidatos' => (int)($v['total_candidatos'] ?? 0),
            'categoria' => $v['categoria'] ?? 'outros'
        ];
    }, $vagas);

    Helper::jsonSuccess('Vagas encontradas', ['vagas' => $vagasFormatadas]);

} catch (\Exception $e) {
    Helper::jsonError('Erro ao buscar vagas: ' . $e->getMessage());
}

