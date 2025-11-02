<?php
/**
 * Files - Classe para upload de arquivos
 * Adaptada do AtomPHP
 */

class Files
{
    private $pathFile;
    private $allowedTypes = [];
    private $maxSize;

    public function __construct(
        $uploadPath = null,
        array $allowedTypes = null,
        int $maxSizeMB = null
    ) {
        $this->pathFile = $uploadPath ?? UPLOADS_PATH . DIRECTORY_SEPARATOR;
        $this->allowedTypes = $allowedTypes ?? FILE_ALLOWEDTYPES;
        $this->maxSize = ($maxSizeMB ?? FILE_MAXSIZE) * 1024 * 1024;
    }

    /**
     * Upload de arquivo único
     * @param array $arquivo - Array do $_FILES['campo']
     * @param string $pasta - Pasta de destino
     * @param string $prefixo - Prefixo opcional para o nome
     * @return array ['status' => bool, 'path' => string, 'message' => string]
     */
    public function upload($arquivo, $pasta, $prefixo = '')
    {
        $diretorioUpload = $this->pathFile . $pasta . DIRECTORY_SEPARATOR;
        
        if (!is_dir($diretorioUpload) && !mkdir($diretorioUpload, 0755, true)) {
            return ['status' => false, 'message' => 'Falha ao criar diretório'];
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'message' => 'Erro no upload do arquivo'];
        }

        // Verificar extensão
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
        
        if (!in_array($extensao, $extensoesPermitidas)) {
            return ['status' => false, 'message' => 'Tipo de arquivo não permitido. Extensões permitidas: ' . implode(', ', $extensoesPermitidas)];
        }

        // Verificar tipo MIME também (segurança adicional)
        $mimeTypesPermitidos = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $arquivo['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $mimeTypesPermitidos)) {
            return ['status' => false, 'message' => 'Tipo MIME do arquivo não permitido'];
        }

        if ($arquivo['size'] > $this->maxSize) {
            return ['status' => false, 'message' => 'Arquivo muito grande. Máximo: ' . (FILE_MAXSIZE) . 'MB'];
        }

        $nomeArquivo = ($prefixo ? $prefixo . '_' : '') . uniqid() . '_' . time() . '.' . $extensao;
        $caminhoCompleto = $diretorioUpload . $nomeArquivo;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            return ['status' => false, 'message' => 'Falha ao salvar arquivo'];
        }

        return [
            'status' => true,
            'path' => $pasta . DIRECTORY_SEPARATOR . $nomeArquivo,
            'nome' => $nomeArquivo,
            'message' => 'Upload realizado com sucesso'
        ];
    }

    /**
     * Deleta um arquivo
     */
    public function delete($nomeArquivo, $pasta)
    {
        $caminhoCompleto = $this->pathFile . $pasta . DIRECTORY_SEPARATOR . $nomeArquivo;
        if (file_exists($caminhoCompleto)) {
            return unlink($caminhoCompleto);
        }
        return false;
    }
}

