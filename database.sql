-- =====================================================
-- Sistema de Gerenciamento de Barbearia
-- Script Completo de Criação e Configuração do Banco
-- MariaDB / MySQL
-- =====================================================

CREATE DATABASE IF NOT EXISTS barbearia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barbearia;

-- =====================================================
-- 1. TABELAS
-- =====================================================

-- Tabela de usuários (login do sistema)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin','barbeiro') NOT NULL DEFAULT 'admin',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100),
    data_nascimento DATE NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de barbeiros
CREATE TABLE IF NOT EXISTS barbeiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100),
    especialidade VARCHAR(100),
    status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de serviços
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    duracao_minutos INT NOT NULL DEFAULT 30,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    barbeiro_id INT NOT NULL,
    servico_id INT NOT NULL,
    valor_servico DECIMAL(10,2) NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_agendamento TIME NOT NULL,
    status ENUM('agendado','concluido','cancelado') NOT NULL DEFAULT 'agendado',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_agendamento_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_agendamento_barbeiro FOREIGN KEY (barbeiro_id) REFERENCES barbeiros(id) ON DELETE RESTRICT,
    CONSTRAINT fk_agendamento_servico FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_barbeiro_data_hora (barbeiro_id, data_agendamento, hora_agendamento)
) ENGINE=InnoDB;

