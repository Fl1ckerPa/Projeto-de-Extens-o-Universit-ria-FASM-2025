<?php

// Processa envio do formulário de currículo com validações de servidor

function limpar($v) { return htmlspecialchars(stripslashes(trim((string)$v))); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Método não permitido';
  exit;
}

$erros = [];

// Parte 1
$nome = limpar($_POST['nome'] ?? '');
$endereco = limpar($_POST['endereco'] ?? '');
$telefone = limpar($_POST['telefone'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $_POST['email'] : '';
$genero = limpar($_POST['genero'] ?? '');
$estado_civil = limpar($_POST['estado_civil'] ?? '');
$nascimento = limpar($_POST['nascimento'] ?? '');

if ($nome === '') $erros[] = 'Nome completo é obrigatório.';
if ($endereco === '') $erros[] = 'Endereço é obrigatório.';
if ($telefone === '') $erros[] = 'Telefone é obrigatório.';
if ($email === '') $erros[] = 'E-mail inválido ou vazio.';
if ($genero === '') $erros[] = 'Gênero é obrigatório.';
if ($nascimento === '') $erros[] = 'Data de nascimento é obrigatória.';

// Arquivo: foto (1MB, jpg/jpeg/png/gif)
if (!empty($_FILES['foto']['name'])) {
  $okExt = ['jpg','jpeg','png','gif'];
  $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $okExt, true)) $erros[] = 'Foto: formato inválido.';
  if ($_FILES['foto']['size'] > 1 * 1024 * 1024) $erros[] = 'Foto: tamanho máximo 1MB.';
  if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) $erros[] = 'Foto: erro no upload.';
}

// Parte 2
$escolaridade = limpar($_POST['escolaridade'] ?? '');
$outros_cursos = limpar($_POST['outros_cursos'] ?? '');
if ($escolaridade === '') $erros[] = 'Grau de escolaridade é obrigatório.';

// certificado (5MB, pdf/jpg/jpeg/png)
if (!empty($_FILES['certificado']['name'])) {
  $okExt = ['pdf','jpg','jpeg','png'];
  $ext = strtolower(pathinfo($_FILES['certificado']['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $okExt, true)) $erros[] = 'Certificado: formato inválido.';
  if ($_FILES['certificado']['size'] > 5 * 1024 * 1024) $erros[] = 'Certificado: tamanho máximo 5MB.';
  if ($_FILES['certificado']['error'] !== UPLOAD_ERR_OK) $erros[] = 'Certificado: erro no upload.';
}

// Parte 3 - experiências dinâmicas
$empresas = $_POST['empresa'] ?? [];
$cargos = $_POST['cargo'] ?? [];
$atividades = $_POST['atividades'] ?? [];

if (!is_array($empresas) || !is_array($cargos) || !is_array($atividades)) {
  $erros[] = 'Estrutura de experiências inválida.';
} else {
  $maxExp = 5;
  if (count($empresas) > $maxExp) $erros[] = 'Excedido o limite de experiências (máx 5).';
  // Pelo menos 1 experiência
  if (count($empresas) === 0) $erros[] = 'Informe ao menos uma experiência.';
  // Validar cada experiência mínima
  foreach ($empresas as $i => $empresa) {
    $empresa = limpar($empresa);
    $cargo = limpar($cargos[$i] ?? '');
    $atividade = limpar($atividades[$i] ?? '');
    if ($empresa === '' || $cargo === '' || $atividade === '') {
      $erros[] = 'Preencha Empresa, Cargo/Função e Atividades em todas as experiências.';
      break;
    }
  }
}

// currículo (10MB, pdf/doc/docx/txt)
if (!empty($_FILES['curriculo']['name'])) {
  $okExt = ['pdf','doc','docx','txt'];
  $ext = strtolower(pathinfo($_FILES['curriculo']['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $okExt, true)) $erros[] = 'Currículo: formato inválido.';
  if ($_FILES['curriculo']['size'] > 10 * 1024 * 1024) $erros[] = 'Currículo: tamanho máximo 10MB.';
  if ($_FILES['curriculo']['error'] !== UPLOAD_ERR_OK) $erros[] = 'Currículo: erro no upload.';
}

// Saída
if (!empty($erros)) {
  $title = 'Erros no cadastro de currículo';
  $messages = $erros;
  $type = 'error';
  $links = [ '../HTML/Cadastro_de_currículo.html' => 'Voltar' ];
  include __DIR__ . '/partials/layout.php';
  exit;
}

// Sucesso (placeholder: aqui você pode salvar em BD, mover uploads, etc.)
$title = 'Cadastro recebido com sucesso!';
$messages = [
  'Seus dados foram validados. Em breve entraremos em contato.',
  'Resumo: ' . htmlspecialchars($nome) . ' - ' . htmlspecialchars($email)
];
$type = 'success';
$links = [ '../HTML/index.html' => 'Voltar ao início' ];
include __DIR__ . '/partials/layout.php';

?>


