"use client";

import { useState } from "react";
import { Shield, Check, X } from "lucide-react";

interface ConsentItem {
  key: string;
  title: string;
  description: string;
  required: boolean;
  granted: boolean;
}

export default function PrivacidadePage() {
  const [consents, setConsents] = useState<ConsentItem[]>([
    {
      key: "essential",
      title: "Cookies Essenciais",
      description: "Necessários para o funcionamento do site (login, segurança, mapas). Não podem ser desativados.",
      required: true,
      granted: true,
    },
    {
      key: "analytics",
      title: "Analytics & Performance",
      description: "Ajuda-nos a melhorar o site ao contabilizar visitas e fontes de tráfego.",
      required: false,
      granted: false,
    },
    {
      key: "marketing",
      title: "Marketing & Personalização",
      description: "Permite mostrar anúncios relevantes e partilhar dados com parceiros imobiliários.",
      required: false,
      granted: false,
    },
  ]);

  const toggle = (key: string) => {
    setConsents((prev) =>
      prev.map((c) => (c.key === key && !c.required ? { ...c, granted: !c.granted } : c))
    );
  };

  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div className="flex items-center gap-3 mb-6">
        <Shield className="text-[var(--color-brand)]" size={28} />
        <h1 className="font-display text-2xl md:text-3xl font-bold text-[var(--color-text-primary)]">
          Privacidade & RGPD
        </h1>
      </div>

      <p className="text-[var(--color-text-secondary)] mb-8 leading-relaxed">
        A PropTech Portugal cumpre integralmente o Regulamento Geral de Proteção de Dados (RGPD).
        Abaixo podes gerir os teus consentimentos e exercer os teus direitos.
      </p>

      <div className="space-y-4 mb-10">
        {consents.map((c) => (
          <div
            key={c.key}
            className={`flex items-start justify-between gap-4 p-4 rounded-xl border ${
              c.granted ? "border-[var(--color-brand)] bg-green-50/50" : "border-[var(--color-border)] bg-white"
            }`}
          >
            <div>
              <h3 className="font-semibold text-[var(--color-text-primary)] text-sm">{c.title}</h3>
              <p className="text-xs text-[var(--color-text-secondary)] mt-1">{c.description}</p>
              {c.required && <span className="text-[10px] font-bold text-red-600 uppercase mt-1 inline-block">Obrigatório</span>}
            </div>
            <button
              onClick={() => toggle(c.key)}
              disabled={c.required}
              className={`shrink-0 w-10 h-6 rounded-full relative transition-colors ${
                c.granted ? "bg-[var(--color-brand)]" : "bg-gray-300"
              } ${c.required ? "opacity-60 cursor-not-allowed" : "cursor-pointer"}`}
            >
              <span
                className={`absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform ${
                  c.granted ? "translate-x-4" : "translate-x-0.5"
                }`}
              />
            </button>
          </div>
        ))}
      </div>

      <div className="grid sm:grid-cols-2 gap-4">
        <button className="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-[var(--color-border)] text-sm font-semibold text-[var(--color-text-primary)] hover:bg-[var(--color-muted)] transition-colors">
          <Check size={16} /> Guardar Preferências
        </button>
        <button className="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-red-200 text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
          <X size={16} /> Eliminar a Minha Conta
        </button>
      </div>

      <div className="mt-10 pt-8 border-t border-[var(--color-border)] text-xs text-[var(--color-text-secondary)] space-y-2">
        <p><strong>Direitos do titular:</strong> Acesso, retificação, apagamento, limitação, portabilidade e oposição.</p>
        <p><strong>Contacto DPO:</strong> dpo@proptech.pt</p>
        <p><strong>Última atualização:</strong> Abril 2026</p>
      </div>
    </div>
  );
}
