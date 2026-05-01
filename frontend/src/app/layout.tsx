import type { Metadata } from "next";
import { Inter, Playfair_Display } from "next/font/google";
import "./globals.css";
import ServiceWorkerRegister from "@/components/ServiceWorkerRegister";

const inter = Inter({ subsets: ["latin"], variable: "--font-inter" });
const playfair = Playfair_Display({ subsets: ["latin"], variable: "--font-playfair" });

export const metadata: Metadata = {
  title: "PropTech Portugal — Imobiliário Inteligente",
  description: "Descubra, simule e transacione imóveis em Portugal com mapas interativos, simulação IMT Jovem e crédito habitação.",
  keywords: ["imobiliário", "Portugal", "comprar casa", "IMT Jovem", "crédito habitação", "mapa imóveis"],
  authors: [{ name: "PropTech Portugal" }],
  openGraph: {
    title: "PropTech Portugal — Imobiliário Inteligente",
    description: "Mapas interativos, simulação financeira e imóveis em Portugal.",
    type: "website",
    locale: "pt_PT",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="pt">
      <body className={`${inter.variable} ${playfair.variable} antialiased min-h-screen flex flex-col`}>
        <ServiceWorkerRegister />
        <header className="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-[var(--color-border)]">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-lg bg-[var(--color-brand)] flex items-center justify-center">
                <span className="text-white font-bold text-sm">PT</span>
              </div>
              <span className="font-display text-xl font-bold text-[var(--color-text-primary)]">PropTech</span>
            </a>
            <nav className="hidden md:flex items-center gap-8 text-sm font-medium text-[var(--color-text-secondary)]">
              <a href="/" className="hover:text-[var(--color-brand-dark)] transition-colors">Descobrir</a>
              <a href="/simulador" className="hover:text-[var(--color-brand-dark)] transition-colors">Simulador</a>
              <a href="/imovel/demo" className="hover:text-[var(--color-brand-dark)] transition-colors">Destaques</a>
            </nav>
            <a
              href="/simulador"
              className="hidden sm:inline-flex items-center justify-center px-4 py-2 rounded-full bg-[var(--color-accent)] text-white text-sm font-semibold hover:bg-[var(--color-accent-hover)] transition-colors shadow-sm"
            >
              Simular IMT Jovem
            </a>
          </div>
        </header>

        <main className="flex-1">{children}</main>

        <footer className="bg-[var(--color-text-primary)] text-white py-12">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
              <h3 className="font-display text-lg font-bold mb-2">PropTech Portugal</h3>
              <p className="text-sm text-gray-400">A nova geração de descoberta imobiliária com mapas inteligentes, simulação financeira e segurança forense.</p>
            </div>
            <div>
              <h4 className="font-semibold mb-2">Funcionalidades</h4>
              <ul className="text-sm text-gray-400 space-y-1">
                <li>Mapa Interativo Map-First</li>
                <li>Simulador IMT Jovem</li>
                <li>Crédito Habitação em Tempo Real</li>
                <li>Auditoria Forense Blockchain-ready</li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-2">Legal</h4>
              <ul className="text-sm text-gray-400 space-y-1">
                <li><a href="/privacidade" className="hover:text-white transition-colors">RGPD / Privacidade</a></li>
                <li>Termos de Utilização</li>
                <li>Certidões Digitais</li>
              </ul>
            </div>
          </div>
          <script
            type="application/ld+json"
            dangerouslySetInnerHTML={{
              __html: JSON.stringify({
                "@context": "https://schema.org",
                "@type": "RealEstateAgent",
                name: "PropTech Portugal",
                url: "https://proptech.pt",
                logo: "https://proptech.pt/logo.png",
                description: "Portal imobiliário inteligente em Portugal com mapas interativos e simulação financeira.",
                areaServed: { "@type": "Country", name: "Portugal" },
              }),
            }}
          />
        </footer>
      </body>
    </html>
  );
}
