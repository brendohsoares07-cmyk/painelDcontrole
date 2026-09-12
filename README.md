# Sistema de Gerenciamento de Barbearia

Projeto acadêmico em **PHP + MySQL + Bootstrap 5**, com login, cadastros (CRUD completo),
agenda com verificação de horários disponíveis, relatório financeiro e dashboard.

## Requisitos

- PHP 7.4 ou superior (com extensão PDO MySQL habilitada)
- MySQL ou MariaDB
- Servidor Apache (recomenda-se o **XAMPP** para facilitar)

## Instalação (passo a passo)

1. Copie a pasta `barbearia` inteira para dentro de `htdocs` (no XAMPP) ou da raiz do seu servidor.
   - Exemplo (Windows/XAMPP): `C:\xampp\htdocs\barbearia`
   - Exemplo (Linux): `/var/www/html/barbearia`

2. Inicie o **Apache** e o **MySQL** pelo painel do XAMPP.

3. Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`), clique em **Importar** e selecione
   o arquivo `database.sql` que está na raiz do projeto. Isso vai criar o banco `barbearia`,
   todas as tabelas e alguns dados de exemplo (clientes, barbeiros e serviços).

4. Confira as credenciais de conexão em `config/database.php`. Por padrão está configurado
   para o ambiente padrão do XAMPP (`usuário: root`, `senha: vazia`). Ajuste se necessário.

5. Se a pasta do projeto tiver outro nome (diferente de `barbearia`), atualize a constante
   `BASE_URL` em `config/config.php`.

6. Acesse no navegador: `http://localhost/barbearia/`

## Login de teste

- **E-mail:** admin@barbearia.com
- **Senha:** admin123

## Estrutura do projeto

```
barbearia/
├── config/
│   ├── config.php          -> sessão e constantes globais
│   └── database.php        -> conexão PDO com o MySQL
├── includes/
│   ├── auth.php             -> protege páginas que exigem login
│   ├── header.php           -> template: cabeçalho/navbar (Bootstrap)
│   ├── sidebar.php          -> template: menu lateral
│   └── footer.php           -> template: rodapé/scripts
├── assets/
│   └── css/style.css        -> estilos personalizados
├── clientes/                -> CRUD de clientes
│   ├── listar.php
│   ├── form.php
│   ├── salvar.php
│   └── excluir.php
├── barbeiros/                -> CRUD de barbeiros
│   ├── listar.php
│   ├── form.php
│   ├── salvar.php
│   └── excluir.php
├── servicos/                 -> CRUD de serviços
│   ├── listar.php
│   ├── form.php
│   ├── salvar.php
│   └── excluir.php
├── agenda/                   -> Agenda e horários disponíveis
│   ├── listar.php
│   ├── form.php
│   ├── horarios_disponiveis.php  -> endpoint AJAX (JSON)
│   ├── salvar.php
│   ├── status.php
│   └── excluir.php
├── relatorios/
│   └── financeiro.php        -> relatório financeiro por período
├── login.php
├── logout.php
├── dashboard.php
├── index.php
└── database.sql
```

## Funcionalidades

- **Login** com sessão PHP e senha protegida com `password_hash`/`password_verify`.
- **CRUD de Clientes, Barbeiros e Serviços** — inclusão, listagem/busca, edição e exclusão,
  todos com mensagens de sucesso/erro claras.
- **Regras de exclusão**: ao tentar excluir um cliente, barbeiro ou serviço que possui
  agendamentos vinculados, o banco bloqueia a exclusão (chave estrangeira `RESTRICT`) e o
  sistema captura o erro e exibe uma mensagem amigável explicando o motivo.
- **Agenda**: cadastro de agendamentos com verificação de horários disponíveis em tempo real
  via AJAX (evita conflito de horário para o mesmo barbeiro).
- **Relatório financeiro**: faturamento total, ticket médio e faturamento por barbeiro,
  filtrável por período.
- **Dashboard**: indicadores gerais (clientes, barbeiros ativos, agendamentos do dia,
  faturamento do mês) e lista dos próximos agendamentos.

## Recursos avançados de banco de dados

O `database.sql` também inclui, além das tabelas:

- **Coluna histórica**: `agendamentos.valor_servico` guarda o preço do serviço
  no momento exato do agendamento. Se o preço do serviço for reajustado depois,
  os relatórios antigos continuam corretos (mostram o valor realmente cobrado
  na época, não o preço atual).
- **Índices**: em `clientes.nome`, `barbeiros.nome`/`status`, `servicos.nome`,
  `agendamentos.data_agendamento`/`status`, índice composto
  `agendamentos(barbeiro_id, data_agendamento)` e `pagamentos.agendamento_id`.
- **Views (3)**: `vw_dashboard` (agendamentos já unidos com cliente/barbeiro/serviço,
  usada no Dashboard e no Relatório Financeiro), `vw_faturamento` (faturamento
  agrupado por barbeiro) e `vw_ranking_servicos` (ranking dos serviços mais
  realizados, usada no Relatório Financeiro).
- **Functions**: `fn_calcular_idade(data_nascimento)` e
  `fn_valor_total_cliente(cliente_id)` — usadas na listagem de clientes
  (`clientes/listar.php`), mostrando idade e total já gasto por cada cliente.
- **Triggers**: `trg_pagamento_after_insert` (AFTER INSERT — ao registrar um
  pagamento, marca o agendamento como concluído automaticamente),
  `trg_agendamento_before_insert` (BEFORE INSERT — impede agendar com um
  barbeiro inativo) e `trg_agendamento_before_update` (BEFORE UPDATE — depois
  que um agendamento é concluído, bloqueia alteração de data/hora/barbeiro/
  serviço/valor, protegendo o histórico financeiro; também impede reabrir um
  agendamento vinculado a um barbeiro inativo).
- **Stored procedures**: `sp_registrar_pagamento` (usada em `agenda/pagamento.php`
  para registrar o pagamento e concluir o agendamento), `sp_horarios_disponiveis`
  (usada em `agenda/horarios_disponiveis.php`, gera a grade de horários com uma
  **CTE recursiva** e já exclui os ocupados), `sp_relatorio_periodo` (usada em
  `relatorios/financeiro.php` para o resumo do período) e `sp_dashboard`
  (usada em `dashboard.php`, concentra todos os indicadores do painel em uma
  única chamada em vez de 5 consultas separadas).

## Tecnologias

- PHP puro (PDO + prepared statements, sem frameworks)
- MySQL (views, functions, triggers, stored procedures, CTE recursiva)
- Bootstrap 5 (CDN) + Bootstrap Icons
- JavaScript (fetch/AJAX) para carregamento dinâmico de horários

## Site público

O projeto agora possui uma área pública integrada ao painel:

- `index.php`: página inicial com serviços, destaques, galeria e avaliações.
- `galeria.php`: galeria de fotos do ambiente e dos cortes.
- `agendar.php`: agendamento público com barbeiro, serviço, data e horário.
- `pagamento_publico.php`: demonstração de pagamento por Pix e cartão sem armazenar dados do cartão.
- `avaliacoes.php`: cadastro e listagem de avaliações dos clientes.
- `api/horarios.php`: endpoint JSON para horários disponíveis.

> Os pagamentos por Pix/cartão são simulados para fins acadêmicos. Para cobrança real, deve ser usado um gateway de pagamento apropriado.
