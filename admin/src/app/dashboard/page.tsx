"use client";

import { ProtectedLayout } from "@/components/layout/protected-layout";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { Home, Users, Shield, FileText } from "lucide-react";

function DashboardContent() {
  const { data: properties } = useQuery({
    queryKey: ["properties-count"],
    queryFn: async () => {
      const res = await api.get("/properties");
      return res.data;
    },
  });

  const stats = [
    {
      title: "Imóveis",
      value: properties?.length ?? "—",
      icon: Home,
      description: "Total no portfólio",
    },
    {
      title: "Utilizadores",
      value: "—",
      icon: Users,
      description: "Em breve",
    },
    {
      title: "Auditoria",
      value: "—",
      icon: Shield,
      description: "Em breve",
    },
    {
      title: "KYC Pendentes",
      value: "—",
      icon: FileText,
      description: "Em breve",
    },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
        <p className="text-muted-foreground">
          Visão geral do seu centro de comando imobiliário.
        </p>
      </div>
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat) => (
          <Card key={stat.title}>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">
                {stat.title}
              </CardTitle>
              <stat.icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stat.value}</div>
              <p className="text-xs text-muted-foreground">
                {stat.description}
              </p>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}

export default function DashboardPage() {
  return (
    <ProtectedLayout>
      <DashboardContent />
    </ProtectedLayout>
  );
}
