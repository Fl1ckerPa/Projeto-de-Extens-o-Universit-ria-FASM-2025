-- Script para criar as tabelas de vagas e candidaturas
-- Execute este script no seu banco de dados MySQL

USE descubra_muriae;

-- Tabela de vagas
CREATE TABLE IF NOT EXISTS vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    salario VARCHAR(100),
    tipo_contrato VARCHAR(50),
    data_publicacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_limite DATE NOT NULL,
    status ENUM('Aberta', 'Pausada', 'Fechada') DEFAULT 'Aberta',
    descricao TEXT NOT NULL,
    requisitos TEXT,
    beneficios TEXT,
    empresa_id INT NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_empresa (empresa_id),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria),
    INDEX idx_data_limite (data_limite),
    INDEX idx_ativo (ativo),
    
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- Tabela de candidaturas
CREATE TABLE IF NOT EXISTS candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vaga_id INT NOT NULL,
    pessoa_id INT NOT NULL,
    status ENUM('Pendente', 'Aprovado', 'Reprovado') DEFAULT 'Pendente',
    data_candidatura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_avaliacao TIMESTAMP NULL,
    observacoes TEXT,
    
    INDEX idx_vaga (vaga_id),
    INDEX idx_pessoa (pessoa_id),
    INDEX idx_status (status),
    INDEX idx_data_candidatura (data_candidatura),
    
    FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE CASCADE,
    FOREIGN KEY (pessoa_id) REFERENCES pessoas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidatura (vaga_id, pessoa_id)
);

-- Tabela de pessoas (candidatos)
CREATE TABLE IF NOT EXISTS pessoas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    endereco TEXT,
    nascimento DATE,
    curriculo JSON,
    ativo TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_ativo (ativo)
);

-- Inserir dados de exemplo
INSERT INTO vagas (titulo, categoria, salario, tipo_contrato, data_limite, status, descricao, requisitos, beneficios, empresa_id) VALUES
('Desenvolvedor Full Stack', 'tecnologia', 'R$ 5.000,00', 'CLT', '2025-02-15', 'Aberta', 'Desenvolvedor para trabalhar com React, Node.js e banco de dados PostgreSQL. Experiência em desenvolvimento web moderno.', 'Conhecimento em JavaScript, React, Node.js, PostgreSQL. Experiência mínima de 2 anos.', 'Vale refeição, plano de saúde, ambiente de trabalho flexível.', 1),
('Vendedor Externo', 'comercio', 'R$ 2.500,00 + Comissão', 'CLT', '2025-02-10', 'Aberta', 'Vendedor para atuar na região de Muriaé e cidades vizinhas. Experiência em vendas B2B.', 'CNH categoria B, experiência em vendas, boa comunicação.', 'Comissão sobre vendas, vale combustível, celular corporativo.', 1),
('Assistente Administrativo', 'administracao', 'R$ 2.000,00', 'CLT', '2025-01-25', 'Pausada', 'Assistente para área administrativa com foco em atendimento ao cliente e organização de documentos.', 'Ensino médio completo, conhecimento em informática básica, boa organização.', 'Vale refeição, plano de saúde, ambiente climatizado.', 1)
ON DUPLICATE KEY UPDATE titulo = titulo;

INSERT INTO pessoas (nome, email, telefone, endereco, nascimento, curriculo) VALUES
('João Silva', 'joao.silva@email.com', '(32) 99999-9999', 'Rua das Flores, 123 - Muriaé/MG', '1995-03-15', '{"dadosPessoais": {"nome": "João Silva", "email": "joao.silva@email.com", "telefone": "(32) 99999-9999", "endereco": "Rua das Flores, 123 - Muriaé/MG", "nascimento": "1995-03-15"}, "formacao": {"escolaridade": "Graduação - Completo", "curso": "Ciência da Computação", "instituicao": "Universidade Federal de Viçosa"}, "experiencia": [{"empresa": "Tech Solutions", "cargo": "Desenvolvedor Frontend", "periodo": "2022-2024", "atividades": "Desenvolvimento de interfaces web com React e Vue.js"}], "sobre": "Desenvolvedor apaixonado por tecnologia, com foco em soluções web modernas e eficientes."}'),
('Maria Santos', 'maria.santos@email.com', '(32) 88888-8888', 'Av. Central, 456 - Muriaé/MG', '1990-07-22', '{"dadosPessoais": {"nome": "Maria Santos", "email": "maria.santos@email.com", "telefone": "(32) 88888-8888", "endereco": "Av. Central, 456 - Muriaé/MG", "nascimento": "1990-07-22"}, "formacao": {"escolaridade": "Graduação - Completo", "curso": "Sistemas de Informação", "instituicao": "PUC Minas"}, "experiencia": [{"empresa": "Digital Corp", "cargo": "Desenvolvedora Full Stack", "periodo": "2020-2024", "atividades": "Desenvolvimento completo de aplicações web e mobile"}], "sobre": "Profissional dedicada com ampla experiência em desenvolvimento de software e liderança de equipes."}')
ON DUPLICATE KEY UPDATE nome = nome;

INSERT INTO candidaturas (vaga_id, pessoa_id, status, data_candidatura) VALUES
(1, 1, 'Pendente', '2025-01-16 10:30:00'),
(1, 2, 'Pendente', '2025-01-17 14:20:00'),
(2, 1, 'Aprovado', '2025-01-15 09:15:00')
ON DUPLICATE KEY UPDATE status = status;
