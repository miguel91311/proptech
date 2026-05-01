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
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { format } from "date-fns";
import { pt } from "date-fns/locale";
import { toast } from "sonner";
import { CheckCircle } from "lucide-react";

interface KycRequest {
  id: string;
  status: string;
  documentUrl?: string;
  selfieUrl?: string;
  verifiedBy?: string;
  verifiedAt?: string;
  createdAt: string;
  user: { id: string; name: string; email: string };
}

function KycContent() {
  const queryClient = useQueryClient();

  const { data: requests, isLoading } = useQuery({
    queryKey: ["kyc-requests"],
    queryFn: async () => {
      const res = await api.get<KycRequest[]>("/kyc");
      return res.data;
    },
  });

  const verifyMutation = useMutation({
    mutationFn: (userId: string) => api.post(`/kyc/${userId}/verify`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["kyc-requests"] });
      toast.success("KYC verificado com sucesso");
    },
    onError: () => toast.error("Erro ao verificar KYC"),
  });

  const statusVariant = (status: string) => {
    switch (status) {
      case "verified":
        return "default";
      case "pending":
        return "secondary";
      case "rejected":
        return "destructive";
      default:
        return "outline";
    }
  };

  const statusLabel = (status: string) => {
    switch (status) {
      case "verified":
        return "Verificado";
      case "pending":
        return "Pendente";
      case "rejected":
        return "Rejeitado";
      case "not_submitted":
        return "Não Submetido";
      default:
        return status;
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">KYC / RGPD</h1>
        <p className="text-muted-foreground">
          Verificação de identidade e consentimentos de privacidade.
        </p>
      </div>

      <div className="space-y-4">
        <h2 className="text-xl font-semibold">Pedidos KYC</h2>
        <div className="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Utilizador</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Documento</TableHead>
                <TableHead>Selfie</TableHead>
                <TableHead>Submetido em</TableHead>
                <TableHead>Verificado em</TableHead>
                <TableHead className="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {isLoading ? (
                Array.from({ length: 5 }).map((_, i) => (
                  <TableRow key={i}>
                    {Array.from({ length: 7 }).map((_, j) => (
                      <TableCell key={j}>
                        <Skeleton className="h-4 w-full" />
                      </TableCell>
                    ))}
                  </TableRow>
                ))
              ) : requests?.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={7} className="text-center py-8 text-muted-foreground">
                    Nenhum pedido KYC encontrado.
                  </TableCell>
                </TableRow>
              ) : (
                requests?.map((req) => (
                  <TableRow key={req.id}>
                    <TableCell>
                      <div>
                        <p className="font-medium">{req.user.name}</p>
                        <p className="text-xs text-muted-foreground">{req.user.email}</p>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant={statusVariant(req.status)}>
                        {statusLabel(req.status)}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      {req.documentUrl ? (
                        <a
                          href={req.documentUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-primary hover:underline text-sm"
                        >
                          Ver documento
                        </a>
                      ) : (
                        "—"
                      )}
                    </TableCell>
                    <TableCell>
                      {req.selfieUrl ? (
                        <a
                          href={req.selfieUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-primary hover:underline text-sm"
                        >
                          Ver selfie
                        </a>
                      ) : (
                        "—"
                      )}
                    </TableCell>
                    <TableCell>
                      {format(new Date(req.createdAt), "dd/MM/yyyy", {
                        locale: pt,
                      })}
                    </TableCell>
                    <TableCell>
                      {req.verifiedAt
                        ? format(new Date(req.verifiedAt), "dd/MM/yyyy", {
                            locale: pt,
                          })
                        : "—"}
                    </TableCell>
                    <TableCell className="text-right">
                      {req.status === "pending" && (
                        <Button
                          size="sm"
                          onClick={() => verifyMutation.mutate(req.user.id)}
                          disabled={verifyMutation.isPending}
                        >
                          <CheckCircle className="mr-1 h-4 w-4" />
                          Verificar
                        </Button>
                      )}
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  );
}

export default function KycPage() {
  return (
    <ProtectedLayout>
      <KycContent />
    </ProtectedLayout>
  );
}
