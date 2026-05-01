import type { MetadataRoute } from "next";

export const dynamic = "force-static";

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: "PropTech Portugal",
    short_name: "PropTech PT",
    description: "Portal imobiliário inteligente com mapas interativos e simulação financeira.",
    start_url: "/",
    display: "standalone",
    background_color: "#FAFAF8",
    theme_color: "#22C55E",
    icons: [
      { src: "/icon-192x192.svg", sizes: "192x192", type: "image/svg+xml" },
      { src: "/icon-512x512.svg", sizes: "512x512", type: "image/svg+xml" },
    ],
  };
}
