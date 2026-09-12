interface Indicadores {
    total_clientes: number;
    total_barbeiros: number;
    total_servicos: number;
    agendamentos_hoje: number;
    faturamento_mes: number;
}

interface Agendamento {
    id: number;
    cliente: string;
    barbeiro: string;
    servico: string;
    preco: number;
    data_agendamento: string;
    hora_agendamento: string;
    status: "agendado" | "concluido" | "cancelado";
}

interface RankingServico {
    servico: string;
    total_realizados: number;
    faturamento_gerado: number;
}

interface DashboardResponse {
    sucesso: boolean;
    mensagem?: string;
    indicadores: Indicadores;
    agendamentos: Agendamento[];
    ranking_banco: RankingServico[];
}

interface ResumoProcessado {
    faturamentoTotal: number;
    faturamentoConcluido: number;
    quantidadeConcluidos: number;
    agendamentosAtivos: Agendamento[];
    agendamentosConcluidos: Agendamento[];
    ranking: RankingServico[];
}

const API_URL = "relatorios/api_dashboard.php";

function obterElemento<T extends HTMLElement>(id: string): T | null {
    return document.getElementById(id) as T | null;
}

function formatarMoeda(valor: number): string {
    return valor.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });
}

function calcularRanking(agendamentos: Agendamento[]): RankingServico[] {
    const agrupado: Record<string, RankingServico> = {};

    agendamentos
        .filter((agendamento: Agendamento): boolean => agendamento.status === "concluido")
        .forEach((agendamento: Agendamento): void => {
            const nomeServico: string = agendamento.servico;

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

    return Object.values(agrupado).sort(
        (a: RankingServico, b: RankingServico): number =>
            b.total_realizados - a.total_realizados
    );
}

function processarDados(agendamentos: Agendamento[]): ResumoProcessado {
    // FILTER: separa somente os atendimentos concluídos.
    const agendamentosConcluidos: Agendamento[] = agendamentos.filter(
        (agendamento: Agendamento): boolean => agendamento.status === "concluido"
    );

    // FILTER: mantém os agendamentos que ainda estão ativos.
    const agendamentosAtivos: Agendamento[] = agendamentos.filter(
        (agendamento: Agendamento): boolean => agendamento.status === "agendado"
    );

    // REDUCE: calcula o faturamento diretamente do array recebido pelo PHP.
    const faturamentoTotal: number = agendamentos.reduce(
        (total: number, agendamento: Agendamento): number =>
            total + agendamento.preco,
        0
    );

    // REDUCE: calcula o faturamento somente dos concluídos.
    const faturamentoConcluido: number = agendamentosConcluidos.reduce(
        (total: number, agendamento: Agendamento): number =>
            total + agendamento.preco,
        0
    );

    const ranking: RankingServico[] = calcularRanking(agendamentos);

    return {
        faturamentoTotal,
        faturamentoConcluido,
        quantidadeConcluidos: agendamentosConcluidos.length,
        agendamentosAtivos,
        agendamentosConcluidos,
        ranking
    };
}

function renderizarIndicadores(
    indicadores: Indicadores,
    resumo: ResumoProcessado
): void {
    const clientes = obterElemento<HTMLElement>("ts-total-clientes");
    const barbeiros = obterElemento<HTMLElement>("ts-total-barbeiros");
    const hoje = obterElemento<HTMLElement>("ts-agendamentos-hoje");
    const faturamento = obterElemento<HTMLElement>("ts-faturamento-mes");
    const faturamentoProcessado = obterElemento<HTMLElement>("ts-faturamento-processado");
    const concluidos = obterElemento<HTMLElement>("ts-concluidos");
    const destaque = obterElemento<HTMLElement>("ts-servico-destaque");

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
        faturamentoProcessado.textContent = formatarMoeda(
            resumo.faturamentoConcluido
        );
    }

    if (concluidos) {
        concluidos.textContent = String(resumo.quantidadeConcluidos);
    }

  if (destaque) {
    const primeiroRanking = resumo.ranking[0];

    if (primeiroRanking) {
        destaque.textContent = primeiroRanking.servico;
    } else {
        destaque.textContent = "Nenhum serviço realizado";
    }
}
}

function renderizarTabela(agendamentos: Agendamento[]): void {
    const corpoTabela = obterElemento<HTMLTableSectionElement>("ts-tabela-agendamentos");

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
        .map((agendamento: Agendamento): string => {
            const data: string = new Date(
                `${agendamento.data_agendamento}T00:00:00`
            ).toLocaleDateString("pt-BR");

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

function renderizarRanking(ranking: RankingServico[]): void {
    const corpoRanking = obterElemento<HTMLTableSectionElement>("ts-ranking-servicos");

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
        .map(
            (item: RankingServico, indice: number): string => `
                <tr>
                    <td>${indice + 1}º</td>
                    <td>${item.servico}</td>
                    <td>${item.total_realizados}</td>
                    <td>${formatarMoeda(item.faturamento_gerado)}</td>
                </tr>
            `
        )
        .join("");
}

async function carregarDashboard(): Promise<void> {
    const mensagem = obterElemento<HTMLElement>("ts-mensagem");

    try {
        if (mensagem) {
            mensagem.textContent = "Atualizando indicadores...";
        }

        const resposta: Response = await fetch(API_URL, {
            method: "GET",
            headers: {
                Accept: "application/json"
            }
        });

        if (!resposta.ok) {
            throw new Error(`Falha HTTP: ${resposta.status}`);
        }

        const dados: DashboardResponse = await resposta.json();

        if (!dados.sucesso) {
            throw new Error(
                dados.mensagem ?? "A API retornou uma resposta inválida."
            );
        }

        const resumo: ResumoProcessado = processarDados(dados.agendamentos);

        renderizarIndicadores(dados.indicadores, resumo);
        renderizarTabela(resumo.agendamentosAtivos);
        renderizarRanking(resumo.ranking);

        if (mensagem) {
            mensagem.textContent = `Dados atualizados: ${dados.agendamentos.length} registros processados.`;
        }
    } catch (erro: unknown) {
        console.error("Erro ao carregar dashboard:", erro);

        if (mensagem) {
            mensagem.textContent =
                "Não foi possível atualizar os dados. Verifique o servidor e o banco.";
        }

        const tabela = obterElemento<HTMLTableSectionElement>(
            "ts-tabela-agendamentos"
        );

        if (tabela) {
            tabela.innerHTML =
                '<tr><td colspan="5" class="text-center text-danger py-3">' +
                "Erro ao carregar os dados da dashboard." +
                "</td></tr>";
        }
    }
}

document.addEventListener("DOMContentLoaded", (): void => {
    void carregarDashboard();
});
