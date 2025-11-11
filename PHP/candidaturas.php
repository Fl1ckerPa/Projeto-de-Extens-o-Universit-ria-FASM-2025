<?php
/**
 * API de Candidaturas
 * Endpoints para candidatos enviarem e visualizarem candidaturas
 */

require_once __DIR__ . '/../lib/bootstrap.php';

$acao = Request::post('acao', Request::get('acao', ''));

if (empty($acao)) {
    Response::error('Ação não informada');
}

$db = new Database();

switch ($acao) {
    case 'enviar':
        // Candidato se candidata a uma vaga
        verificarCandidato();
        
        $vagaId = Request::post('vaga_id', '');
        
        if (empty($vagaId)) {
            Response::error('ID da vaga é obrigatório');
        }
        
        try {
            // Verificar se vaga existe e está aberta no modelo normalizado
            $sqlVaga = "SELECT v.vaga_id
                        FROM vaga v
                        INNER JOIN status_vaga sv ON sv.status_vaga_id = v.status_vaga_id
                        WHERE v.vaga_id = ? AND sv.codigo = 'ABERTA' AND v.dt_fim >= CURDATE()";
            $resultVaga = $db->dbSelect($sqlVaga, [$vagaId]);
            $vaga = $db->dbBuscaArray($resultVaga);
            
            if (!$vaga) {
                Response::error('Vaga não encontrada ou não está mais disponível');
            }
            
            // Determinar curriculo_id do usuário logado
            $curriculoId = Session::get('curriculo_id');
            if (empty($curriculoId)) {
                $usuarioId = Session::get('user_id');
                if (!$usuarioId) {
                    Response::error('Sessão inválida');
                }
                // Obter pessoa_id via usuario
                $uRow = $db->dbSelect("SELECT pessoa_id FROM usuario WHERE usuario_id = ? LIMIT 1", [$usuarioId]);
                $u = $db->dbBuscaArray($uRow);
                if (!$u) {
                    Response::error('Usuário não encontrado');
                }
                $pessoaId = (int)$u['pessoa_id'];
                // Buscar curriculo da pessoa
                $cRow = $db->dbSelect("SELECT curriculo_id FROM curriculo WHERE pessoa_id = ? LIMIT 1", [$pessoaId]);
                $c = $db->dbBuscaArray($cRow);
                if (!$c) {
                    Response::error('Currículo não encontrado. Por favor, cadastre seu currículo primeiro.');
                }
                $curriculoId = (int)$c['curriculo_id'];
            }
            
            // Verificar se já se candidatou
            $sqlJaCandidatou = "SELECT 1 FROM candidatura WHERE vaga_id = ? AND curriculo_id = ?";
            $resultJaCandidatou = $db->dbSelect($sqlJaCandidatou, [$vagaId, $curriculoId]);
            $jaCandidatou = $db->dbBuscaArray($resultJaCandidatou);
            
            if ($jaCandidatou) {
                Response::error('Você já se candidatou a esta vaga');
            }
            
            // Criar candidatura no modelo normalizado
            $statusPendenteId = null;
            $rowSt = $db->dbSelect("SELECT status_candidatura_id FROM status_candidatura WHERE codigo = 'PENDENTE' LIMIT 1");
            $st = $db->dbBuscaArray($rowSt);
            $statusPendenteId = $st ? (int)$st['status_candidatura_id'] : null;
            
            if (!$statusPendenteId) {
                Response::error('Status de candidatura não configurado. Contate o administrador.');
            }

            $db->dbInsert(
                "INSERT INTO candidatura (vaga_id, curriculo_id, status_candidatura_id, data_candidatura) VALUES (?, ?, ?, NOW())",
                [$vagaId, $curriculoId, $statusPendenteId]
            );
            
            Response::success('Candidatura enviada com sucesso!', [
                'vaga_id' => $vagaId,
                'curriculo_id' => $curriculoId
            ]);
            
        } catch (\Exception $e) {
            Response::error('Erro ao enviar candidatura: ' . $e->getMessage());
        }
        break;

    case 'minhas':
        // Listar candidaturas do candidato logado
        verificarCandidato();
        
        $status = Request::get('status', 'todas');
        
        try {
            // Determinar curriculo_id
            $curriculoId = Session::get('curriculo_id');
            if (empty($curriculoId)) {
                $usuarioId = Session::get('user_id');
                if (!$usuarioId) {
                    Response::error('Sessão inválida');
                }
                $uRow = $db->dbSelect("SELECT pessoa_id FROM usuario WHERE usuario_id = ? LIMIT 1", [$usuarioId]);
                $u = $db->dbBuscaArray($uRow);
                if (!$u) {
                    Response::error('Usuário não encontrado');
                }
                $pessoaId = (int)$u['pessoa_id'];
                $cRow = $db->dbSelect("SELECT curriculo_id FROM curriculo WHERE pessoa_id = ? LIMIT 1", [$pessoaId]);
                $c = $db->dbBuscaArray($cRow);
                if (!$c) {
                    Response::success('Candidaturas listadas com sucesso!', ['candidaturas' => []]);
                }
                $curriculoId = (int)$c['curriculo_id'];
            }
            
            // Query usando estrutura normalizada: candidatura, vaga, empresa, cargo
            $sql = "SELECT 
                    cd.vaga_id,
                    cd.curriculo_id,
                    cd.data_candidatura,
                    v.titulo as vaga_titulo,
                    v.descricao as vaga_descricao,
                    v.dt_inicio,
                    v.dt_fim,
                    e.nome_social as empresa_nome,
                    e.email as empresa_email,
                    cgo.descricao as cargo_nome,
                    sc.descricao as status
                    FROM candidatura cd
                    INNER JOIN vaga v ON cd.vaga_id = v.vaga_id
                    INNER JOIN empresa e ON v.empresa_id = e.empresa_id
                    LEFT JOIN cargo cgo ON v.cargo_id = cgo.cargo_id
                    INNER JOIN status_candidatura sc ON cd.status_candidatura_id = sc.status_candidatura_id
                    WHERE cd.curriculo_id = ?";
            
            $params = [$curriculoId];
            
            if (!empty($status) && $status !== 'todas') {
                $sql .= " AND sc.descricao = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY cd.data_candidatura DESC";
            
            $result = $db->dbSelect($sql, $params);
            $candidaturas = $db->dbBuscaArrayAll($result);
            
            // Formatar dados para o frontend
            $candidaturasFormatadas = array_map(function($c) {
                return [
                    'vaga_id' => $c['vaga_id'],
                    'curriculum_id' => $c['curriculum_id'],
                    'data_candidatura' => $c['data_candidatura'],
                    'vaga_titulo' => $c['vaga_titulo'] ?? 'Vaga sem título',
                    'vaga_descricao' => $c['vaga_descricao'] ?? '',
                    'vaga_categoria' => $c['cargo_nome'] ?? '',
                    'empresa_nome' => $c['empresa_nome'] ?? 'Empresa não informada',
                    'empresa_email' => $c['empresa_email'] ?? '',
                    'status' => $c['status'] ?? 'Pendente'
                ];
            }, $candidaturas);
            
            Response::success('Candidaturas listadas com sucesso!', ['candidaturas' => $candidaturasFormatadas]);
            
        } catch (\Exception $e) {
            Response::error('Erro ao listar candidaturas: ' . $e->getMessage());
        }
        break;

    case 'detalhes':
        // Detalhes de uma candidatura específica
        verificarCandidato();
        
        $id = Request::get('id', '');
        
        if (empty($id)) {
            Response::error('ID da candidatura é obrigatório');
        }
        
        try {
            // Determinar curriculo_id
            $curriculoId = Session::get('curriculo_id');
            if (empty($curriculoId)) {
                $usuarioId = Session::get('user_id');
                if (!$usuarioId) {
                    Response::error('Sessão inválida');
                }
                $uRow = $db->dbSelect("SELECT pessoa_id FROM usuario WHERE usuario_id = ? LIMIT 1", [$usuarioId]);
                $u = $db->dbBuscaArray($uRow);
                if (!$u) {
                    Response::error('Usuário não encontrado');
                }
                $pessoaId = (int)$u['pessoa_id'];
                $cRow = $db->dbSelect("SELECT curriculo_id FROM curriculo WHERE pessoa_id = ? LIMIT 1", [$pessoaId]);
                $c = $db->dbBuscaArray($cRow);
                if (!$c) {
                    Response::error('Currículo não encontrado', null, 404);
                }
                $curriculoId = (int)$c['curriculo_id'];
            }
            
            // Query usando estrutura normalizada
            $sql = "SELECT 
                    cd.vaga_id,
                    cd.curriculo_id,
                    cd.data_candidatura,
                    v.titulo as vaga_titulo,
                    v.descricao as vaga_descricao,
                    v.dt_inicio,
                    v.dt_fim,
                    e.nome_social as empresa_nome,
                    e.email as empresa_email,
                    cgo.descricao as cargo_nome,
                    sc.descricao as status
                    FROM candidatura cd
                    INNER JOIN vaga v ON cd.vaga_id = v.vaga_id
                    INNER JOIN empresa e ON v.empresa_id = e.empresa_id
                    LEFT JOIN cargo cgo ON v.cargo_id = cgo.cargo_id
                    INNER JOIN status_candidatura sc ON cd.status_candidatura_id = sc.status_candidatura_id
                    WHERE cd.vaga_id = ? AND cd.curriculo_id = ?";
            
            $result = $db->dbSelect($sql, [$id, $curriculumId]);
            $candidatura = $db->dbBuscaArray($result);
            
            if (!$candidatura) {
                Response::error('Candidatura não encontrada', null, 404);
            }
            
            // Formatar dados
            $candidaturaFormatada = [
                'vaga_id' => $candidatura['vaga_id'],
                'curriculum_id' => $candidatura['curriculum_id'],
                'data_candidatura' => $candidatura['data_candidatura'],
                'vaga_titulo' => $candidatura['vaga_titulo'] ?? 'Vaga sem título',
                'vaga_descricao' => $candidatura['vaga_descricao'] ?? '',
                'empresa_nome' => $candidatura['empresa_nome'] ?? 'Empresa não informada',
                'empresa_email' => $candidatura['empresa_email'] ?? '',
                'cargo_nome' => $candidatura['cargo_nome'] ?? '',
                'status' => $candidatura['status'] ?? 'Pendente'
            ];
            
            Response::success('Candidatura encontrada!', ['candidatura' => $candidaturaFormatada]);
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar candidatura: ' . $e->getMessage());
        }
        break;

    default:
        Response::error('Ação não reconhecida');
        break;
}

