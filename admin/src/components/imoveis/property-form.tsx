"use client";

import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { api } from "@/lib/api";
import { Property, CreatePropertyDto } from "@/lib/types";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { toast } from "sonner";
import { useEffect } from "react";

const propertySchema = z.object({
  OriginatingSystemKey: z.string().min(1, "Obrigatório"),
  StandardStatus: z.string().min(1, "Obrigatório"),
  PropertyType: z.string().min(1, "Obrigatório"),
  PropertySubType: z.string().optional(),
  City: z.string().optional(),
  StateOrProvince: z.string().optional(),
  PostalCode: z.string().optional(),
  BedroomsTotal: z.string().optional(),
  BathroomsTotalInteger: z.string().optional(),
  LivingArea: z.string().optional(),
  LotSizeArea: z.string().optional(),
  ListPrice: z.string().optional(),
  UnparsedAddress: z.string().optional(),
  Latitude: z.string().optional(),
  Longitude: z.string().optional(),
});

type PropertyFormData = z.infer<typeof propertySchema>;

interface PropertyFormProps {
  property?: Property | null;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export function PropertyForm({ property, open, onOpenChange }: PropertyFormProps) {
  const queryClient = useQueryClient();
  const isEditing = !!property;

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<PropertyFormData>({
    resolver: zodResolver(propertySchema),
    defaultValues: {
      OriginatingSystemKey: "",
      StandardStatus: "Active",
      PropertyType: "Residential",
      PropertySubType: "",
      City: "",
      StateOrProvince: "",
      PostalCode: "",
      BedroomsTotal: "",
      BathroomsTotalInteger: "",
      LivingArea: "",
      LotSizeArea: "",
      ListPrice: "",
      UnparsedAddress: "",
      Latitude: "",
      Longitude: "",
    },
  });

  useEffect(() => {
    if (property) {
      reset({
        OriginatingSystemKey: property.OriginatingSystemKey,
        StandardStatus: property.StandardStatus,
        PropertyType: property.PropertyType,
        PropertySubType: property.PropertySubType || "",
        City: property.City || "",
        StateOrProvince: property.StateOrProvince || "",
        PostalCode: property.PostalCode || "",
        BedroomsTotal: property.BedroomsTotal != null ? String(property.BedroomsTotal) : "",
        BathroomsTotalInteger: property.BathroomsTotalInteger != null ? String(property.BathroomsTotalInteger) : "",
        LivingArea: property.LivingArea != null ? String(property.LivingArea) : "",
        LotSizeArea: property.LotSizeArea != null ? String(property.LotSizeArea) : "",
        ListPrice: property.ListPrice != null ? String(property.ListPrice) : "",
        UnparsedAddress: property.UnparsedAddress || "",
        Latitude: property.Latitude != null ? String(property.Latitude) : "",
        Longitude: property.Longitude != null ? String(property.Longitude) : "",
      });
    } else {
      reset({
        OriginatingSystemKey: "",
        StandardStatus: "Active",
        PropertyType: "Residential",
        PropertySubType: "",
        City: "",
        StateOrProvince: "",
        PostalCode: "",
        BedroomsTotal: "",
        BathroomsTotalInteger: "",
        LivingArea: "",
        LotSizeArea: "",
        ListPrice: "",
        UnparsedAddress: "",
        Latitude: "",
        Longitude: "",
      });
    }
  }, [property, reset, open]);

  const createMutation = useMutation({
    mutationFn: (data: CreatePropertyDto) => api.post("/properties", data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["properties"] });
      toast.success("Imóvel criado com sucesso");
      onOpenChange(false);
    },
    onError: () => toast.error("Erro ao criar imóvel"),
  });

  const updateMutation = useMutation({
    mutationFn: (data: CreatePropertyDto) =>
      api.patch(`/properties/${property?.ListingKey}`, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["properties"] });
      toast.success("Imóvel atualizado com sucesso");
      onOpenChange(false);
    },
    onError: () => toast.error("Erro ao atualizar imóvel"),
  });

  const toNum = (val?: string) => (val && val !== "" ? Number(val) : undefined);

  const onSubmit = (data: PropertyFormData) => {
    const dto: CreatePropertyDto = {
      OriginatingSystemKey: data.OriginatingSystemKey,
      StandardStatus: data.StandardStatus,
      PropertyType: data.PropertyType,
      PropertySubType: data.PropertySubType,
      City: data.City,
      StateOrProvince: data.StateOrProvince,
      PostalCode: data.PostalCode,
      UnparsedAddress: data.UnparsedAddress,
      BedroomsTotal: toNum(data.BedroomsTotal),
      BathroomsTotalInteger: toNum(data.BathroomsTotalInteger),
      LivingArea: toNum(data.LivingArea),
      LotSizeArea: toNum(data.LotSizeArea),
      ListPrice: toNum(data.ListPrice),
      Latitude: toNum(data.Latitude),
      Longitude: toNum(data.Longitude),
    };

    if (isEditing) {
      updateMutation.mutate(dto);
    } else {
      createMutation.mutate(dto);
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>
            {isEditing ? "Editar Imóvel" : "Novo Imóvel"}
          </DialogTitle>
          <DialogDescription>
            Preencha os dados do imóvel conforme o padrão RESO 2.0.
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="OriginatingSystemKey">Referência Interna *</Label>
              <Input
                id="OriginatingSystemKey"
                {...register("OriginatingSystemKey")}
                disabled={isEditing}
              />
              {errors.OriginatingSystemKey && (
                <p className="text-sm text-destructive">
                  {errors.OriginatingSystemKey.message}
                </p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="StandardStatus">Status *</Label>
              <Input id="StandardStatus" {...register("StandardStatus")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="PropertyType">Tipo *</Label>
              <Input id="PropertyType" {...register("PropertyType")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="PropertySubType">Subtipo</Label>
              <Input id="PropertySubType" {...register("PropertySubType")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="City">Cidade</Label>
              <Input id="City" {...register("City")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="StateOrProvince">Distrito</Label>
              <Input id="StateOrProvince" {...register("StateOrProvince")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="PostalCode">Código Postal</Label>
              <Input id="PostalCode" {...register("PostalCode")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="UnparsedAddress">Morada</Label>
              <Input id="UnparsedAddress" {...register("UnparsedAddress")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="BedroomsTotal">Quartos</Label>
              <Input id="BedroomsTotal" type="number" {...register("BedroomsTotal")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="BathroomsTotalInteger">Casas de Banho</Label>
              <Input id="BathroomsTotalInteger" type="number" {...register("BathroomsTotalInteger")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="LivingArea">Área Útil (m²)</Label>
              <Input id="LivingArea" type="number" {...register("LivingArea")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="LotSizeArea">Área do Lote (m²)</Label>
              <Input id="LotSizeArea" type="number" {...register("LotSizeArea")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="ListPrice">Preço (€)</Label>
              <Input id="ListPrice" type="number" {...register("ListPrice")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="Latitude">Latitude</Label>
              <Input id="Latitude" type="number" step="any" {...register("Latitude")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="Longitude">Longitude</Label>
              <Input id="Longitude" type="number" step="any" {...register("Longitude")} />
            </div>
          </div>
          <DialogFooter>
            <Button
              type="submit"
              disabled={createMutation.isPending || updateMutation.isPending}
            >
              {createMutation.isPending || updateMutation.isPending
                ? "A guardar..."
                : isEditing
                ? "Guardar Alterações"
                : "Criar Imóvel"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
