-- Atualização do banco para o novo site público da barbearia
-- Execute este arquivo no DBeaver conectado ao banco barbearia.

USE barbearia;

CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    nota TINYINT NOT NULL,
    comentario TEXT NOT NULL,
    aprovado TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_avaliacao_nota CHECK (nota BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE INDEX idx_avaliacoes_aprovado
    ON avaliacoes (aprovado, criado_em);

INSERT INTO avaliacoes (nome, nota, comentario, aprovado)
SELECT 'Marcos Oliveira', 5, 'Atendimento excelente e corte muito bem feito.', 1
WHERE NOT EXISTS (SELECT 1 FROM avaliacoes WHERE nome = 'Marcos Oliveira');

INSERT INTO avaliacoes (nome, nota, comentario, aprovado)
SELECT 'Diego Santos', 5, 'Ambiente organizado e barbeiros muito profissionais.', 1
WHERE NOT EXISTS (SELECT 1 FROM avaliacoes WHERE nome = 'Diego Santos');

INSERT INTO avaliacoes (nome, nota, comentario, aprovado)
SELECT 'Felipe Rocha', 4, 'Gostei bastante do resultado e do atendimento.', 1
WHERE NOT EXISTS (SELECT 1 FROM avaliacoes WHERE nome = 'Felipe Rocha');
