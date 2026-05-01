import type { MetadataRoute } from "next";

export const dynamic = "force-static";

export default function sitemap(): MetadataRoute.Sitemap {
  return [
    { url: "https://proptech.pt", lastModified: new Date(), changeFrequency: "daily", priority: 1 },
    { url: "https://proptech.pt/simulador", lastModified: new Date(), changeFrequency: "weekly", priority: 0.9 },
    { url: "https://proptech.pt/privacidade", lastModified: new Date(), changeFrequency: "yearly", priority: 0.3 },
  ];
}
