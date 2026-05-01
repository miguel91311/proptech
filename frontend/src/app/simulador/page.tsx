"use client";

import { useState, useMemo } from "react";
import { Calculator, Home, PiggyBank, Info } from "lucide-react";
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from "recharts";

type Tab = "imt" | "credito";

/* ─── Helpers IMT ─── */
function calcularIMTNormal(valor: number) {
  // Simplificação dos escalões IMT 2025 (alíquotas aproximadas para habitação própria)
  if (valor <= 101917) return 0;
  if (valor <= 139412) return (valor - 101917) * 0.02;
  if (valor <= 190086) return (139412 - 101917) * 0.02 + (valor - 139412) * 0.05;
  if (valor <= 316772) return (139412 - 101917) * 0.02 + (190086 - 139412) * 0.05 + (valor - 190086) * 0.07;
  return (
    (139412 - 101917) * 0.02 +
    (190086 - 139412) * 0.05 +
    (316772 - 190086) * 0.07 +
    (valor - 316772) * 0.08
  );
}

function calcularIMTJovem(idade: number, valor: number) {
  if (idade > 35) return { isento: false, valor: calcularIMTNormal(valor), mensagem: "IMT Jovem apenas para compradores até 35 anos." };
  if (valor <= 330539) return { isento: true, valor: 0, mensagem: "Isenção total de IMT! Poupa " + calcularIMTNormal(valor).toLocaleString("pt-PT", { style: "currency", currency: "EUR" }) };
  if (valor <= 660982) {
    const imtNormal = calcularIMTNormal(valor);
    const reduzido = imtNormal * 0.5; // simplificação: 50% isenção na faixa intermediária
    return { isento: false, valor: reduzido, mensagem: "Isenção parcial de 50% aplicada." };
  }
  const imtNormal = calcularIMTNormal(valor);
  return { isento: false, valor: imtNormal, mensagem: "Valor excede 660.982 € — sem benefício IMT Jovem." };
}

/* ─── Helpers Crédito ─── */
function calcularCredito(valorImovel: number, entradaPerc: number, prazoAnos: number, euribor: number, spread: number) {
  const entrada = valorImovel * (entradaPerc / 100);
  const montante = valorImovel - entrada;
  const taxaAnual = (euribor + spread) / 100;
  const n = prazoAnos * 12;
  const taxaMensal = taxaAnual / 12;
  const prestacao = (montante * taxaMensal) / (1 - Math.pow(1 + taxaMensal, -n));
  const totalPago = prestacao * n;
  const totalJuros = totalPago - montante;
  const taeg = taxaAnual * 100;

  // Gerar dados para gráfico de amortização (primeiros 20 anos ou prazo total)
  const chartData = [];
  let capitalRestante = montante;
  const mesesMostrar = Math.min(n, 30 * 12);
  for (let i = 1; i <= mesesMostrar; i++) {
    const jurosMes = capitalRestante * taxaMensal;
    const capitalMes = prestacao - jurosMes;
    capitalRestante -= capitalMes;
    if (i % 12 === 0) {
      chartData.push({
        ano: i / 12,
        capital: Math.round(montante - capitalRestante),
        juros: Math.round(totalJuros - (capitalRestante < 0 ? 0 : capitalRestante * taxaMensal)),
      });
    }
  }

  return { entrada, montante, prestacao, totalPago, totalJuros, taeg, chartData };
}

