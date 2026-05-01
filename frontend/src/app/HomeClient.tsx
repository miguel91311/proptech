"use client";

import { useEffect, useState } from "react";
import dynamic from "next/dynamic";
import PropertyCard from "@/components/PropertyCard";
import SearchBar from "@/components/SearchBar";
import { fetchProperties, PropertyApi } from "@/lib/api";
import { useSearchParams } from "next/navigation";

const MapView = dynamic(() => import("@/components/MapView"), { ssr: false });

export default function HomeClient() {
    const searchParams = useSearchParams();
    const [properties, setProperties] = useState<PropertyApi[]>([]);
    const [loading, setLoading] = useState(true);

    const filters: Record<string, string> = {};
    const city = searchParams.get("city");
    const minPrice = searchParams.get("minPrice");
    const maxPrice = searchParams.get("maxPrice");
    const minBeds = searchParams.get("minBeds");
    const propertyType = searchParams.get("propertyType");
    const sortBy = searchParams.get("sortBy") || "newest";

    if (city) filters.city = city;
    if (minPrice) filters.minPrice = minPrice;
    if (maxPrice) filters.maxPrice = maxPrice;
    if (minBeds) filters.minBeds = minBeds;
    if (propertyType) filters.propertyType = propertyType;

    useEffect(() => {
        let cancelled = false;
        fetchProperties(filters).then((data) => {
            if (!cancelled) {
                setProperties(data);
                setLoading(false);
            }
        });
        return () => { cancelled = true; };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [city, minPrice, maxPrice, minBeds, propertyType]);

    const sorted = [...properties];
    if (sortBy === "price_asc") {
        sorted.sort((a, b) => (a.ListPrice || 0) - (b.ListPrice || 0));
    } else if (sortBy === "price_desc") {
        sorted.sort((a, b) => (b.ListPrice || 0) - (a.ListPrice || 0));
    } else if (sortBy === "area_desc") {
        sorted.sort((a, b) => (b.LivingArea || 0) - (a.LivingArea || 0));
    }

    const mapProps = sorted
        .filter((p): p is PropertyApi & { Latitude: number; Longitude: number } =>
            typeof p.Latitude === "number" && typeof p.Longitude === "number"
        )
        .map((p) => ({
            id: p.ListingKey,
            title: p.StreetAddress || p.PropertySubType || "Imóvel",
            price: p.ListPrice || 0,
            lat: p.Latitude,
            lng: p.Longitude,
            image: "",
        }));

    const hasFilters = Object.keys(filters).length > 0;

    return (
        <div className="flex flex-col h-[calc(100vh-4rem)]">
            <SearchBar />

            {hasFilters && (
                <div className="bg-[var(--color-muted)] px-4 py-2 flex items-center gap-2 text-xs text-[var(--color-text-secondary)]">
                    <span className="font-medium">Filtros ativos:</span>
                    {city && <span className="bg-white px-2 py-0.5 rounded-full border border-[var(--color-border)]">{city}</span>}
                    {minPrice && <span className="bg-white px-2 py-0.5 rounded-full border border-[var(--color-border)]">Min €{minPrice}</span>}
                    {maxPrice && <span className="bg-white px-2 py-0.5 rounded-full border border-[var(--color-border)]">Max €{maxPrice}</span>}
                    {minBeds && <span className="bg-white px-2 py-0.5 rounded-full border border-[var(--color-border)]">{minBeds}+ quartos</span>}
                    {propertyType && <span className="bg-white px-2 py-0.5 rounded-full border border-[var(--color-border)]">{propertyType}</span>}
                </div>
            )}

            <div className="flex-1 flex flex-col md:flex-row overflow-hidden">
                <div className="w-full md:w-[55%] lg:w-[60%] h-[50vh] md:h-auto relative">
                    <MapView properties={mapProps} />
                </div>

                <div className="w-full md:w-[45%] lg:w-[40%] bg-[var(--color-background)] overflow-y-auto p-4">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-sm font-semibold text-[var(--color-text-primary)]">
                            {sorted.length} imóveis encontrados
                        </h2>
                    </div>

                    {loading ? (
                        <div className="text-center py-12 text-[var(--color-text-secondary)]">
                            <p className="text-sm">A carregar imóveis...</p>
                        </div>
                    ) : (
                        <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4">
                                {sorted.map((p) => (
                                    <PropertyCard
                                        key={p.ListingKey}
                                        property={{
                                            id: p.ListingKey,
                                            title: `${p.PropertySubType || p.PropertyType} em ${p.City || "Portugal"}`,
                                            address: `${p.StreetAddress || ""}, ${p.City || ""}`.replace(/,\s*$/, ""),
                                            price: p.ListPrice || 0,
                                            image: `https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600&h=400&fit=crop`,
                                            beds: p.BedroomsTotal || 0,
                                            baths: p.BathroomsTotalInteger || 0,
                                            area: p.LivingArea || 0,
                                            tag: p.PropertySubType || undefined,
                                        }}
                                    />
                                ))}
                            </div>

                            {sorted.length === 0 && (
                                <div className="text-center py-12 text-[var(--color-text-secondary)]">
                                    <p className="text-sm">Nenhum imóvel encontrado.</p>
                                    <p className="text-xs mt-1">Verifica se o backend está a correr em localhost:3000</p>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
