"use client";

import { useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { Search } from "lucide-react";
import FilterSheet from "./FilterSheet";

export default function SearchBar() {
  const router = useRouter();
  const params = useSearchParams();
  const [query, setQuery] = useState(params.get("city") || "");

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    const sp = new URLSearchParams(params.toString());
    if (query) sp.set("city", query);
    else sp.delete("city");
    router.push(`/?${sp.toString()}`);
  };

  return (
    <form
      onSubmit={handleSearch}
      className="bg-white border-b border-[var(--color-border)] px-4 py-3"
    >
      <div className="max-w-7xl mx-auto flex items-center gap-3">
        <div className="flex-1 relative">
          <Search
            className="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"
            size={18}
          />
          <input
            type="text"
            placeholder="Lisboa, Porto, Cascais..."
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[var(--color-muted)] text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)] transition-shadow"
          />
        </div>
        <FilterSheet />
      </div>
    </form>
  );
}