export default function SimuladorPage() {
  const [tab, setTab] = useState<Tab>("imt");

  /* IMT State */
  const [idade, setIdade] = useState(30);
  const [valorImovelIMT, setValorImovelIMT] = useState(300000);

  /* Crédito State */
  const [valorImovelCred, setValorImovelCred] = useState(300000);
  const [entradaPerc, setEntradaPerc] = useState(20);
  const [prazo, setPrazo] = useState(30);
  const [euribor, setEuribor] = useState(3.5);
  const [spread, setSpread] = useState(1.2);

  const resultadoIMT = useMemo(() => calcularIMTJovem(idade, valorImovelIMT), [idade, valorImovelIMT]);
  const resultadoCred = useMemo(
    () => calcularCredito(valorImovelCred, entradaPerc, prazo, euribor, spread),
    [valorImovelCred, entradaPerc, prazo, euribor, spread]
  );

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="mb-8 text-center">
        <h1 className="font-display text-3xl md:text-4xl font-bold text-[var(--color-text-primary)] mb-2">
          Simulador Financeiro
        </h1>
        <p className="text-[var(--color-text-secondary)] text-sm md:text-base max-w-2xl mx-auto">
          Calcula o teu IMT Jovem e a prestação mensal do crédito à habitação em segundos.
          Dados atualizados com as regras fiscais de 2025/2026.
        </p>
      </div>

      {/* Tabs */}
      <div className="flex justify-center mb-8">
        <div className="inline-flex bg-[var(--color-muted)] rounded-full p-1">
          <button
            onClick={() => setTab("imt")}
            className={`flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-semibold transition-all ${
              tab === "imt"
                ? "bg-white text-[var(--color-brand-dark)] shadow-sm"
                : "text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]"
            }`}
          >
            <Home size={16} /> IMT Jovem
          </button>
          <button
            onClick={() => setTab("credito")}
            className={`flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-semibold transition-all ${
              tab === "credito"
                ? "bg-white text-[var(--color-brand-dark)] shadow-sm"
                : "text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]"
            }`}
          >
            <PiggyBank size={16} /> Crédito Habitação
          </button>
        </div>
      </div>

      {tab === "imt" && (
        <div className="grid md:grid-cols-2 gap-8 items-start">
          <div className="bg-white rounded-2xl border border-[var(--color-border)] p-6 shadow-sm">
            <h2 className="text-lg font-bold text-[var(--color-text-primary)] mb-4 flex items-center gap-2">
              <Calculator size={20} className="text-[var(--color-brand)]" />
              Dados do Comprador
            </h2>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-1">Idade do comprador</label>
                <input
                  type="number"
                  value={idade}
                  onChange={(e) => setIdade(Number(e.target.value))}
                  className="w-full px-4 py-2.5 rounded-xl border border-[var(--color-border)] focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)] transition-shadow"
                />
                {idade > 35 && (
                  <p className="text-xs text-red-600 mt-1">O IMT Jovem aplica-se apenas a compradores até 35 anos.</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-1">Valor do imóvel (€)</label>
                <input
                  type="range"
                  min={100000}
                  max={900000}
                  step={5000}
                  value={valorImovelIMT}
                  onChange={(e) => setValorImovelIMT(Number(e.target.value))}
                  className="w-full accent-[var(--color-brand)]"
                />
                <div className="text-center font-semibold text-[var(--color-text-primary)] mt-1">
                  {valorImovelIMT.toLocaleString("pt-PT")} €
                </div>
              </div>
            </div>

            <div className="mt-6 bg-[var(--color-muted)] rounded-xl p-4 text-xs text-[var(--color-text-secondary)] flex items-start gap-2">
              <Info size={16} className="shrink-0 mt-0.5" />
              <p>
                Regras IMT Jovem 2025/2026: Isenção total até 330.539 €, isenção parcial até 660.982 €. 
                Acima deste valor, não há benefício fiscal para jovens.
              </p>
            </div>
          </div>

          <div className="bg-white rounded-2xl border border-[var(--color-border)] p-6 shadow-sm">
            <h2 className="text-lg font-bold text-[var(--color-text-primary)] mb-4">Resultado</h2>
            <div className="text-center py-6">
              <p className="text-sm text-[var(--color-text-secondary)] mb-1">IMT a pagar</p>
              <p
                className={`text-4xl font-bold font-display ${
                  resultadoIMT.isento ? "text-[var(--color-brand)]" : "text-[var(--color-text-primary)]"
                }`}
              >
                {resultadoIMT.valor.toLocaleString("pt-PT", { style: "currency", currency: "EUR" })}
              </p>
              {resultadoIMT.isento && (
                <span className="inline-block mt-2 bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                  Isento
                </span>
              )}
            </div>
            <div className="border-t border-[var(--color-border)] pt-4 space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Valor do imóvel</span>
                <span className="font-medium">{valorImovelIMT.toLocaleString("pt-PT", { style: "currency", currency: "EUR" })}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">IMT normal (sem benefício)</span>
                <span className="font-medium">{calcularIMTNormal(valorImovelIMT).toLocaleString("pt-PT", { style: "currency", currency: "EUR" })}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Poupança</span>
                <span className="font-bold text-[var(--color-brand)]">
                  {(calcularIMTNormal(valorImovelIMT) - resultadoIMT.valor).toLocaleString("pt-PT", { style: "currency", currency: "EUR" })}
                </span>
              </div>
            </div>
            <p className="mt-4 text-xs text-[var(--color-text-secondary)]">{resultadoIMT.mensagem}</p>
          </div>
        </div>
      )}

      {tab === "credito" && (
        <div className="grid lg:grid-cols-2 gap-8 items-start">
          <div className="bg-white rounded-2xl border border-[var(--color-border)] p-6 shadow-sm space-y-5">
            <h2 className="text-lg font-bold text-[var(--color-text-primary)] mb-2 flex items-center gap-2">
              <PiggyBank size={20} className="text-[var(--color-brand)]" />
              Dados do Empréstimo
            </h2>

            <div>
              <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-1">Valor do imóvel</label>
              <input
                type="number"
                value={valorImovelCred}
                onChange={(e) => setValorImovelCred(Number(e.target.value))}
                className="w-full px-4 py-2.5 rounded-xl border border-[var(--color-border)] focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-1">Entrada ({entradaPerc}%)</label>
              <input
                type="range"
                min={10}
                max={100}
                value={entradaPerc}
                onChange={(e) => setEntradaPerc(Number(e.target.value))}
                className="w-full accent-[var(--color-brand)]"
              />
              <div className="text-right text-xs text-[var(--color-text-secondary)] mt-1">
                {resultadoCred.entrada.toLocaleString("pt-PT", { style: "currency", currency: "EUR" })}
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-1">Prazo (anos)</label>
                <input
                  type="range"
                  min={5}
                  max={40}
                  value={prazo}
                  onChange={(e) => setPrazo(Number(e.target.value))}
                  className="w-full accent-[var(--color-brand)]"
                />
                <div className="text-center text-xs font-semibold">{prazo} anos</div>
              </div>
              <div>
                <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-1">Euribor (%)</label>
                <input
                  type="number"
                  step={0.01}
                  value={euribor}
                  onChange={(e) => setEuribor(Number(e.target.value))}
                  className="w-full px-3 py-2 rounded-xl border border-[var(--color-border)] focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-1">Spread bancário (%)</label>
              <input
                type="number"
                step={0.05}
                value={spread}
                onChange={(e) => setSpread(Number(e.target.value))}
                className="w-full px-4 py-2.5 rounded-xl border border-[var(--color-border)] focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]"
              />
            </div>

            <div className="bg-[var(--color-muted)] rounded-xl p-4 text-xs text-[var(--color-text-secondary)] flex items-start gap-2">
              <Info size={16} className="shrink-0 mt-0.5" />
              <p>TAEG estimada inclui taxa de juro (Euribor + Spread). Não inclui comissões de processamento ou avaliação bancária.</p>
            </div>
          </div>

          <div className="bg-white rounded-2xl border border-[var(--color-border)] p-6 shadow-sm">
            <h2 className="text-lg font-bold text-[var(--color-text-primary)] mb-4">Resultado</h2>

            <div className="grid grid-cols-2 gap-4 mb-6">
              <div className="bg-[var(--color-muted)] rounded-xl p-4 text-center">
                <p className="text-xs text-[var(--color-text-secondary)] mb-1">Prestação mensal</p>
                <p className="text-2xl font-bold text-[var(--color-text-primary)]">
                  {Math.round(resultadoCred.prestacao).toLocaleString("pt-PT")} €
                </p>
              </div>
              <div className="bg-[var(--color-muted)] rounded-xl p-4 text-center">
                <p className="text-xs text-[var(--color-text-secondary)] mb-1">TAEG</p>
                <p className="text-2xl font-bold text-[var(--color-brand-dark)]">
                  {(euribor + spread).toFixed(2)}%
                </p>
              </div>
            </div>

            <div className="space-y-2 text-sm mb-6">
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Montante financiado</span>
                <span className="font-medium">{resultadoCred.montante.toLocaleString("pt-PT", { style: "currency", currency: "EUR" })}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Total pago em juros</span>
                <span className="font-medium">{Math.round(resultadoCred.totalJuros).toLocaleString("pt-PT", { style: "currency", currency: "EUR" })}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-[var(--color-text-secondary)]">Custo total do crédito</span>
                <span className="font-bold">{Math.round(resultadoCred.totalPago).toLocaleString("pt-PT", { style: "currency", currency: "EUR" })}</span>
              </div>
            </div>

            <div className="h-64">
              <h3 className="text-xs font-semibold text-[var(--color-text-secondary)] mb-2 uppercase tracking-wide">Evolução da Dívida</h3>
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={resultadoCred.chartData}>
                  <defs>
                    <linearGradient id="colorCapital" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#22C55E" stopOpacity={0.2} />
                      <stop offset="95%" stopColor="#22C55E" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke="#E5E7EB" />
                  <XAxis dataKey="ano" tick={{ fontSize: 12 }} />
                  <YAxis tick={{ fontSize: 12 }} tickFormatter={(v) => `${(v / 1000).toFixed(0)}k`} />
                  <Tooltip formatter={(value: any) => (value ? Number(value).toLocaleString("pt-PT") + " €" : "")} />
                  <Area type="monotone" dataKey="capital" stroke="#22C55E" fillOpacity={1} fill="url(#colorCapital)" strokeWidth={2} />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