-- Tabela de pagamentos
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    forma_pagamento ENUM('dinheiro','pix','cartao') NOT NULL,
    pago_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagamento_agendamento FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabela de avaliações públicas da barbearia
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    nota TINYINT NOT NULL,
    comentario TEXT NOT NULL,
    aprovado TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_avaliacao_nota CHECK (nota BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- =====================================================
-- 2. ÍNDICES
-- =====================================================

CREATE INDEX idx_clientes_nome ON clientes (nome);
CREATE INDEX idx_barbeiros_nome ON barbeiros (nome);
CREATE INDEX idx_barbeiros_status ON barbeiros (status);
CREATE INDEX idx_servicos_nome ON servicos (nome);
CREATE INDEX idx_agendamentos_data ON agendamentos (data_agendamento);
CREATE INDEX idx_agendamentos_status ON agendamentos (status);
CREATE INDEX idx_agendamentos_barbeiro_data ON agendamentos (barbeiro_id, data_agendamento);
CREATE INDEX idx_pagamentos_agendamento ON pagamentos (agendamento_id);
CREATE INDEX idx_avaliacoes_aprovado ON avaliacoes (aprovado, criado_em);

-- =====================================================
-- 3. VIEWS (Incluindo Views Analíticas e CTEs)
-- =====================================================

-- View Centralizadora: Junta dados de 4 tabelas distintas
CREATE OR REPLACE VIEW vw_dashboard AS
SELECT
    a.id,
    c.nome AS cliente,
    b.nome AS barbeiro,
    s.nome AS servico,
    a.valor_servico AS preco,
    a.data_agendamento,
    a.hora_agendamento,
    a.status
FROM agendamentos a
INNER JOIN clientes c  ON c.id = a.cliente_id
INNER JOIN barbeiros b ON b.id = a.barbeiro_id
INNER JOIN servicos s  ON s.id = a.servico_id;

-- View Analítica usando CTE (Common Table Expression) para consolidar os dados brutos
CREATE OR REPLACE VIEW vw_analise_mensal_cte AS
WITH faturamento_mensal AS (
    SELECT 
        DATE_FORMAT(a.data_agendamento, '%Y-%m') AS mes_ano,
        COUNT(a.id) AS total_atendimentos,
        SUM(a.valor_servico) AS receita_total
    FROM agendamentos a
    WHERE a.status = 'concluido'
    GROUP BY DATE_FORMAT(a.data_agendamento, '%Y-%m')
)
SELECT 
    mes_ano,
    total_atendimentos,
    receita_total,
    ROUND(receita_total / total_atendimentos, 2) AS ticket_medio_mensal
FROM faturamento_mensal;

-- View Faturamento por Barbeiro
CREATE OR REPLACE VIEW vw_faturamento AS
SELECT
    b.nome AS barbeiro,
    COUNT(*) AS total_atendimentos,
    SUM(a.valor_servico) AS faturamento
FROM agendamentos a
INNER JOIN barbeiros b ON b.id = a.barbeiro_id
WHERE a.status = 'concluido'
GROUP BY b.nome;

-- View Ranking de Serviços
CREATE OR REPLACE VIEW vw_ranking_servicos AS
SELECT
    s.id,
    s.nome AS servico,
    COUNT(*) AS total_realizados,
    SUM(a.valor_servico) AS faturamento_gerado
FROM agendamentos a
INNER JOIN servicos s ON s.id = a.servico_id
WHERE a.status = 'concluido'
GROUP BY s.id, s.nome
ORDER BY total_realizados DESC;

-- =====================================================
-- 4. FUNCTIONS
-- =====================================================

DELIMITER $$

CREATE FUNCTION fn_calcular_idade(p_data_nascimento DATE)
RETURNS INT
DETERMINISTIC
BEGIN
    IF p_data_nascimento IS NULL THEN
        RETURN NULL;
    END IF;
    RETURN TIMESTAMPDIFF(YEAR, p_data_nascimento, CURDATE());
END$$

CREATE FUNCTION fn_valor_total_cliente(p_cliente_id INT)
RETURNS DECIMAL(10,2)
READS SQL DATA
BEGIN
    DECLARE v_total DECIMAL(10,2);

    SELECT COALESCE(SUM(a.valor_servico), 0) INTO v_total
    FROM agendamentos a
    WHERE a.cliente_id = p_cliente_id AND a.status = 'concluido';

    RETURN v_total;
END$$

DELIMITER ;

-- =====================================================
-- 5. TRIGGERS (Incluindo BEFORE UPDATE para Valores Positivos)
-- =====================================================

DELIMITER $$

-- Trigger 1: Atualização automática de status do agendamento após pagamento
CREATE TRIGGER trg_pagamento_after_insert
AFTER INSERT ON pagamentos
FOR EACH ROW
BEGIN
    UPDATE agendamentos
    SET status = 'concluido'
    WHERE id = NEW.agendamento_id;
END$$

-- Trigger 2: Impede agendamento com barbeiro inativo
CREATE TRIGGER trg_agendamento_before_insert
BEFORE INSERT ON agendamentos
FOR EACH ROW
BEGIN
    DECLARE v_status VARCHAR(10);

    SELECT status INTO v_status FROM barbeiros WHERE id = NEW.barbeiro_id;

    IF v_status IS NULL OR v_status != 'ativo' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é possível agendar com um barbeiro inativo.';
    END IF;
END$$

-- Trigger 3 (BEFORE UPDATE): Protege a integridade histórica de agendamentos e valida alteração de valores positivos
CREATE TRIGGER trg_agendamento_before_update
BEFORE UPDATE ON agendamentos
FOR EACH ROW
BEGIN
    -- Validação do Requisito: Garantir que o valor atualizado é positivo
    IF NEW.valor_servico <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'O valor do serviço deve ser estritamente positivo.';
    END IF;

    -- Trava para não alterar dados de agendamentos concluídos
    IF OLD.status = 'concluido' AND (
        NEW.data_agendamento <> OLD.data_agendamento
        OR NEW.hora_agendamento <> OLD.hora_agendamento
        OR NEW.barbeiro_id <> OLD.barbeiro_id
        OR NEW.servico_id <> OLD.servico_id
        OR NEW.valor_servico <> OLD.valor_servico
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é possível alterar os dados de um agendamento já concluído.';
    END IF;

    IF NEW.status = 'agendado' THEN
        IF (SELECT status FROM barbeiros WHERE id = NEW.barbeiro_id) != 'ativo' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Não é possível reabrir este agendamento: o barbeiro está inativo.';
        END IF;
    END IF;
END$$

-- Trigger 4 (BEFORE UPDATE): Valida que o preço cadastrado na tabela servicos deve ser sempre positivo
CREATE TRIGGER trg_servicos_before_update
BEFORE UPDATE ON servicos
FOR EACH ROW
BEGIN
    IF NEW.preco <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'O preço do serviço deve ser um valor maior que zero.';
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- 6. STORED PROCEDURES (Com Busca, Filtros e Paginação)
-- =====================================================

DELIMITER $$

-- Procedure para Registrar Pagamento
CREATE PROCEDURE sp_registrar_pagamento(
    IN p_agendamento_id INT,
    IN p_forma_pagamento VARCHAR(20)
)
BEGIN
    DECLARE v_valor DECIMAL(10,2);

    SELECT valor_servico INTO v_valor
    FROM agendamentos
    WHERE id = p_agendamento_id;

    IF v_valor IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Agendamento não encontrado.';
    ELSE
        INSERT INTO pagamentos (agendamento_id, valor, forma_pagamento)
        VALUES (p_agendamento_id, v_valor, p_forma_pagamento);
    END IF;
END$$

-- Procedure para Cálculo de Horários Disponíveis com CTE Recursiva
CREATE PROCEDURE sp_horarios_disponiveis(
    IN p_barbeiro_id INT,
    IN p_data DATE
)
BEGIN
    WITH RECURSIVE grade_horarios AS (
        SELECT TIME('09:00:00') AS hora
        UNION ALL
        SELECT ADDTIME(hora, '00:30:00')
        FROM grade_horarios
        WHERE hora < '18:30:00'
    )
    SELECT g.hora
    FROM grade_horarios g
    LEFT JOIN agendamentos a
           ON a.barbeiro_id = p_barbeiro_id
          AND a.data_agendamento = p_data
          AND a.hora_agendamento = g.hora
          AND a.status != 'cancelado'
    WHERE a.id IS NULL
      AND (p_data > CURDATE() OR (p_data = CURDATE() AND g.hora > CURTIME()))
    ORDER BY g.hora;
END$$

-- Procedure para Indicadores Gerais do Dashboard
CREATE PROCEDURE sp_dashboard()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM clientes) AS total_clientes,
        (SELECT COUNT(*) FROM barbeiros WHERE status = 'ativo') AS total_barbeiros,
        (SELECT COUNT(*) FROM servicos) AS total_servicos,
        (SELECT COUNT(*) FROM agendamentos
            WHERE data_agendamento = CURDATE() AND status != 'cancelado') AS agendamentos_hoje,
        (SELECT COALESCE(SUM(valor_servico), 0) FROM agendamentos
            WHERE status = 'concluido'
              AND MONTH(data_agendamento) = MONTH(CURDATE())
              AND YEAR(data_agendamento) = YEAR(CURDATE())) AS faturamento_mes;
END$$

-- Procedure Otimizada para Busca, Filtros e Paginação (Ideal para consumo assíncrono pela API em PHP)
CREATE PROCEDURE sp_dashboard_indicadores_paginado(
    IN p_busca VARCHAR(100),
    IN p_status VARCHAR(20),
    IN p_limite INT,
    IN p_pagina INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_pagina - 1) * p_limite;

    SELECT 
        a.id,
        c.nome AS cliente,
        b.nome AS barbeiro,
        s.nome AS servico,
        a.valor_servico AS preco,
        a.data_agendamento,
        a.hora_agendamento,
        a.status
    FROM agendamentos a
    INNER JOIN clientes c ON c.id = a.cliente_id
    INNER JOIN barbeiros b ON b.id = a.barbeiro_id
    INNER JOIN servicos s ON s.id = a.servico_id
    WHERE (p_busca IS NULL OR p_busca = '' OR (c.nome LIKE CONCAT('%', p_busca, '%') OR b.nome LIKE CONCAT('%', p_busca, '%') OR s.nome LIKE CONCAT('%', p_busca, '%')))
      AND (p_status IS NULL OR p_status = '' OR a.status = p_status)
    ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC
    LIMIT p_limite OFFSET v_offset;
END$$

DELIMITER ;

-- =====================================================
-- 7. DADOS INICIAIS DE TESTE
-- =====================================================

INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@barbearia.com', '$2y$10$1SKKt2i8B4izYthZdJsYyeF0g8pQfxz.TJSUGjRQNEJO/DnMgwo9m', 'admin');

INSERT INTO barbeiros (nome, telefone, email, especialidade, status) VALUES
('João Silva', '(44) 99911-1111', 'joao@barbearia.com', 'Corte degradê e barba', 'ativo'),
('Carlos Souza', '(44) 99922-2222', 'carlos@barbearia.com', 'Cortes clássicos', 'ativo'),
('Carlos Souza', '(44) 99922-2222', 'carlos@barbearia.com', 'Cortes clássicos', 'ativo'),
('Pedro Alves', '(44) 99933-3333', 'pedro@barbearia.com', 'Coloração e design de barba', 'ativo');

INSERT INTO servicos (nome, descricao, preco, duracao_minutos) VALUES
('Corte de Cabelo', 'Corte tradicional na tesoura ou máquina', 35.00, 30),
('Barba', 'Aparar e desenhar a barba com toalha quente', 25.00, 20),
('Corte + Barba', 'Combo completo de corte e barba', 55.00, 50),
('Sobrancelha', 'Design de sobrancelha na navalha', 15.00, 10),
('Coloração', 'Coloração de cabelo ou barba', 45.00, 40);

INSERT INTO clientes (nome, telefone, email, data_nascimento) VALUES
('Lucas Ferreira', '(44) 98811-1111', 'lucas@email.com', '1995-03-12'),
('Rafael Costa', '(44) 98822-2222', 'rafael@email.com', '1990-07-25'),
('Bruno Martins', '(44) 98833-3333', 'bruno@email.com', '1998-11-02');

-- Avaliações iniciais para demonstração do site público
INSERT INTO avaliacoes (nome, nota, comentario, aprovado) VALUES
('Marcos Oliveira', 5, 'Atendimento excelente e corte muito bem feito.', 1),
('Diego Santos', 5, 'Ambiente organizado e barbeiros muito profissionais.', 1),
('Felipe Rocha', 4, 'Gostei bastante do resultado e do atendimento.', 1);
