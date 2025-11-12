
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema descubra_muriae
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `descubra_muriae` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `descubra_muriae`;

-- -----------------------------------------------------
-- Tabela cidade
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cidade`;
CREATE TABLE IF NOT EXISTS `cidade` (
  `cidade_id` INT NOT NULL AUTO_INCREMENT,
  `cidade` VARCHAR(200) NOT NULL,
  `uf` CHAR(2) NOT NULL,
  PRIMARY KEY (`cidade_id`),
  UNIQUE KEY `uk_cidade_uf` (`cidade`, `uf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela usuario_tipo
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuario_tipo`;
CREATE TABLE IF NOT EXISTS `usuario_tipo` (
  `usuario_tipo_id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(10) NOT NULL,
  `descricao` VARCHAR(60) NOT NULL,
  PRIMARY KEY (`usuario_tipo_id`),
  UNIQUE KEY `uk_usuario_tipo_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela pessoa
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pessoa`;
CREATE TABLE IF NOT EXISTS `pessoa` (
  `pessoa_id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `cpf` CHAR(11) NULL DEFAULT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `nascimento` DATE NULL DEFAULT NULL,
  `sexo` CHAR(1) NULL DEFAULT NULL COMMENT 'M=Masculino;F=Feminino;O=Outro',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pessoa_id`),
  UNIQUE KEY `uk_pessoa_cpf` (`cpf`),
  UNIQUE KEY `uk_pessoa_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela usuario
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `usuario_id` INT NOT NULL AUTO_INCREMENT,
  `pessoa_id` INT NOT NULL,
  `login` VARCHAR(80) NOT NULL,
  `senha_hash` VARCHAR(255) NOT NULL,
  `usuario_tipo_id` TINYINT UNSIGNED NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `uk_usuario_login` (`login`),
  KEY `idx_usuario_pessoa` (`pessoa_id`),
  KEY `idx_usuario_tipo` (`usuario_tipo_id`),
  CONSTRAINT `fk_usuario_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoa` (`pessoa_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuario_tipo` FOREIGN KEY (`usuario_tipo_id`) REFERENCES `usuario_tipo` (`usuario_tipo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela empresa
