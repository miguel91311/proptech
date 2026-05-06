# Roadmap Estrategico 2025-2026
## Transformacao do Portal Imobiliario para Ecossistema PropTech de Classe Mundial

**Data:** Abril 2026  
**Base Atual:** Backend NestJS (PostgreSQL/PostGIS) + Frontend Next.js + Admin Next.js  
**Mercados Alvo:** Portugal e Angola  
**Meta:** Compatibilidade funcional com Idealista/Imovirtual/Zillow na proxima geracao

---

## Diagnostico Atual (Baseline)

| Dimensao | Estado | Nota |
|----------|--------|------|
| Backend API | Solido | NestJS, JWT, RBAC, Audit Logs, PostGIS, RESO 2.0 schema |
| Banco de Dados | Solido | PostgreSQL + PostGIS + Prisma ORM |
| Seguranca API | Corporativa | Zero Trust, Circuit Breakers, Swagger |
| Frontend Site | Moderno | Next.js 16, Tailwind v4, Mobile-First, Map-First |
| Painel Admin | Moderno | Next.js 16, shadcn/ui, TanStack Query |
| Design System | Parcial | Paleta definida, WCAG AA em progresso |
| Performance | Melhorada | CDN configuravel, Core Web Vitals monitorados |

---

## Fases de Implementacao

### Fase 1 - Fundacao (Concluida)
- [x] Backend API NestJS + Prisma + PostgreSQL
- [x] PostGIS para dados geograficos
- [x] Autenticacao JWT com roles (RBAC)
- [x] Frontend publico Next.js
- [x] Painel administrativo Next.js
- [x] Deploy em producao (Railway + Supabase)

### Fase 2 - Imobiliario Core (Em Progresso)
- [ ] Simulador de credito integrado
- [ ] Mapa interativo com filtros avancados
- [ ] AVM (Automated Valuation Model)
- [ ] Integracao MLS/OpenImmo
- [ ] Sistema de favoritos e alertas

### Fase 3 - ERP Completo (Plano)

#### 1. CRM & Vendas (Pre-venda)
- Gestao de leads, oportunidades e pipeline comercial
- Cotacoes/propostas comerciais e sua conversao em faturas
- Segmentacao de clientes e scoring
- Historico de interacoes/comunicacoes com clientes

#### 2. Gestao de Stocks e Supply Chain
- Inventario em tempo real e alertas de reposicao
- Gestao de armazens multiplos e localizacoes
- Encomendas de compra e fornecedores
- Rastreabilidade de lotes e seriais
- Gestao de PMP (Planeamento de Necessidades de Materiais)

#### 3. Gestao de Projetos (Operacional)
- Planeamento de tarefas e Gantt
- Alocacao de recursos e tempo
- Faturacao por projetos/milestones
- Controlo de horas e timesheets

#### 4. eCommerce & Canais Digitais
- Loja online integrada
- Integracao com marketplaces (Amazon, etc.)
- Gestao de campanhas de marketing
- Carrinho de compras e checkout

#### 5. Gestao Documental Avancada
- Assinatura digital qualificada
- Arquivo digital legal (com retencao regulada)
- OCR e extracao automatica de dados de documentos
- Workflow de aprovacao documental

#### 6. Producao & Operacoes (para empresas industriais)
- Ordens de producao
- Gestao de BOMs (listas de materiais)
- Controlo de qualidade
- Manutencao de equipamentos

#### 7. Integracoes & Ecossistema
- APIs abertas para developers
- Integracao com ferramentas externas (Slack, Teams, etc.)
- Webhooks e automatizacoes customizaveis (Zapier-style)
- Importacao/exportacao flexivel de dados

#### 8. Multi-entidade & Internacional
- Consolidado de grupos empresariais
- Multi-moeda com conversoes automaticas (EUR/AOA/USD)
- Conformidade fiscal de varios paises:
  - Portugal (IVA, SAF-T, e-fatura)
  - Angola (IVA, modelo fiscal local)
  - EU (GDPR, conformidade fiscal)

---

## Especificidades por Mercado

### Portugal
- Conformidade com IVA portugues e SAF-T
- Integracao com Portal das Financas
- Suporte a NIF e NIPC
- Conformidade RGPD/GDPR

### Angola
- Suporte a moeda AOA (Kwanza)
- Modelo fiscal angolano
- Integracao com sistemas bancarios locais
- Suporte a multi-idioma (portugues)

---

## Arquitetura Tecnologica Alvo

| Camada | Tecnologia |
|--------|------------|
| Frontend Publico | Next.js 16 + Tailwind v4 |
| Painel Admin | Next.js 16 + shadcn/ui |
| Backend API | NestJS + Prisma |
| Base de Dados | PostgreSQL + PostGIS (Supabase) |
| Cache | Redis |
| Fila/Background | BullMQ + Redis |
| Storage | Supabase Storage |
| Deploy | Railway (backend) + Vercel (frontend) |
| Monitorizacao | Sentry + LogRocket |

---

## Notas
- Prioridade imediata: estabilizar frontend e admin em producao
- Segunda prioridade: simulador de credito e mapa interativo
- ERP completo: fase 3, apos consolidacao do core imobiliario
