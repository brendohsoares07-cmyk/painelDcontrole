"use strict";
const API_URL = "relatorios/api_dashboard.php";
function obterElemento(id) {
    return document.getElementById(id);
}
function formatarMoeda(valor) {
    return valor.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });
}
function calcularRanking(agendamentos) {
    const agrupado = {};
    agendamentos
        .filter((agendamento) => agendamento.status === "concluido")
        .forEach((agendamento) => {
        const nomeServico = agendamento.servico;
        if (!agrupado[nomeServico]) {
            agrupado[nomeServico] = {
                servico: nomeServico,
                total_realizados: 0,
                faturamento_gerado: 0
            };
        }
        agrupado[nomeServico].total_realizados += 1;
        agrupado[nomeServico].faturamento_gerado += agendamento.preco;
    });
    return Object.values(agrupado).sort((a, b) => b.total_realizados - a.total_realizados);
}
function processarDados(agendamentos) {
    // FILTER: separa somente os atendimentos concluídos.
    const agendamentosConcluidos = agendamentos.filter((agendamento) => agendamento.status === "concluido");
    // FILTER: mantém os agendamentos que ainda estão ativos.
    const agendamentosAtivos = agendamentos.filter((agendamento) => agendamento.status === "agendado");
    // REDUCE: calcula o faturamento diretamente do array recebido pelo PHP.
    const faturamentoTotal = agendamentos.reduce((total, agendamento) => total + agendamento.preco, 0);
    // REDUCE: calcula o faturamento somente dos concluídos.
    const faturamentoConcluido = agendamentosConcluidos.reduce((total, agendamento) => total + agendamento.preco, 0);
    const ranking = calcularRanking(agendamentos);
    return {
        faturamentoTotal,
        faturamentoConcluido,
        quantidadeConcluidos: agendamentosConcluidos.length,
        agendamentosAtivos,
        agendamentosConcluidos,
        ranking
    };
}
function renderizarIndicadores(indicadores, resumo) {
    const clientes = obterElemento("ts-total-clientes");
    const barbeiros = obterElemento("ts-total-barbeiros");
    const hoje = obterElemento("ts-agendamentos-hoje");
    const faturamento = obterElemento("ts-faturamento-mes");
    const faturamentoProcessado = obterElemento("ts-faturamento-processado");
    const concluidos = obterElemento("ts-concluidos");
    const destaque = obterElemento("ts-servico-destaque");
    // DOM seguro: cada elemento é validado antes de ser manipulado.
    if (clientes) {
        clientes.textContent = String(indicadores.total_clientes);
    }
    if (barbeiros) {
        barbeiros.textContent = String(indicadores.total_barbeiros);
    }
    if (hoje) {
        hoje.textContent = String(indicadores.agendamentos_hoje);
    }
    if (faturamento) {
        faturamento.textContent = formatarMoeda(indicadores.faturamento_mes);
    }
    if (faturamentoProcessado) {
        faturamentoProcessado.textContent = formatarMoeda(resumo.faturamentoConcluido);
    }
    if (concluidos) {
        concluidos.textContent = String(resumo.quantidadeConcluidos);
    }
    if (destaque) {
        const primeiroRanking = resumo.ranking[0];
        if (primeiroRanking) {
            destaque.textContent = primeiroRanking.servico;
        }
        else {
            destaque.textContent = "Nenhum serviço realizado";
        }
    }
}
function renderizarTabela(agendamentos) {
    const corpoTabela = obterElemento("ts-tabela-agendamentos");
    if (!corpoTabela) {
        return;
    }
    if (agendamentos.length === 0) {
        corpoTabela.innerHTML =
            '<tr><td colspan="5" class="text-center text-muted py-3">' +
                "Nenhum dado registrado para os filtros selecionados." +
                "</td></tr>";
        return;
    }
    // MAP: transforma cada objeto em uma linha HTML.
    corpoTabela.innerHTML = agendamentos
        .slice(0, 8)
        .map((agendamento) => {
        const data = new Date(`${agendamento.data_agendamento}T00:00:00`).toLocaleDateString("pt-BR");
        return `
                <tr>
                    <td>${data}</td>
                    <td>${agendamento.hora_agendamento.substring(0, 5)}</td>
                    <td>${agendamento.cliente}</td>
                    <td>${agendamento.barbeiro}</td>
                    <td>${agendamento.servico}</td>
                </tr>
            `;
    })
        .join("");
}
function renderizarRanking(ranking) {
    const corpoRanking = obterElemento("ts-ranking-servicos");
    if (!corpoRanking) {
        return;
    }
    if (ranking.length === 0) {
        corpoRanking.innerHTML =
            '<tr><td colspan="3" class="text-center text-muted py-3">' +
                "Ainda não há atendimentos concluídos." +
                "</td></tr>";
        return;
    }
    // MAP: prepara o ranking para apresentação na dashboard.
    corpoRanking.innerHTML = ranking
        .slice(0, 5)
        .map((item, indice) => `
                <tr>
                    <td>${indice + 1}º</td>
                    <td>${item.servico}</td>
                    <td>${item.total_realizados}</td>
                    <td>${formatarMoeda(item.faturamento_gerado)}</td>
                </tr>
            `)
        .join("");
}
async function carregarDashboard() {
    const mensagem = obterElemento("ts-mensagem");
    try {
        if (mensagem) {
            mensagem.textContent = "Atualizando indicadores...";
        }
        const resposta = await fetch(API_URL, {
            method: "GET",
            headers: {
                Accept: "application/json"
            }
        });
        if (!resposta.ok) {
            throw new Error(`Falha HTTP: ${resposta.status}`);
        }
        const dados = await resposta.json();
        if (!dados.sucesso) {
            throw new Error(dados.mensagem ?? "A API retornou uma resposta inválida.");
        }
        const resumo = processarDados(dados.agendamentos);
        renderizarIndicadores(dados.indicadores, resumo);
        renderizarTabela(resumo.agendamentosAtivos);
        renderizarRanking(resumo.ranking);
        if (mensagem) {
            mensagem.textContent = `Dados atualizados: ${dados.agendamentos.length} registros processados.`;
        }
    }
    catch (erro) {
        console.error("Erro ao carregar dashboard:", erro);
        if (mensagem) {
            mensagem.textContent =
                "Não foi possível atualizar os dados. Verifique o servidor e o banco.";
        }
        const tabela = obterElemento("ts-tabela-agendamentos");
        if (tabela) {
            tabela.innerHTML =
                '<tr><td colspan="5" class="text-center text-danger py-3">' +
                    "Erro ao carregar os dados da dashboard." +
                    "</td></tr>";
        }
    }
}
document.addEventListener("DOMContentLoaded", () => {
    void carregarDashboard();
});
