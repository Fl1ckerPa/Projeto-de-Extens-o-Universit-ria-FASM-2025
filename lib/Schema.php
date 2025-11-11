<?php
/**
 * Schema - utilitário para garantir que o banco esteja com o modelo normalizado
 */

class Schema
{
    /**
     * Garante que a estrutura normalizada exista; se a tabela `pessoa` não for encontrada,
     * executa o script `banco de dados/descubramuriae.sql`.
     *
     * ATENÇÃO: o script derruba tabelas antigas. Garanta backup prévio antes de rodar em produção.
     */
    public static function ensureNormalizedSchema(\PDO $pdo): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $checked = true;

        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'pessoa'");
            if ($stmt !== false && $stmt->rowCount() > 0) {
                return;
            }
        } catch (\PDOException $e) {
            // Continua para tentar instalar a estrutura
        }

        // 1) Cria o núcleo do esquema normalizado mínimo para cadastro/login
        self::createCoreNormalizedSchema($pdo);

        // 2) Em seguida, se existir, tenta aplicar o arquivo de estrutura (idempotente)
        $sqlPath = BASE_PATH . DIRECTORY_SEPARATOR . 'banco de dados' . DIRECTORY_SEPARATOR . 'descubramuriae.sql';
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            if ($sqlContent !== false) {
                self::executeSqlBatch($pdo, $sqlContent);
            }
        }
    }

    /**
     * Executa um lote de comandos SQL (separados por ";") de maneira segura.
     */
    private static function executeSqlBatch(\PDO $pdo, string $sql): void
    {
        $statements = preg_split('/;(\s*[\r\n]+|\s*$)/', $sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '' || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
                continue;
            }

            // Ignorar restauração de variáveis @OLD_* se não existirem na sessão
            if (preg_match('/^SET\s+(UNIQUE_CHECKS|FOREIGN_KEY_CHECKS|SQL_MODE)\s*=\s*@OLD_\1/i', $statement)) {
                // Pula sem erro para rodadas repetidas/parciais
                continue;
            }

            try {
                // Evitar erro por índice duplicado (1061) checando antes
                if (preg_match('/^CREATE\s+(?:FULLTEXT\s+)?INDEX\s+`?([A-Za-z0-9_]+)`?\s+ON\s+`?([A-Za-z0-9_]+)`?\.`?([A-Za-z0-9_]+)`?/i', $statement, $m)) {
                    $indexName = $m[1];
                    $schemaName = $m[2];
                    $tableName = $m[3];
                    $checkSql = "SELECT 1 FROM information_schema.statistics 
                                 WHERE table_schema = :schema AND table_name = :table AND index_name = :index LIMIT 1";
                    $check = $pdo->prepare($checkSql);
                    $check->execute([':schema' => $schemaName, ':table' => $tableName, ':index' => $indexName]);
                    if ($check->fetch()) {
                        // Índice já existe, pula este statement
                        continue;
                    }
                }

                $pdo->exec($statement);
            } catch (\PDOException $e) {
                // Ignorar erro específico de índice duplicado (1061) para rodadas repetidas
                if (strpos($e->getMessage(), '1061 Duplicate key name') !== false) {
                    continue;
                }
                throw new \RuntimeException('Erro ao executar SQL: ' . $e->getMessage() . ' | Trecho: ' . $statement);
            }
        }
    }

    /**
     * Cria o conjunto mínimo de tabelas para suportar cadastro/login no modelo normalizado.
     * Usa IF NOT EXISTS para ser idempotente.
     */
    private static function createCoreNormalizedSchema(\PDO $pdo): void
    {
        $ddl = [
            // Cidade
            "CREATE TABLE IF NOT EXISTS cidade (
                cidade_id INT NOT NULL AUTO_INCREMENT,
                cidade VARCHAR(200) NOT NULL,
                uf CHAR(2) NOT NULL,
                PRIMARY KEY (cidade_id),
                UNIQUE KEY uk_cidade_uf (cidade, uf)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            // usuario_tipo
            "CREATE TABLE IF NOT EXISTS usuario_tipo (
                usuario_tipo_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
                codigo VARCHAR(10) NOT NULL,
                descricao VARCHAR(60) NOT NULL,
                PRIMARY KEY (usuario_tipo_id),
                UNIQUE KEY uk_usuario_tipo_codigo (codigo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            // pessoa
            "CREATE TABLE IF NOT EXISTS pessoa (
                pessoa_id INT NOT NULL AUTO_INCREMENT,
                nome VARCHAR(150) NOT NULL,
                cpf CHAR(11) NULL DEFAULT NULL,
                email VARCHAR(255) NULL DEFAULT NULL,
                nascimento DATE NULL DEFAULT NULL,
                sexo CHAR(1) NULL DEFAULT NULL,
                ativo TINYINT(1) NOT NULL DEFAULT 1,
                data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (pessoa_id),
                UNIQUE KEY uk_pessoa_cpf (cpf),
                UNIQUE KEY uk_pessoa_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            // usuario
            "CREATE TABLE IF NOT EXISTS usuario (
                usuario_id INT NOT NULL AUTO_INCREMENT,
                pessoa_id INT NOT NULL,
                login VARCHAR(80) NOT NULL,
                senha_hash VARCHAR(255) NOT NULL,
                usuario_tipo_id TINYINT UNSIGNED NOT NULL,
                ativo TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (usuario_id),
                UNIQUE KEY uk_usuario_login (login),
                KEY idx_usuario_pessoa (pessoa_id),
                KEY idx_usuario_tipo (usuario_tipo_id),
                CONSTRAINT fk_usuario_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoa (pessoa_id),
                CONSTRAINT fk_usuario_tipo FOREIGN KEY (usuario_tipo_id) REFERENCES usuario_tipo (usuario_tipo_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            // empresa
            "CREATE TABLE IF NOT EXISTS empresa (
                empresa_id INT NOT NULL AUTO_INCREMENT,
                cnpj VARCHAR(14) NOT NULL,
                nome_social VARCHAR(255) NOT NULL,
                email VARCHAR(255) NULL,
                cidade_id INT NULL,
                ativo TINYINT(1) NOT NULL DEFAULT 1,
                data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (empresa_id),
                UNIQUE KEY uk_empresa_cnpj (cnpj),
                KEY idx_empresa_cidade (cidade_id),
                CONSTRAINT fk_empresa_cidade FOREIGN KEY (cidade_id) REFERENCES cidade (cidade_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        foreach ($ddl as $sql) {
            $pdo->exec($sql);
        }

        // Seeds de usuario_tipo
        $seeds = [
            ['ANUNC', 'Anunciante'],
            ['GEST', 'Gestor'],
            ['CONT', 'Contribuinte Normativo']
        ];
        foreach ($seeds as [$codigo, $descricao]) {
            $stmt = $pdo->prepare("INSERT INTO usuario_tipo (codigo, descricao) VALUES (?, ?)
                                   ON DUPLICATE KEY UPDATE descricao = VALUES(descricao)");
            $stmt->execute([$codigo, $descricao]);
        }

        // Reconcilia tabela `usuario` legada (ex.: coluna pessoa_fisica_id ao invés de pessoa_id)
        self::reconcileLegacyUsuarioTable($pdo);
    }

    /**
     * Ajusta a tabela `usuario` legada para conter colunas esperadas pelo modelo normalizado.
     * Não remove colunas antigas; apenas adiciona as novas se estiverem ausentes.
     */
    private static function reconcileLegacyUsuarioTable(\PDO $pdo): void
    {
        // Descobrir schema corrente
        $schemaStmt = $pdo->query("SELECT DATABASE() AS db");
        $schemaRow = $schemaStmt ? $schemaStmt->fetch(\PDO::FETCH_ASSOC) : null;
        $schema = $schemaRow && !empty($schemaRow['db']) ? $schemaRow['db'] : null;
        if (!$schema) return;

        $hasUsuario = $pdo->query("SHOW TABLES LIKE 'usuario'");
        if (!$hasUsuario || $hasUsuario->rowCount() === 0) {
            return;
        }

        // Helpers
        $hasColumn = function(string $col) use ($pdo, $schema) {
            $q = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = 'usuario' AND column_name = ? LIMIT 1");
            $q->execute([$schema, $col]);
            return (bool)$q->fetchColumn();
        };
        $dropForeignKeysByColumn = function(string $col) use ($pdo, $schema) {
            $stmt = $pdo->prepare("SELECT constraint_name FROM information_schema.key_column_usage
                WHERE table_schema = ? AND table_name = 'usuario' AND column_name = ? AND referenced_table_name IS NOT NULL");
            $stmt->execute([$schema, $col]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $fk) {
                try {
                    $pdo->exec("ALTER TABLE usuario DROP FOREIGN KEY `$fk`");
                } catch (\Throwable $e) {}
            }
        };
        $dropIndexesByColumn = function(string $col) use ($pdo, $schema) {
            $stmt = $pdo->prepare("SELECT DISTINCT index_name FROM information_schema.statistics
                WHERE table_schema = ? AND table_name = 'usuario' AND column_name = ? AND index_name <> 'PRIMARY'");
            $stmt->execute([$schema, $col]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $idx) {
                try {
                    $pdo->exec("ALTER TABLE usuario DROP INDEX `$idx`");
                } catch (\Throwable $e) {}
            }
        };

        // Renomear colunas legadas para manter dados
        if ($hasColumn('pessoa_fisica_id') && !$hasColumn('pessoa_id')) {
            try {
                $dropForeignKeysByColumn('pessoa_fisica_id');
                $dropIndexesByColumn('pessoa_fisica_id');
                $pdo->exec("ALTER TABLE usuario CHANGE pessoa_fisica_id pessoa_id INT NULL");
            } catch (\Throwable $e) {}
        }
        if ($hasColumn('senha') && !$hasColumn('senha_hash')) {
            try {
                $pdo->exec("ALTER TABLE usuario CHANGE senha senha_hash VARCHAR(255) NULL");
            } catch (\Throwable $e) {}
        }
        if ($hasColumn('tipo') && !$hasColumn('usuario_tipo_id')) {
            try {
                $pdo->exec("ALTER TABLE usuario ADD COLUMN usuario_tipo_id TINYINT UNSIGNED NULL");
            } catch (\Throwable $e) {}
        }

        // pessoa_id
        if (!$hasColumn('pessoa_id')) {
            try {
                $pdo->exec("ALTER TABLE usuario ADD COLUMN pessoa_id INT NULL");
            } catch (\Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE usuario ADD INDEX idx_usuario_pessoa (pessoa_id)");
            } catch (\Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE usuario ADD CONSTRAINT fk_usuario_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoa(pessoa_id)");
            } catch (\Throwable $e) {}
        }

        // login
        if (!$hasColumn('login')) {
            try {
                $pdo->exec("ALTER TABLE usuario ADD COLUMN login VARCHAR(80) NOT NULL");
            } catch (\Throwable $e) {}
        }
        // unique index para login
        try {
            $idx = $pdo->prepare("SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = 'usuario' AND index_name = 'uk_usuario_login' LIMIT 1");
            $idx->execute([$schema]);
            if (!$idx->fetch()) {
                $pdo->exec("ALTER TABLE usuario ADD UNIQUE KEY uk_usuario_login (login)");
            }
        } catch (\Throwable $e) {}

        // senha_hash
        if (!$hasColumn('senha_hash')) {
            try {
                $pdo->exec("ALTER TABLE usuario ADD COLUMN senha_hash VARCHAR(255) NULL");
            } catch (\Throwable $e) {}
        }

        // usuario_tipo_id
        if (!$hasColumn('usuario_tipo_id')) {
            try {
                $pdo->exec("ALTER TABLE usuario ADD COLUMN usuario_tipo_id TINYINT UNSIGNED NULL");
            } catch (\Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE usuario ADD INDEX idx_usuario_tipo (usuario_tipo_id)");
            } catch (\Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE usuario ADD CONSTRAINT fk_usuario_tipo FOREIGN KEY (usuario_tipo_id) REFERENCES usuario_tipo(usuario_tipo_id)");
            } catch (\Throwable $e) {}
        }

        // Garantia final
        if (!$hasColumn('pessoa_id')) {
            throw new \RuntimeException("Falha ao preparar a tabela 'usuario': coluna 'pessoa_id' permanece ausente. Ajuste manualmente o schema legado.");
        }

        // Preencher usuario_tipo_id a partir da coluna legada 'tipo', se existir
        if ($hasColumn('tipo')) {
            try {
                $pdo->exec("UPDATE usuario u SET usuario_tipo_id = (
                    SELECT usuario_tipo_id FROM usuario_tipo WHERE codigo = 
                        CASE UPPER(u.tipo)
                            WHEN 'A' THEN 'ANUNC'
                            WHEN 'PJ' THEN 'ANUNC'
                            WHEN 'G' THEN 'GEST'
                            WHEN 'GEST' THEN 'GEST'
                            WHEN 'ADMIN' THEN 'GEST'
                            WHEN 'CN' THEN 'CONT'
                            WHEN 'PF' THEN 'CONT'
                            ELSE 'CONT'
                        END
                ) WHERE (usuario_tipo_id IS NULL OR usuario_tipo_id = 0)");
            } catch (\Throwable $e) {}
        }

        // Preencher login se estiver vazio usando email (coluna legada)
        if ($hasColumn('login')) {
            try {
                $pdo->exec("UPDATE usuario SET login = email WHERE (login IS NULL OR login = '') AND email IS NOT NULL");
            } catch (\Throwable $e) {}
        }
    }
}

