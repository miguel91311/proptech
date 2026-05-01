"use client";

import { MapPin, BedDouble, Bath, Maximize, Heart } from "lucide-react";
import Link from "next/link";

interface Property {
  id: string;
  title: string;
  address: string;
  price: number;
  image: string;
  beds: number;
  baths: number;
  area: number;
  tag?: string;
}

export default function PropertyCard({ property }: { property: Property }) {
  return (
    <Link href={`/imovel/${property.id}`} className="group bg-white rounded-2xl border border-[var(--color-border)] overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">
      <div className="relative aspect-[4/3] overflow-hidden">
        <img
          src={property.image}
          alt={property.title}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          loading="lazy"
        />
        {property.tag && (
          <span className="absolute top-3 left-3 bg-[var(--color-accent)] text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md">
            {property.tag}
          </span>
        )}
        <button className="absolute top-3 right-3 p-2 rounded-full bg-white/90 hover:bg-white text-[var(--color-text-secondary)] hover:text-red-500 transition-colors shadow-sm">
          <Heart size={16} />
        </button>
      </div>

      <div className="p-4 flex flex-col flex-1">
        <div className="flex items-baseline justify-between gap-2 mb-1">
          <h3 className="text-xl font-bold text-[var(--color-text-primary)] leading-tight">
            {property.price.toLocaleString("pt-PT")} €
          </h3>
          {property.tag === "Novo" && (
            <span className="text-[10px] font-semibold text-[var(--color-brand-dark)] bg-green-50 px-2 py-0.5 rounded-full">
              IMT ISENTO
            </span>
          )}
        </div>

        <p className="text-sm font-medium text-[var(--color-text-primary)] mb-1 line-clamp-1">{property.title}</p>
        <p className="text-xs text-[var(--color-text-secondary)] mb-3 flex items-center gap-1">
          <MapPin size={12} /> {property.address}
        </p>

        <div className="mt-auto flex items-center gap-4 text-xs text-[var(--color-text-secondary)] border-t border-[var(--color-border)] pt-3">
          <span className="flex items-center gap-1"><BedDouble size={14} /> {property.beds}</span>
          <span className="flex items-center gap-1"><Bath size={14} /> {property.baths}</span>
          <span className="flex items-center gap-1"><Maximize size={14} /> {property.area} m²</span>
        </div>
      </div>
    </Link>
  );
}
