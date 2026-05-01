"use client";

import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { Property } from "@/lib/types";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Pencil, Trash2, Search, Plus } from "lucide-react";
import { toast } from "sonner";
import { PropertyForm } from "./property-form";

export function PropertyTable() {
  const [search, setSearch] = useState("");
  const [editProperty, setEditProperty] = useState<Property | null>(null);
  const [formOpen, setFormOpen] = useState(false);
  const [deleteProperty, setDeleteProperty] = useState<Property | null>(null);
  const queryClient = useQueryClient();

  const { data: properties, isLoading } = useQuery({
    queryKey: ["properties"],
    queryFn: async () => {
      const res = await api.get<Property[]>("/properties");
      return res.data;
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/properties/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["properties"] });
      toast.success("Imóvel eliminado com sucesso");
      setDeleteProperty(null);
    },
    onError: () => toast.error("Erro ao eliminar imóvel"),
  });

  const filtered = properties?.filter((p) => {
    const term = search.toLowerCase();
    return (
      p.OriginatingSystemKey?.toLowerCase().includes(term) ||
      p.City?.toLowerCase().includes(term) ||
      p.UnparsedAddress?.toLowerCase().includes(term) ||
      p.PropertyType?.toLowerCase().includes(term)
    );
  });

  const handleEdit = (property: Property) => {
    setEditProperty(property);
    setFormOpen(true);
  };

  const handleNew = () => {
    setEditProperty(null);
    setFormOpen(true);
  };

  const formatPrice = (price?: number | null) => {
    if (!price) return "—";
    return new Intl.NumberFormat("pt-PT", {
      style: "currency",
      currency: "EUR",
    }).format(price);
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between gap-4">
        <div className="relative flex-1 max-w-sm">
          <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Pesquisar imóveis..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-8"
          />
        </div>
        <Button onClick={handleNew}>
          <Plus className="mr-2 h-4 w-4" />
          Novo Imóvel
        </Button>
      </div>

      <div className="rounded-md border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Ref.</TableHead>
              <TableHead>Tipo</TableHead>
              <TableHead>Cidade</TableHead>
              <TableHead>Morada</TableHead>
              <TableHead className="text-right">Preço</TableHead>
              <TableHead className="text-center">Q</TableHead>
              <TableHead className="text-center">WB</TableHead>
              <TableHead className="text-center">Área</TableHead>
              <TableHead>Status</TableHead>
              <TableHead className="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading ? (
              Array.from({ length: 5 }).map((_, i) => (
                <TableRow key={i}>
                  {Array.from({ length: 10 }).map((_, j) => (
                    <TableCell key={j}>
                      <Skeleton className="h-4 w-full" />
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : filtered?.length === 0 ? (
              <TableRow>
                <TableCell colSpan={10} className="text-center py-8 text-muted-foreground">
                  Nenhum imóvel encontrado.
                </TableCell>
              </TableRow>
            ) : (
              filtered?.map((property) => (
                <TableRow key={property.ListingKey}>
                  <TableCell className="font-medium">
                    {property.OriginatingSystemKey}
                  </TableCell>
                  <TableCell>{property.PropertyType}</TableCell>
                  <TableCell>{property.City || "—"}</TableCell>
                  <TableCell className="max-w-[200px] truncate">
                    {property.UnparsedAddress || "—"}
                  </TableCell>
                  <TableCell className="text-right font-medium">
                    {formatPrice(property.ListPrice)}
                  </TableCell>
                  <TableCell className="text-center">
                    {property.BedroomsTotal ?? "—"}
                  </TableCell>
                  <TableCell className="text-center">
                    {property.BathroomsTotalInteger ?? "—"}
                  </TableCell>
                  <TableCell className="text-center">
                    {property.LivingArea
                      ? `${property.LivingArea} m²`
                      : "—"}
                  </TableCell>
                  <TableCell>
                    <Badge variant={property.StandardStatus === "Active" ? "default" : "secondary"}>
                      {property.StandardStatus}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-right">
                    <div className="flex justify-end gap-2">
                      <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => handleEdit(property)}
                      >
                        <Pencil className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => setDeleteProperty(property)}
                      >
                        <Trash2 className="h-4 w-4 text-destructive" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </div>

      <PropertyForm
        property={editProperty}
        open={formOpen}
        onOpenChange={setFormOpen}
      />

      <Dialog open={!!deleteProperty} onOpenChange={() => setDeleteProperty(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirmar eliminação</DialogTitle>
            <DialogDescription>
              Tens a certeza que queres eliminar o imóvel{" "}
              <strong>{deleteProperty?.OriginatingSystemKey}</strong>? Esta ação é
              irreversível.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDeleteProperty(null)}>
              Cancelar
            </Button>
            <Button
              variant="destructive"
              onClick={() =>
                deleteProperty && deleteMutation.mutate(deleteProperty.ListingKey)
              }
              disabled={deleteMutation.isPending}
            >
              {deleteMutation.isPending ? "A eliminar..." : "Eliminar"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
