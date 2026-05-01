"use client";

import { ProtectedLayout } from "@/components/layout/protected-layout";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { format } from "date-fns";
import { pt } from "date-fns/locale";

interface AuditLog {
  id: string;
  method: string;
  route: string;
  delta: Record<string, { before: unknown; after: unknown }> | null;
  timestamp: string;
  user: { id: string; name: string; email: string } | null;
}

function AuditoriaContent() {
  const { data: logs, isLoading } = useQuery({
    queryKey: ["audit-logs"],
    queryFn: async () => {
      const res = await api.get<AuditLog[]>("/audit-logs");
      return res.data;
    },
  });

  const formatDelta = (delta: AuditLog["delta"]) => {
    if (!delta) return "—";
    const keys = Object.keys(delta);
    if (keys.length === 0) return "—";
    return keys.slice(0, 3).join(", ") + (keys.length > 3 ? "..." : "");
  };

  const methodColor = (method: string) => {
    switch (method) {
      case "POST":
        return "bg-green-100 text-green-800";
      case "PUT":
      case "PATCH":
        return "bg-yellow-100 text-yellow-800";
      case "DELETE":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Auditoria Forense</h1>
        <p className="text-muted-foreground">
          Registo completo de todas as alterações efetuadas no sistema.
        </p>
      </div>
      <div className="rounded-md border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Data/Hora</TableHead>
              <TableHead>Utilizador</TableHead>
              <TableHead>Método</TableHead>
              <TableHead>Rota</TableHead>
              <TableHead>Campos Alterados</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading ? (
              Array.from({ length: 5 }).map((_, i) => (
                <TableRow key={i}>
                  {Array.from({ length: 5 }).map((_, j) => (
                    <TableCell key={j}>
                      <Skeleton className="h-4 w-full" />
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : logs?.length === 0 ? (
              <TableRow>
                <TableCell colSpan={5} className="text-center py-8 text-muted-foreground">
                  Nenhum registo de auditoria encontrado.
                </TableCell>
              </TableRow>
            ) : (
              logs?.map((log) => (
                <TableRow key={log.id}>
                  <TableCell>
                    {format(new Date(log.timestamp), "dd/MM/yyyy HH:mm:ss", {
                      locale: pt,
                    })}
                  </TableCell>
                  <TableCell>
                    {log.user ? (
                      <div>
                        <p className="font-medium">{log.user.name}</p>
                        <p className="text-xs text-muted-foreground">{log.user.email}</p>
                      </div>
                    ) : (
                      "Sistema"
                    )}
                  </TableCell>
                  <TableCell>
                    <Badge className={methodColor(log.method)}>{log.method}</Badge>
                  </TableCell>
                  <TableCell className="font-mono text-xs">{log.route}</TableCell>
                  <TableCell className="text-sm text-muted-foreground max-w-xs truncate">
                    {formatDelta(log.delta)}
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </div>
    </div>
  );
}

export default function AuditoriaPage() {
  return (
    <ProtectedLayout>
      <AuditoriaContent />
    </ProtectedLayout>
  );
}
