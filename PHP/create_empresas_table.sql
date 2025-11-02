-- Script para criar a tabela de empresas
-- Execute este script no seu banco de dados MySQL

CREATE DATABASE IF NOT EXISTS descubra_muriae;
USE descubra_muriae;

CREATE TABLE IF NOT EXISTS empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cnpj VARCHAR(14) NOT NULL UNIQUE,
    nome_social VARCHAR(255) NOT NULL,
    segmento VARCHAR(100) NOT NULL,
    endereco VARCHAR(500) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    cep VARCHAR(8),
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    site VARCHAR(500),
    linkedin VARCHAR(500),
    sobre TEXT NOT NULL,
    funcionarios VARCHAR(50),
    fundacao YEAR,
    logo VARCHAR(500),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ativo TINYINT(1) DEFAULT 1,
    
    INDEX idx_cnpj (cnpj),
    INDEX idx_segmento (segmento),
    INDEX idx_cidade (cidade),
    INDEX idx_estado (estado),
    INDEX idx_ativo (ativo)
);

-- Inserir alguns segmentos de exemplo (opcional)
INSERT INTO empresas (cnpj, nome_social, segmento, endereco, cidade, estado, email, telefone, sobre, ativo) VALUES
('12345678000195', 'Empresa Exemplo LTDA', 'tecnologia', 'Rua Exemplo, 123', 'Muriaé', 'MG', 'contato@exemplo.com', '32999999999', 'Esta é uma empresa de exemplo para demonstração do sistema.', 0)
ON DUPLICATE KEY UPDATE nome_social = nome_social;
