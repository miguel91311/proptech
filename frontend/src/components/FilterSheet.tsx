"use client";

import { useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { SlidersHorizontal, X } from "lucide-react";

export default function FilterSheet() {
  const router = useRouter();
  const params = useSearchParams();
  const [open, setOpen] = useState(false);

  const [minPrice, setMinPrice] = useState(params.get("minPrice") || "0");
  const [maxPrice, setMaxPrice] = useState(params.get("maxPrice") || "2000000");
  const [minBeds, setMinBeds] = useState(params.get("minBeds") || "1");
  const [propertyType, setPropertyType] = useState(params.get("propertyType") || "");
  const [sortBy, setSortBy] = useState(params.get("sortBy") || "newest");

  const apply = () => {
    const sp = new URLSearchParams(params.toString());
    if (minPrice && minPrice !== "0") sp.set("minPrice", minPrice);
    else sp.delete("minPrice");
    if (maxPrice && maxPrice !== "2000000") sp.set("maxPrice", maxPrice);
    else sp.delete("maxPrice");
    if (minBeds && minBeds !== "1") sp.set("minBeds", minBeds);
    else sp.delete("minBeds");
    if (propertyType) sp.set("propertyType", propertyType);
    else sp.delete("propertyType");
    if (sortBy) sp.set("sortBy", sortBy);
    else sp.delete("sortBy");
    router.push(`/?${sp.toString()}`);
    setOpen(false);
  };

  const clear = () => {
    setMinPrice("0");
    setMaxPrice("2000000");
    setMinBeds("1");
    setPropertyType("");
    setSortBy("newest");
    router.push("/");
    setOpen(false);
  };

  return (
    <>
      <button
        onClick={() => setOpen(true)}
        className="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[var(--color-border)] text-sm font-medium text-[var(--color-text-secondary)] hover:bg-[var(--color-muted)] transition-colors"
      >
        <SlidersHorizontal size={16} />
        <span className="hidden sm:inline">Filtros</span>
      </button>

      {open && (
        <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-sm" onClick={() => setOpen(false)}>
          <div
            className="bg-white w-full max-w-lg sm:rounded-2xl rounded-t-2xl p-6 shadow-2xl max-h-[85vh] overflow-y-auto"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-bold text-[var(--color-text-primary)]">Filtros</h2>
              <button onClick={() => setOpen(false)} className="p-2 rounded-full hover:bg-[var(--color-muted)] transition-colors">
                <X size={18} />
              </button>
            </div>

            {/* Preço */}
            <div className="mb-6">
              <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-3">Preço (€)</label>
              <div className="flex items-center gap-3">
                <div className="flex-1">
                  <input
                    type="number"
                    value={minPrice}
                    onChange={(e) => setMinPrice(e.target.value)}
                    placeholder="Mín"
                    className="w-full px-3 py-2 rounded-xl border border-[var(--color-border)] text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]"
                  />
                </div>
                <span className="text-[var(--color-text-secondary)]">—</span>
                <div className="flex-1">
                  <input
                    type="number"
                    value={maxPrice}
                    onChange={(e) => setMaxPrice(e.target.value)}
                    placeholder="Máx"
                    className="w-full px-3 py-2 rounded-xl border border-[var(--color-border)] text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]"
                  />
                </div>
              </div>
              <input
                type="range"
                min={0}
                max={2000000}
                step={50000}
                value={maxPrice}
                onChange={(e) => setMaxPrice(e.target.value)}
                className="w-full mt-3 accent-[var(--color-brand)]"
              />
            </div>

            {/* Tipologia */}
            <div className="mb-6">
              <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-3">Tipologia mínima</label>
              <div className="flex flex-wrap gap-2">
                {["T0", "T1", "T2", "T3", "T4", "T5+"].map((t) => (
                  <button
                    key={t}
                    onClick={() => setMinBeds(t === "T0" ? "0" : t === "T5+" ? "5" : t.replace("T", ""))}
                    className={`px-4 py-2 rounded-xl text-sm font-medium border transition-colors ${
                      minBeds === (t === "T0" ? "0" : t === "T5+" ? "5" : t.replace("T", ""))
                        ? "bg-[var(--color-brand)] text-white border-[var(--color-brand)]"
                        : "bg-white text-[var(--color-text-secondary)] border-[var(--color-border)] hover:bg-[var(--color-muted)]"
                    }`}
                  >
                    {t}
                  </button>
                ))}
              </div>
            </div>

            {/* Tipo de imóvel */}
            <div className="mb-6">
              <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-3">Tipo de imóvel</label>
              <div className="flex flex-wrap gap-2">
                {[
                  { label: "Todos", value: "" },
                  { label: "Apartamento", value: "Apartamento" },
                  { label: "Moradia", value: "Moradia" },
                  { label: "Estúdio", value: "Estúdio" },
                  { label: "Quinta", value: "Quinta" },
                ].map((opt) => (
                  <button
                    key={opt.value}
                    onClick={() => setPropertyType(opt.value)}
                    className={`px-4 py-2 rounded-xl text-sm font-medium border transition-colors ${
                      propertyType === opt.value
                        ? "bg-[var(--color-brand)] text-white border-[var(--color-brand)]"
                        : "bg-white text-[var(--color-text-secondary)] border-[var(--color-border)] hover:bg-[var(--color-muted)]"
                    }`}
                  >
                    {opt.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Ordenar */}
            <div className="mb-8">
              <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-3">Ordenar por</label>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="w-full px-3 py-2.5 rounded-xl border border-[var(--color-border)] text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)]"
              >
                <option value="newest">Mais recentes</option>
                <option value="price_asc">Preço: baixo → alto</option>
                <option value="price_desc">Preço: alto → baixo</option>
                <option value="area_desc">Área: maior → menor</option>
              </select>
            </div>

            {/* Actions */}
            <div className="flex gap-3">
              <button
                onClick={clear}
                className="flex-1 px-4 py-3 rounded-xl border border-[var(--color-border)] text-sm font-semibold text-[var(--color-text-secondary)] hover:bg-[var(--color-muted)] transition-colors"
              >
                Limpar
              </button>
              <button
                onClick={apply}
                className="flex-1 px-4 py-3 rounded-xl bg-[var(--color-accent)] text-white text-sm font-semibold hover:bg-[var(--color-accent-hover)] transition-colors shadow-sm"
              >
                Mostrar resultados
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
