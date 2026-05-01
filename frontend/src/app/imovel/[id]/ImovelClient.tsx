"use client";

import { useParams } from "next/navigation";
import { MapPin, BedDouble, Bath, Maximize, Calendar, Home, Share2, Heart, ChevronLeft } from "lucide-react";
import Link from "next/link";

const MOCK_PROPERTIES: Record<string, any> = {
    "1": {
        title: "Apartamento T3 com varanda em Cascais",
        address: "Cascais, Lisboa",
        price: 325000,
        image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1200&h=600&fit=crop",
        gallery: [
            "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&fit=crop",
            "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&fit=crop",
            "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&fit=crop",
        ],
        beds: 3,
        baths: 2,
        area: 110,
        year: 2021,
        condition: "Novo",
        description:
            "Apartamento luminoso com acabamentos de luxo, varanda com vista para o mar, cozinha equipada e estacionamento subterrâneo. Localizado a 5 minutos a pé da praia e do centro de Cascais.",
        features: ["Varanda", "Estacionamento", "Ar Condicionado", "Vidros Duplos", "Painel Solar"],
    },
    "2": {
        title: "Moradia V4 com piscina em Sintra",
        address: "Sintra, Lisboa",
        price: 650000,
        image: "https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&h=600&fit=crop",
        gallery: [
            "https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&fit=crop",
            "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&fit=crop",
        ],
        beds: 4,
        baths: 3,
        area: 240,
        year: 2015,
        condition: "Usado",
        description:
            "Moradia isolada com jardim de 800m², piscina aquecida, barbecue e anexo para hóspedes. Vista deslumbrante para a Serra de Sintra.",
        features: ["Piscina", "Jardim", "Anexo", "Lareira", "Sistema de Segurança"],
    },
};

export default function ImovelClient() {
    const params = useParams();
    const id = params?.id as string;
    const property = MOCK_PROPERTIES[id] || Object.values(MOCK_PROPERTIES)[0];

    return (
        <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <Link
                href="/"
                className="inline-flex items-center gap-1 text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-brand-dark)] mb-4 transition-colors"
            >
                <ChevronLeft size={16} /> Voltar à pesquisa
            </Link>

            <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                <div>
                    <h1 className="font-display text-2xl md:text-3xl font-bold text-[var(--color-text-primary)] mb-1">
                        {property.title}
                    </h1>
                    <p className="flex items-center gap-1 text-[var(--color-text-secondary)] text-sm">
                        <MapPin size={14} /> {property.address}
                    </p>
                </div>
                <div className="flex items-center gap-3">
                    <button className="p-2.5 rounded-full border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-red-500 hover:border-red-200 transition-colors">
                        <Heart size={18} />
                    </button>
                    <button className="p-2.5 rounded-full border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[var(--color-brand-dark)] transition-colors">
                        <Share2 size={18} />
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-3 mb-8 rounded-2xl overflow-hidden">
                <div className="md:col-span-2 aspect-[16/9] md:aspect-auto">
                    <img
                        src={property.gallery[0]}
                        alt={property.title}
                        className="w-full h-full object-cover"
                        loading="eager"
                    />
                </div>
                <div className="hidden md:grid grid-rows-2 gap-3">
                    {property.gallery.slice(1, 3).map((img: string, i: number) => (
                        <div key={i} className="relative aspect-[4/3] overflow-hidden">
                            <img src={img} alt={`${property.title} ${i + 2}`} className="w-full h-full object-cover" loading="lazy" />
                        </div>
                    ))}
                </div>
            </div>

            <div className="grid lg:grid-cols-3 gap-8">
                <div className="lg:col-span-2 space-y-6">
                    <div className="flex flex-wrap gap-6 text-sm text-[var(--color-text-secondary)]">
                        <span className="flex items-center gap-1.5"><BedDouble size={16} /> {property.beds} quartos</span>
                        <span className="flex items-center gap-1.5"><Bath size={16} /> {property.baths} casas de banho</span>
                        <span className="flex items-center gap-1.5"><Maximize size={16} /> {property.area} m²</span>
                        <span className="flex items-center gap-1.5"><Calendar size={16} /> {property.year}</span>
                        <span className="flex items-center gap-1.5"><Home size={16} /> {property.condition}</span>
                    </div>

                    <div>
                        <h2 className="text-lg font-bold text-[var(--color-text-primary)] mb-2">Descrição</h2>
                        <p className="text-[var(--color-text-secondary)] leading-relaxed">{property.description}</p>
                    </div>

                    <div>
                        <h2 className="text-lg font-bold text-[var(--color-text-primary)] mb-3">Características</h2>
                        <div className="flex flex-wrap gap-2">
                            {property.features.map((f: string) => (
                                <span
                                    key={f}
                                    className="px-3 py-1.5 rounded-lg bg-[var(--color-muted)] text-sm font-medium text-[var(--color-text-secondary)]"
                                >
                                    {f}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="lg:col-span-1">
                    <div className="sticky top-24 bg-white rounded-2xl border border-[var(--color-border)] p-6 shadow-lg">
                        <div className="mb-4">
                            <p className="text-sm text-[var(--color-text-secondary)] mb-1">Preço</p>
                            <p className="text-3xl font-bold font-display text-[var(--color-text-primary)]">
                                {property.price.toLocaleString("pt-PT")} €
                            </p>
                        </div>

                        <div className="space-y-3 mb-6">
                            <Link
                                href="/simulador"
                                className="block w-full text-center px-4 py-3 rounded-xl bg-[var(--color-accent)] text-white font-semibold hover:bg-[var(--color-accent-hover)] transition-colors shadow-sm"
                            >
                                Simular Crédito / IMT Jovem
                            </Link>
                            <button className="block w-full text-center px-4 py-3 rounded-xl border border-[var(--color-border)] text-[var(--color-text-primary)] font-semibold hover:bg-[var(--color-muted)] transition-colors">
                                Contactar Agente
                            </button>
                        </div>

                        <div className="border-t border-[var(--color-border)] pt-4 space-y-2 text-xs text-[var(--color-text-secondary)]">
                            <p>Ref: PT-{id?.padStart(6, "0")}</p>
                            <p>Publicado há 2 dias</p>
                            <p>Visitas: 124</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
