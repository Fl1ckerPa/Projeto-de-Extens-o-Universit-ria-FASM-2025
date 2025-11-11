<?php
/**
 * API de Vagas - Listagem Pública
 * Endpoint para candidatos buscarem vagas
 */

require_once __DIR__ . '/../lib/bootstrap.php';

$acao = Request::get('acao', 'listar');
$db = new Database();

try {
    switch ($acao) {
        case 'listar':
            // Listar vagas públicas (abertas e ativas) - usando esquema normalizado
            $categoria = Request::get('categoria', '');
            $localidade = Request::get('localidade', '');
            $tipo = Request::get('tipo', '');
            $pagina = (int)Request::get('pagina', 1);
            $porPagina = (int)Request::get('por_pagina', 12);
            
            // Query usando o esquema normalizado
            $sql = "SELECT 
                        v.vaga_id as id,
                        v.titulo,
                        v.descricao,
                        v.dt_inicio,
                        v.dt_fim,
                        e.nome_social as empresa_nome,
                        cgo.descricao as cargo_nome,
                        mt.descricao as modalidade,
                        vcx.descricao as vinculo,
                        COALESCE(cv.nome, 'outros') as categoria,
                        COUNT(cdt.candidatura_id) as total_candidatos
                    FROM vaga v
                    INNER JOIN empresa e ON v.empresa_id = e.empresa_id
                    LEFT JOIN cargo cgo ON v.cargo_id = cgo.cargo_id
                    INNER JOIN modalidade_trabalho mt ON v.modalidade_trabalho_id = mt.modalidade_trabalho_id
                    INNER JOIN vinculo_contratual vcx ON v.vinculo_contratual_id = vcx.vinculo_contratual_id
                    INNER JOIN status_vaga sv ON v.status_vaga_id = sv.status_vaga_id
                    LEFT JOIN categoria_vaga cv ON v.categoria_vaga_id = cv.categoria_vaga_id
                    LEFT JOIN candidatura cdt ON cdt.vaga_id = v.vaga_id
                    WHERE sv.codigo = 'ABERTA' AND v.dt_fim >= CURDATE()";
            
            $params = [];
            
            // Filtro por categoria (categoria_vaga.nome)
            if (!empty($categoria) && $categoria !== 'todos') {
                $sql .= " AND cv.nome = ?";
                $params[] = $categoria;
            }
            
            if (!empty($localidade)) {
                $sql .= " AND (e.logradouro LIKE ? OR EXISTS (SELECT 1 FROM cidade c WHERE c.cidade_id = e.cidade_id AND c.cidade LIKE ?))";
                $params[] = "%{$localidade}%";
                $params[] = "%{$localidade}%";
            }
            
            if (!empty($tipo)) {
                // tipo pode ser modalidade (mt.descricao) ou vínculo (vcx.descricao)
                if (in_array(strtolower($tipo), ['presencial','remoto','híbrido','hibrido'])) {
                    $sql .= " AND LOWER(mt.descricao) = ?";
                    $params[] = strtolower($tipo) === 'hibrido' ? 'híbrido' : strtolower($tipo);
                } elseif (in_array(strtolower($tipo), ['clt','pessoa jurídica','pj','pessoa juridica'])) {
                    $sql .= " AND (LOWER(vcx.descricao) = ? OR (LOWER(?) IN ('pj','pessoa juridica') AND LOWER(vcx.descricao) = 'pessoa jurídica'))";
                    $params[] = strtolower($tipo);
                    $params[] = strtolower($tipo);
                }
            }
            
            $sql .= " GROUP BY v.vaga_id";
            $sql .= " ORDER BY v.dt_inicio DESC";
            
            // Contar total
            $sqlCount = "SELECT COUNT(DISTINCT v.vaga_id) as total 
                        FROM vaga v
                        INNER JOIN empresa e ON v.empresa_id = e.empresa_id
                        INNER JOIN status_vaga sv ON v.status_vaga_id = sv.status_vaga_id
                        LEFT JOIN categoria_vaga cv ON v.categoria_vaga_id = cv.categoria_vaga_id
                        WHERE sv.codigo = 'ABERTA' AND v.dt_fim >= CURDATE()";
            
            $paramsCount = [];
            if (!empty($categoria) && $categoria !== 'todos') {
                $sqlCount .= " AND cv.nome = ?";
                $paramsCount[] = $categoria;
            }
            if (!empty($localidade)) {
                $sqlCount .= " AND (e.logradouro LIKE ? OR EXISTS (SELECT 1 FROM cidade c WHERE c.cidade_id = e.cidade_id AND c.cidade LIKE ?))";
                $paramsCount[] = "%{$localidade}%";
                $paramsCount[] = "%{$localidade}%";
            }
            
            try {
                $resultCount = $db->dbSelect($sqlCount, $paramsCount);
                $totalRow = $db->dbBuscaArray($resultCount);
                $total = $totalRow ? (int)$totalRow['total'] : 0;
            } catch (\Exception $e) {
                error_log('Erro ao contar vagas: ' . $e->getMessage());
                error_log('SQL Count: ' . $sqlCount);
                $total = 0;
            }
            
            // Paginação
            $offset = ($pagina - 1) * $porPagina;
            $sql .= " LIMIT {$porPagina} OFFSET {$offset}";
            
            try {
                $result = $db->dbSelect($sql, $params);
                $vagas = $db->dbBuscaArrayAll($result);
            } catch (\Exception $e) {
                error_log('Erro ao buscar vagas: ' . $e->getMessage());
                error_log('SQL: ' . $sql);
                error_log('Params: ' . print_r($params, true));
                throw $e; // Re-lança para ser capturado pelo catch externo
            }
            
            // Formatar dados para o frontend
            $vagasFormatadas = array_map(function($v) use ($categoria) {
                return [
                    'id' => $v['id'],
                    'titulo' => $v['titulo'] ?? 'Vaga sem título',
                    'descricao' => $v['descricao'] ?? '',
                    'empresa_nome' => $v['empresa_nome'] ?? 'Empresa não informada',
                    'cargo_nome' => $v['cargo_nome'] ?? '',
                    'modalidade' => $v['modalidade'] ?? '',
                    'vinculo' => $v['vinculo'] ?? '',
                    'data_inicio' => $v['dt_inicio'] ?? '',
                    'data_fim' => $v['dt_fim'] ?? '',
                    'total_candidatos' => (int)($v['total_candidatos'] ?? 0),
                    'categoria' => $v['categoria'] ?? ($categoria ?: 'outros')
                ];
            }, $vagas);
            
            Response::paginated($vagasFormatadas, $total, $pagina, $porPagina, 'Vagas listadas com sucesso!');
            break;

        case 'detalhes':
            // Detalhes de uma vaga específica - usando esquema normalizado
            $id = Request::get('id', '');
            
            if (empty($id)) {
                Response::error('ID da vaga é obrigatório');
            }
            
            $sql = "SELECT 
                        v.vaga_id,
                        v.titulo,
                        v.descricao,
                        v.dt_inicio,
                        v.dt_fim,
                        e.nome_social as empresa_nome,
                        e.email as empresa_email,
                        cgo.descricao as cargo_nome,
                        mt.descricao as modalidade,
                        vcx.descricao as vinculo,
                        COUNT(cdt.candidatura_id) as total_candidatos
                    FROM vaga v
                    INNER JOIN empresa e ON v.empresa_id = e.empresa_id
                    LEFT JOIN cargo cgo ON v.cargo_id = cgo.cargo_id
                    INNER JOIN modalidade_trabalho mt ON v.modalidade_trabalho_id = mt.modalidade_trabalho_id
                    INNER JOIN vinculo_contratual vcx ON v.vinculo_contratual_id = vcx.vinculo_contratual_id
                    INNER JOIN status_vaga sv ON v.status_vaga_id = sv.status_vaga_id
                    LEFT JOIN candidatura cdt ON cdt.vaga_id = v.vaga_id
                    WHERE v.vaga_id = ? AND sv.codigo = 'ABERTA'
                    GROUP BY v.vaga_id";
            
            $result = $db->dbSelect($sql, [$id]);
            $vaga = $db->dbBuscaArray($result);
            
            if (!$vaga) {
                Response::error('Vaga não encontrada', null, 404);
            }
            
            // Verificar se usuário já se candidatou (se estiver logado)
            $jaCandidatou = false;
            if (Session::get('user_id') && Session::get('user_type') === 'pf') {
                // Assumindo que há curriculo vinculado ao usuário
                $curriculoId = Session::get('curriculo_id');
                if ($curriculoId) {
                    $sqlCandidatura = "SELECT 1 FROM candidatura WHERE vaga_id = ? AND curriculo_id = ?";
                    $resultCandidatura = $db->dbSelect($sqlCandidatura, [$id, $curriculoId]);
                    $jaCandidatou = !empty($db->dbBuscaArray($resultCandidatura));
                }
            }
            
            // Formatar dados
            $vagaFormatada = [
                'id' => $vaga['vaga_id'],
                'titulo' => $vaga['titulo'] ?? 'Vaga sem título',
                'descricao' => $vaga['descricao'] ?? '',
                'empresa_nome' => $vaga['empresa_nome'] ?? 'Empresa não informada',
                'empresa_email' => $vaga['empresa_email'] ?? '',
                'cargo_nome' => $vaga['cargo_nome'] ?? '',
                'modalidade' => $vaga['modalidade'] ?? '',
                'vinculo' => $vaga['vinculo'] ?? '',
                'data_inicio' => $vaga['dt_inicio'] ?? '',
                'data_fim' => $vaga['dt_fim'] ?? '',
                'total_candidatos' => (int)($vaga['total_candidatos'] ?? 0),
                'ja_candidatou' => $jaCandidatou
            ];
            
            Response::success('Vaga encontrada!', ['vaga' => $vagaFormatada]);
            break;

        case 'categorias':
            // Listar categorias disponíveis - usando categoria_vaga
            $sql = "SELECT cv.categoria_vaga_id, cv.nome, COUNT(DISTINCT v.vaga_id) as total
                    FROM categoria_vaga cv
                    LEFT JOIN vaga v ON v.categoria_vaga_id = cv.categoria_vaga_id
                    LEFT JOIN status_vaga sv ON v.status_vaga_id = sv.status_vaga_id
                    WHERE sv.codigo = 'ABERTA' AND v.dt_fim >= CURDATE()
                    GROUP BY cv.categoria_vaga_id, cv.nome
                    ORDER BY cv.nome ASC";
            
            $result = $db->dbSelect($sql);
            $categorias = $db->dbBuscaArrayAll($result);
            
            Response::success('Categorias listadas!', ['categorias' => $categorias]);
            break;

        default:
            Response::error('Ação não reconhecida');
            break;
    }
    
} catch (\PDOException $e) {
    // Log do erro completo para debug
    error_log('Erro SQL em vagas.php: ' . $e->getMessage());
    error_log('SQL State: ' . $e->getCode());
    Response::error('Erro ao processar solicitação: ' . $e->getMessage());
} catch (\Exception $e) {
    // Log do erro completo para debug
    error_log('Erro em vagas.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    Response::error('Erro ao processar solicitação: ' . $e->getMessage());
}