-- -----------------------------------------------------
DROP TABLE IF EXISTS `empresa`;
CREATE TABLE IF NOT EXISTS `empresa` (
  `empresa_id` INT NOT NULL AUTO_INCREMENT,
  `cnpj` VARCHAR(14) NOT NULL,
  `nome_social` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `site` VARCHAR(500) NULL,
  `linkedin` VARCHAR(500) NULL,
  `sobre` TEXT NULL,
  `funcionarios` VARCHAR(50) NULL,
  `fundacao` YEAR NULL,
  `logradouro` VARCHAR(255) NULL,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NULL,
  `bairro` VARCHAR(100) NULL,
  `cep` VARCHAR(8) NULL,
  `cidade_id` INT NULL,
  `logo` VARCHAR(500) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`empresa_id`),
  UNIQUE KEY `uk_empresa_cnpj` (`cnpj`),
  KEY `idx_empresa_cidade` (`cidade_id`),
  CONSTRAINT `fk_empresa_cidade` FOREIGN KEY (`cidade_id`) REFERENCES `cidade` (`cidade_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela telefone
-- -----------------------------------------------------
DROP TABLE IF EXISTS `telefone`;
CREATE TABLE IF NOT EXISTS `telefone` (
  `telefone_id` INT NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(20) NOT NULL,
  `tipo` ENUM('mobile', 'f', 'm') NOT NULL DEFAULT 'mobile' COMMENT 'mobile=celular;f=fixo;m=móvel',
  PRIMARY KEY (`telefone_id`),
  KEY `idx_telefone_numero` (`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela empresa_telefone
-- -----------------------------------------------------
DROP TABLE IF EXISTS `empresa_telefone`;
CREATE TABLE IF NOT EXISTS `empresa_telefone` (
  `empresa_telefone_id` INT NOT NULL AUTO_INCREMENT,
  `empresa_id` INT NOT NULL,
  `telefone_id` INT NOT NULL,
  `principal` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`empresa_telefone_id`),
  KEY `idx_empresa_telefone_empresa` (`empresa_id`),
  KEY `idx_empresa_telefone_telefone` (`telefone_id`),
  CONSTRAINT `fk_empresa_telefone_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`empresa_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_empresa_telefone_telefone` FOREIGN KEY (`telefone_id`) REFERENCES `telefone` (`telefone_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela curriculo
-- -----------------------------------------------------
DROP TABLE IF EXISTS `curriculo`;
CREATE TABLE IF NOT EXISTS `curriculo` (
  `curriculo_id` INT NOT NULL AUTO_INCREMENT,
  `pessoa_id` INT NOT NULL,
  `nome` VARCHAR(160) NOT NULL,
  `endereco` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(32) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `genero` VARCHAR(20) NOT NULL,
  `estado_civil` VARCHAR(20) NULL,
  `nascimento` DATE NOT NULL,
  `escolaridade` VARCHAR(100) NOT NULL,
  `outros_cursos` TEXT NULL,
  `foto` VARCHAR(255) NULL,
  `certificado` VARCHAR(255) NULL,
  `curriculo` VARCHAR(255) NULL COMMENT 'Arquivo PDF/DOC do currículo',
  `experiencias` JSON NULL,
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`curriculo_id`),
  KEY `idx_curriculo_pessoa` (`pessoa_id`),
  CONSTRAINT `fk_curriculo_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoa` (`pessoa_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela cargo
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cargo`;
CREATE TABLE IF NOT EXISTS `cargo` (
  `cargo_id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`cargo_id`),
  UNIQUE KEY `uk_cargo_descricao` (`descricao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela modalidade_trabalho
-- -----------------------------------------------------
DROP TABLE IF EXISTS `modalidade_trabalho`;
CREATE TABLE IF NOT EXISTS `modalidade_trabalho` (
  `modalidade_trabalho_id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NOT NULL,
  `descricao` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`modalidade_trabalho_id`),
  UNIQUE KEY `uk_modalidade_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela vinculo_contratual
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vinculo_contratual`;
CREATE TABLE IF NOT EXISTS `vinculo_contratual` (
  `vinculo_contratual_id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NOT NULL,
  `descricao` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`vinculo_contratual_id`),
  UNIQUE KEY `uk_vinculo_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela status_vaga
-- -----------------------------------------------------
DROP TABLE IF EXISTS `status_vaga`;
CREATE TABLE IF NOT EXISTS `status_vaga` (
  `status_vaga_id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NOT NULL,
  `descricao` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`status_vaga_id`),
  UNIQUE KEY `uk_status_vaga_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela categoria_vaga
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categoria_vaga`;
CREATE TABLE IF NOT EXISTS `categoria_vaga` (
  `categoria_vaga_id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` TEXT NULL,
  PRIMARY KEY (`categoria_vaga_id`),
  UNIQUE KEY `uk_categoria_vaga_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela vaga
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vaga`;
CREATE TABLE IF NOT EXISTS `vaga` (
  `vaga_id` INT NOT NULL AUTO_INCREMENT,
  `empresa_id` INT NOT NULL,
  `cargo_id` INT NULL,
  `categoria_vaga_id` INT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NOT NULL,
  `modalidade_trabalho_id` INT NOT NULL,
  `vinculo_contratual_id` INT NOT NULL,
  `status_vaga_id` INT NOT NULL,
  `dt_inicio` DATE NOT NULL,
  `dt_fim` DATE NOT NULL,
  `salario` VARCHAR(100) NULL,
  `requisitos` TEXT NULL,
  `beneficios` TEXT NULL,
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`vaga_id`),
  KEY `idx_vaga_empresa` (`empresa_id`),
  KEY `idx_vaga_cargo` (`cargo_id`),
  KEY `idx_vaga_categoria` (`categoria_vaga_id`),
  KEY `idx_vaga_modalidade` (`modalidade_trabalho_id`),
  KEY `idx_vaga_vinculo` (`vinculo_contratual_id`),
  KEY `idx_vaga_status` (`status_vaga_id`),
  KEY `idx_vaga_dt_fim` (`dt_fim`),
  CONSTRAINT `fk_vaga_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`empresa_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vaga_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargo` (`cargo_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vaga_categoria` FOREIGN KEY (`categoria_vaga_id`) REFERENCES `categoria_vaga` (`categoria_vaga_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vaga_modalidade` FOREIGN KEY (`modalidade_trabalho_id`) REFERENCES `modalidade_trabalho` (`modalidade_trabalho_id`),
  CONSTRAINT `fk_vaga_vinculo` FOREIGN KEY (`vinculo_contratual_id`) REFERENCES `vinculo_contratual` (`vinculo_contratual_id`),
  CONSTRAINT `fk_vaga_status` FOREIGN KEY (`status_vaga_id`) REFERENCES `status_vaga` (`status_vaga_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela status_candidatura
-- -----------------------------------------------------
DROP TABLE IF EXISTS `status_candidatura`;
CREATE TABLE IF NOT EXISTS `status_candidatura` (
  `status_candidatura_id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NOT NULL,
  `descricao` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`status_candidatura_id`),
  UNIQUE KEY `uk_status_candidatura_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela candidatura
-- -----------------------------------------------------
DROP TABLE IF EXISTS `candidatura`;
CREATE TABLE IF NOT EXISTS `candidatura` (
  `candidatura_id` INT NOT NULL AUTO_INCREMENT,
  `vaga_id` INT NOT NULL,
  `curriculo_id` INT NOT NULL,
  `status_candidatura_id` INT NOT NULL,
  `data_candidatura` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_avaliacao` TIMESTAMP NULL,
  `observacoes` TEXT NULL,
  PRIMARY KEY (`candidatura_id`),
  KEY `idx_candidatura_vaga` (`vaga_id`),
  KEY `idx_candidatura_curriculo` (`curriculo_id`),
  KEY `idx_candidatura_status` (`status_candidatura_id`),
  UNIQUE KEY `uk_candidatura_vaga_curriculo` (`vaga_id`, `curriculo_id`),
  CONSTRAINT `fk_candidatura_vaga` FOREIGN KEY (`vaga_id`) REFERENCES `vaga` (`vaga_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_candidatura_curriculo` FOREIGN KEY (`curriculo_id`) REFERENCES `curriculo` (`curriculo_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_candidatura_status` FOREIGN KEY (`status_candidatura_id`) REFERENCES `status_candidatura` (`status_candidatura_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela administradores
-- -----------------------------------------------------
DROP TABLE IF EXISTS `administradores`;
CREATE TABLE IF NOT EXISTS `administradores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(160) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `ativo` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_email` (`email`),
  KEY `idx_admin_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela reset_tokens
-- -----------------------------------------------------
DROP TABLE IF EXISTS `reset_tokens`;
CREATE TABLE IF NOT EXISTS `reset_tokens` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(160) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `tipo_usuario` ENUM('pf', 'pj') NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reset_token` (`token`),
  KEY `idx_reset_email` (`email`),
  KEY `idx_reset_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------
-- Tabela  Dados Iniciais
-- -----------------------------------------------------

-- Usuario Tipo
INSERT INTO `usuario_tipo` (`codigo`, `descricao`) VALUES
('ANUNC', 'Anunciante'),
('GEST', 'Gestor'),
('CONT', 'Contribuinte Normativo'),
('ADMIN', 'Administrador')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);

-- Modalidade Trabalho
INSERT INTO `modalidade_trabalho` (`codigo`, `descricao`) VALUES
('PRESENCIAL', 'Presencial'),
('REMOTO', 'Remoto'),
('HIBRIDO', 'Híbrido')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);

-- Vinculo Contratual
INSERT INTO `vinculo_contratual` (`codigo`, `descricao`) VALUES
('CLT', 'CLT'),
('PJ', 'Pessoa Jurídica'),
('ESTAGIO', 'Estágio'),
('TEMPORARIO', 'Temporário')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);

-- Status Vaga
INSERT INTO `status_vaga` (`codigo`, `descricao`) VALUES
('RASCUNHO', 'Rascunho'),
('ABERTA', 'Aberta'),
('PAUSADA', 'Pausada'),
('FECHADA', 'Fechada')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);

-- Categoria Vaga
INSERT INTO `categoria_vaga` (`nome`, `descricao`) VALUES
('tecnologia', 'Tecnologia da Informação'),
('administracao', 'Administração'),
('comercio', 'Comércio'),
('saude', 'Saúde'),
('educacao', 'Educação'),
('engenharia', 'Engenharia'),
('agronegocio', 'Agronegócio'),
('servicos', 'Serviços'),
('outros', 'Outros')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);

-- Status Candidatura
INSERT INTO `status_candidatura` (`codigo`, `descricao`) VALUES
('PENDENTE', 'Pendente'),
('EM_ANALISE', 'Em Análise'),
('APROVADA', 'Aprovada'),
('REJEITADA', 'Rejeitada')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);

-- Cidade (Muriaé)
INSERT INTO `cidade` (`cidade`, `uf`) VALUES
('Muriaé', 'MG')
ON DUPLICATE KEY UPDATE `cidade` = VALUES(`cidade`);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
