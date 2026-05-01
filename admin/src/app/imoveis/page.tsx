"use client";

import { ProtectedLayout } from "@/components/layout/protected-layout";
import { PropertyTable } from "@/components/imoveis/property-table";

export default function ImoveisPage() {
  return (
    <ProtectedLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Imóveis</h1>
          <p className="text-muted-foreground">
            Gestão do inventário imobiliário no padrão RESO 2.0.
          </p>
        </div>
        <PropertyTable />
      </div>
    </ProtectedLayout>
  );
}
