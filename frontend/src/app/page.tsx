import { Suspense } from "react";
import HomeClient from "./HomeClient";

export default function HomePage() {
  return (
    <Suspense fallback={<div className="h-[calc(100vh-4rem)] flex items-center justify-center text-sm text-[var(--color-text-secondary)]">A carregar...</div>}>
      <HomeClient />
    </Suspense>
  );
}
