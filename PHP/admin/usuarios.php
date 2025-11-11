<?php
/**
 * Gerenciamento de Usuários (Admin)
 * CRUD completo de usuários PF e PJ
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
        // Listar todos os usuários
        try {
            $tipo = Request::get('tipo', 'todos'); // 'pf', 'pj', 'todos'
            
            $usuarios = [];
            
            if ($tipo === 'pf' || $tipo === 'todos') {
                $pf = $db->table('usuarios_pf')->orderBy('created_at', 'DESC')->findAll();
                foreach ($pf as $u) {
                    $u['tipo'] = 'pf';
                    $usuarios[] = $u;
                }
            }
            
            if ($tipo === 'pj' || $tipo === 'todos') {
                $pj = $db->table('usuarios_pj')->orderBy('created_at', 'DESC')->findAll();
                foreach ($pj as $u) {
                    $u['tipo'] = 'pj';
                    $usuarios[] = $u;
                }
            }
            
            Response::success('Usuários listados com sucesso!', ['usuarios' => $usuarios]);
            
        } catch (\Exception $e) {
            Response::error('Erro ao listar usuários: ' . $e->getMessage());
        }
        break;

    case 'ativar':
    case 'desativar':
        // Nota: Tabelas usuarios_pf e usuarios_pj não têm campo 'ativo'
        // Para implementar ativação/desativação, seria necessário adicionar esse campo
        // Por enquanto, retornar erro informativo
        Response::error('Funcionalidade de ativar/desativar usuário não está disponível. As tabelas usuarios_pf e usuarios_pj não possuem campo "ativo".');
        break;

    case 'excluir':
        // Excluir usuário
        try {
            $id = Request::post('id', '');
            $tipo = Request::post('tipo', '');
            
            if (empty($id) || empty($tipo)) {
                Response::error('ID e tipo são obrigatórios');
            }
            
            $tabela = $tipo === 'pf' ? 'usuarios_pf' : 'usuarios_pj';
            
            $resultado = $db->table($tabela)
                ->where('id', $id)
                ->delete();
            
            if ($resultado) {
                Response::success('Usuário excluído com sucesso!');
            } else {
                Response::error('Erro ao excluir usuário');
            }
            
        } catch (\Exception $e) {
            Response::error('Erro ao excluir usuário: ' . $e->getMessage());
        }
        break;

    default:
        Response::error('Ação não reconhecida');
        break;
}

