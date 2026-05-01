"use client";

import { useEffect, useRef } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

interface Property {
  id: string;
  title: string;
  price: number;
  lat: number;
  lng: number;
  image: string;
}

interface MapViewProps {
  properties: Property[];
  onSelect?: (id: string) => void;
}

export default function MapView({ properties, onSelect }: MapViewProps) {
  const mapContainer = useRef<HTMLDivElement>(null);
  const mapRef = useRef<L.Map | null>(null);
  const markersRef = useRef<L.Marker[]>([]);

  useEffect(() => {
    if (!mapContainer.current || mapRef.current) return;

    const map = L.map(mapContainer.current, {
      center: [38.75, -9.15],
      zoom: 12,
      zoomControl: false,
    });

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 19,
    }).addTo(map);

    L.control.zoom({ position: "bottomright" }).addTo(map);

    mapRef.current = map;

    return () => {
      map.remove();
      mapRef.current = null;
    };
  }, []);

  useEffect(() => {
    const map = mapRef.current;
    if (!map) return;

    // Clear existing markers
    markersRef.current.forEach((m) => map.removeLayer(m));
    markersRef.current = [];

    if (properties.length === 0) return;

    const bounds = L.latLngBounds([]);

    properties.forEach((prop) => {
      const icon = L.divIcon({
        className: "custom-marker",
        html: `<div style="
          background:#22C55E;
          color:white;
          font-size:11px;
          font-weight:bold;
          padding:4px 8px;
          border-radius:999px;
          box-shadow:0 2px 6px rgba(0,0,0,0.25);
          white-space:nowrap;
          cursor:pointer;
          border:2px solid white;
        ">${prop.price.toLocaleString("pt-PT")} €</div>`,
        iconSize: [80, 24],
        iconAnchor: [40, 12],
      });

      const marker = L.marker([prop.lat, prop.lng], { icon })
        .addTo(map)
        .on("click", () => onSelect?.(prop.id));

      markersRef.current.push(marker);
      bounds.extend([prop.lat, prop.lng]);
    });

    if (bounds.isValid()) {
      map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
    }
  }, [properties, onSelect]);

  return (
    <div className="w-full h-full min-h-[50vh] md:min-h-0 relative rounded-2xl overflow-hidden border border-[var(--color-border)] shadow-sm">
      <div ref={mapContainer} className="absolute inset-0" />
    </div>
  );
}
