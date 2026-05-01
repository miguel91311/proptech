# Roadmap Estratégico 2025-2026
## Transformação do Portal Imobiliário para Ecossistema PropTech de Classe Mundial

**Data:** Abril 2026  
**Base Atual:** Backend NestJS (PostgreSQL/PostGIS) + Frontend PHP  
**Meta:** Compatibilidade funcional com Idealista/Imovirtual/Zillow na próxima geração

---

## Diagnóstico Atual (Baseline)

| Dimensão | Estado | Nota |
|----------|--------|------|
| Backend API | ✅ Sólido | NestJS, JWT, RBAC, Audit Logs, PostGIS, RESO 2.0 schema |
| Banco de Dados | ✅ Sólido | PostgreSQL + PostGIS + Prisma ORM |
| Segurança API | ✅ Corporativa | Zero Trust, Circuit Breakers, Swagger |
| Frontend Site | ⚠️ Arcaico | PHP tradicional, não Mobile-First, sem Map-First |
| Design System | ❌ Inexistente | Sem paleta 60/30/10, sem WCAG AA |
| Performance | ❌ Fraca | Sem CDN, sem Core Web Vitals |
| Funcionalidades Negócio | ❌ Inexistentes | Sem simulador, sem mapa interativo, sem AVM |
| Compliance | ⚠️ Parcial | JWT/RBAC sim, mas sem KYC/AML/RGPD granular |
| Interoperabilidade | ❌ Inexistente | Sem OpenImmo, sem RESO feeds, sem CRM |
| IA/NLP | ❌ Inexistente | Sem busca semântica, sem chatbot |

---

## Arquitetura Alvo (Target Architecture)

### Stack Tecnológica Recomendada

| Camada | Tecnologia | Justificativa |
|--------|-----------|---------------|
| **Frontend** | React 19 + Next.js 15 (App Router) | SSR para SEO, Mobile-First nativo, performance |
| **Mapas** | Mapbox GL JS + PostGIS | Map-First, mapas isocrónicos, polígonos livres |
| **Design** | Tailwind CSS + Radix UI + Framer Motion | 60/30/10, animações microinterações |
| **Backend** | Manter NestJS existente | APIs REST + GraphQL opcional |
| **DB** | PostgreSQL 16 + PostGIS 3.4 | Já implementado, manter |
| **Cache** | Redis | Cache de sessões, AVM, ML feeds |
| **CDN** | Cloudflare (gratuito/pro) | Core Web Vitals, DDoS, cache global |
| **Storage** | Cloudflare R2 / AWS S3 | Fotos WebP, documentos KYC |
| **Search** | Meilisearch / Algolia | Full-text search instantâneo |
| **Fila** | BullMQ (Redis) | Jobs async (processamento de imagens, feeds) |
| **Monitorização** | Sentry + LogRocket | Rastreamento de erros e sessões |

---

## Fases de Implementação

### 🔴 FASE 1: Fundação Frontend (Meses 1-2)
**Objetivo:** Substituir o site PHP por uma aplicação React moderna, Mobile-First, com Design System aplicado.

#### Entregáveis
- [ ] **Projeto Next.js 15** na pasta `/frontend` (ou substituição gradual do PHP)
- [ ] **Design System** com Tokens:
  - Paleta 60/30/10 definida (ex: 60% `#FAFAF8`, 30% `#1A1A1A` + `#22C55E`, 10% `#F97316`)
  - Tipografia scale (Inter/Geist para corpo, Playfair Display para títulos de luxo)
  - Componentes: Button, Card, Input, Slider, Badge
- [ ] **Layout Mobile-First**:
  - Thumb-friendly: controles dentro do arco do polegar (bottom sheet para filtros)
  - Bottom navigation fixa no mobile
  - Top bar colapsável com search integrado
- [ ] **Homepage Visual-First**:
  - Hero com busca minimalista (sem overload)
  - Grid de imóveis em Card UI
  - F-pattern para textos legais, Z-pattern para descoberta
- [ ] **Página de Imóvel**:
  - Galeria expansiva (lightbox nativo, sem sair da página)
  - Carrossel touch-friendly
  - Informações em accordion (reduz carga cognitiva)
- [ ] **Acessibilidade**: Testes WCAG AA (contraste 4.5:1, leitores de ecrã, aria-labels)

#### Verificação
- [ ] Lighthouse Performance > 70 (desktop e mobile)
- [ ] Lighthouse Accessibility > 90
- [ ] Testes E2E com Playwright (5 cenários críticos)

#### Tempo Estimado
- **1 desenvolvedor fullstack:** 6-8 semanas
- **1 dev frontend + 1 dev backend:** 4-5 semanas

---

### 🔴 FASE 2: Map-First & Descoberta Inteligente (Meses 2-3)
**Objetivo:** Implementar o mapa como núcleo absoluto da interface, com filtros avançados e geolocalização.

#### Entregáveis
- [ ] **Mapa interativo** (Mapbox GL JS) ocupando 100% do ecrã em desktop, modal/sheet em mobile
- [ ] **Painel duplo** (split-pane): mapa à esquerda (60%), lista de cards à direita (40%)
- [ ] **Filtros hiper-contextuais**:
  - Preço (range slider dual)
  - Tipologia, estado (novo/reabilitado/ruína)
  - Com elevador / Sem elevador
  - Áreas úteis / Brutas
  - Certificação energética
  - Ano de construção
  - **Exclusão de agências** (botão "Só particulares")
- [ ] **Desenho livre no mapa** (polígonos): utilizador desenha área personalizada
- [ ] **Clusters de imóveis**: agrupamento inteligente de pins
- [ ] **Hover nos pins**: card flutuante com foto, preço, quick actions
- [ ] **Walkability Score**: integrar API WalkScore ou calcular internamente (distância a serviços)
- [ ] **Backend**: Endpoints `GET /properties/search` com queries PostGIS (`ST_Within`, `ST_DWithin`)

#### Verificação
- [ ] Mapa carrega < 1.5s com 1000+ imóveis
- [ ] Filtros aplicam em < 500ms
- [ ] Polígono desenhado retorna imóveis corretamente

#### Tempo Estimado
- **1 dev frontend + 1 dev backend:** 4-6 semanas

---

### 🟠 FASE 3: Experiência Imersiva & Performance (Meses 3-4)
**Objetivo:** Ativar galerias 3D, virtual tours, lazy loading, e garantir Core Web Vitals.

#### Entregáveis
- [ ] **Galeria expansiva** com lazy loading nativo (`loading="lazy"` + Intersection Observer)
- [ ] **Conversão automática WebP** em todos os uploads (já existe `comum/web_converter.php` — integrar no pipeline)
- [ ] **CDN Cloudflare** configurada:
  - Cache de assets estáticos
  - Image Optimization (compressão automática)
  - DDoS protection
- [ ] **Virtual Tour MVP**: Integração iframe com Matterport ou modelo 3D básico (Google Model Viewer)
- [ ] **Virtual Staging IA básica**: Integração API externa (ex: REimagine Home) ou marcação d'água em imagens processadas
- [ ] **Performance gatilho**:
  - LCP < 2.5s (WebP + CDN + preload de hero image)
  - INP < 200ms (debounce em filtros, virtualização de listas com `react-window`)
  - CLS < 0.1 (dimensões fixas em imagens, skeleton loaders)
- [ ] **PWA**: Service Worker para cache offline, add-to-home-screen

#### Verificação
- [ ] PageSpeed Insights: LCP, INP, CLS dentro dos limites
- [ ] GTmetrix: Grade A
- [ ] Teste de carga: 1000 users simultâneos sem degradação

#### Tempo Estimado
- **1 dev frontend + 1 dev DevOps/cloud:** 3-4 semanas

---

### 🟠 FASE 4: Ferramentas Financeiras (Mês 4)
**Objetivo:** Implementar simuladores de crédito, IMT Jovem, e base para AVM.

#### Entregáveis
- [ ] **Simulador de Crédito à Habitação**:
  - Inputs: Valor imóvel, entrada (%), prazo (anos), taxa Euribor (atualizada via API Banco de Portugal/ECB)
  - Spread bancário selecionável
  - Cálculo TAN, TAEG, prestação mensal
  - Gráfico de amortização (Stack Recharts)
- [ ] **Calculadora IMT Jovem** (funcionalidade diferenciadora):
  - Regras exatas do diploma 2025/2026:
    - Isenção total até €330.539
    - Isenção parcial (percentual regressivo) até €660.982
    - Sem isenção acima de €660.982
    - **Apenas para compradores ≤ 35 anos**
  - Input: idade do comprador, valor do imóvel, localização (IMT varia por concelho)
  - Output: imposto a pagar (ou €0 se isento)
- [ ] **API de Euribor**: Integração com API ECB/BdP para taxas em tempo real
- [ ] **AVM Baseline (Zestimate-style)**:
  - Algoritmo simples baseado em:
    - Preço médio por m² na área (PostGIS buffer)
    - Estado do imóvel
    - Ano de construção
    - Tendência de mercado (últimos 6 meses)
  - Exibir como "Estimativa de Valor" na ficha do imóvel
  - Disclaimer: "Estimativa automatizada, não substitui avaliação bancária"

#### Verificação
- [ ] Simulador de crédito devolve TAEG idêntico ao de um banco comparador
- [ ] IMT Jovem: testar valores limite (€330.539, €660.982)
- [ ] AVM: desvio < 15% face a valor de venda real (benchmark interno)

#### Tempo Estimado
- **1 dev frontend + 1 dev backend:** 3-4 semanas

---

### 🟡 FASE 5: Segurança Corporativa & Compliance (Mês 5)
**Objetivo:** Ativar KYC, AML, anti-deepfake, e RGPD granular.

#### Entregáveis
- [ ] **KYC (Know Your Customer)** — para vendedores e agentes:
  - Upload de CC/Passaporte (frente e verso)
  - Selfie com liveness detection (API: Onfido / Veriff / AU10TIX)
  - Validação cruzada com dados públicos (se disponível em PT)
  - Status: `pending_verification` → `verified` | `rejected`
- [ ] **AML Screening**:
  - Integração API: NameScan ou World-Check One (LSEG)
  - Verificação de UBOs (Ultimate Beneficial Owners)
  - Flag de PEPs (Politically Exposed Persons) e sanções
- [ ] **Anti-Deepfake / Anti-Ghost Listing**:
  - Análise de fotos no upload: metadados EXIF, reverse image search (Google Images API)
  - Deteção de inconsistências de luz/textura (API: Coraly.ai ou solução open-source)
  - Marcação d'água em imagens com staging virtual
  - Score de confiança do anúncio exibido ao utilizador
- [ ] **RGPD Granular**:
  - Consentimento explícito (Opt-In) por finalidade:
    - Comunicações de marketing
    - Partilha de dados com parceiros financeiros
    - Analytics comportamental
  - Painel "A Minha Privacidade": ver, exportar, apagar dados
  - API `DELETE /users/me` (direito ao esquecimento)
  - Registo de consentimentos em `AuditLog` (já implementado)
- [ ] **Certidão do Predial Online**:
  - Link direto para IAPMEI/Registos (ou integração futura)
  - Verificação de ónus e litígios no anúncio

#### Verificação
- [ ] Vendedor sem KYC não pode publicar anúncio
- [ ] Foto copiada de outro site é detetada (testar com URL conhecido)
- [ ] Utilizador consegue exportar todos os seus dados em JSON
- [ ] Auditoria mostra todos os consentimentos assinados

#### Tempo Estimado
- **1 dev backend + 1 dev compliance/integrações:** 4-5 semanas

---

### 🟡 FASE 6: Interoperabilidade & Multiposting (Mês 6)
**Objetivo:** Permitir que imobiliárias sincronizem inventário com múltiplos portais.

#### Entregáveis
- [ ] **API RESO Web API** (compatibilidade EUA):
  - Endpoints padronizados: `GET /reso/Property`, `GET /reso/Media`
  - Formato RCF (RESO Common Format)
  - Documentação Swagger detalhada
- [ ] **Exportação OpenImmo XML** ( Europa):
  - Gerar XML com 300+ campos mapeados do schema RESO
  - Zip automático com fotos
  - Endpoint: `GET /exports/openimmo`
- [ ] **Feeds Multiposting** (MVP):
  - Integração manual com Idealista (XML upload ou API)
  - Integração manual com Imovirtual
  - Integração manual com OLX/Custo Justo
- [ ] **Webhooks**:
  - `property.created`, `property.updated`, `property.deleted`
  - Para CRMs externos se subscreverem
- [ ] **Dashboard de Extranets**:
  - Visualização de leads por portal
  - Estatísticas de cliques e contactos

#### Verificação
- [ ] XML OpenImmo valida contra schema oficial
- [ ] Multiposting sincroniza em < 5 minutos
- [ ] Webhooks entregam com retry (exponencial backoff)

#### Tempo Estimado
- **1 dev backend:** 4-5 semanas

---

### 🟢 FASE 7: Inteligência Artificial & NLP (Meses 6-7)
**Objetivo:** Adicionar busca semântica, chatbot, e valoração preditiva avançada.

#### Entregáveis
- [ ] **Busca Semântica (Smart Search)**:
  - Input de texto livre: "casa com quintal perto de escolas em Cascais"
  - Parser NLP (OpenAI GPT-4-mini / Mistral local):
    - Extrai: localização, características, proximidades
  - Conversão para query PostGIS + filtros
  - Resultados ranqueados por relevância
- [ ] **Chatbot Imobiliário**:
  - Integrado no widget (canto inferior direito)
  - Responde a: preços médios por zona, documentos necessários, simulação rápida
  - Contexto: sabe o imóvel que o utilizador está a ver
- [ ] **AVM Avançado (Machine Learning)**:
  - Modelo de regressão (XGBoost / scikit-learn) treinado com:
    - Histórico de vendas na plataforma
    - Características do imóvel
    - Localização (escolas, transportes, comércio)
  - Atualização semanal automática
  - Intervalo de confiança (ex: €350.000 ± €25.000)
- [ ] **Recomendações Inteligentes**:
  - "Imóveis semelhantes" baseado em embeddings
  - "Baseado na tua pesquisa" (collaborative filtering)

#### Verificação
- [ ] Busca semântica: 80% das queries retornam resultados relevantes
- [ ] Chatbot: resolve 60% das perguntas sem intervenção humana
- [ ] AVM ML: MAPE (erro percentual médio) < 10%

#### Tempo Estimado
- **1 dev IA/ML + 1 dev backend:** 5-6 semanas

---

### 🟢 FASE 8: Otimização & Escala (Mês 7-8)
**Objetivo:** Consolidar performance, SEO, e preparar para escala internacional.

#### Entregáveis
- [ ] **SEO Técnico Avançado**:
  - SSR com Next.js (já garantido na Fase 1)
  - Sitemap dinâmico (`/sitemap.xml` com 100k+ URLs)
  - Schema.org markup: `RealEstateListing`, `Product`, `BreadcrumbList`
  - URLs amigáveis: `/imoveis/lisboa/cascais/t3-a-venda-12345`
  - Meta tags dinâmicas por página
- [ ] **Internacionalização (i18n)**:
  - Suporte PT, EN, FR, ES
  - Moedas: EUR, USD, GBP
  - Imóveis com geo-referenciação multi-país
- [ ] **Rate Limiting & DDoS**:
  - `@nestjs/throttler` ativado em todas as rotas públicas
  - Cloudflare Rate Limiting
- [ ] **Observabilidade Completa**:
  - Sentry: rastreamento de erros frontend + backend
  - LogRocket: gravação de sessões de utilizadores frustrados
  - UptimeRobot: monitorização de uptime
  - Dashboard Grafana (métricas de API, DB, cache)
- [ ] **Testes de Carga**:
  - k6 ou Artillery: 10.000 users simultâneos
  - P95 latência < 500ms em endpoints críticos

#### Verificação
- [ ] Google Search Console: 0 erros de indexação
- [ ] Lighthouse Performance > 90 (mobile)
- [ ] Uptime > 99.9% no mês

#### Tempo Estimado
- **1 dev frontend + 1 dev DevOps:** 3-4 semanas

---

## Cronograma Consolidado

```
Mês 1-2:  ████████ FASE 1 (Frontend Next.js + Design System)
Mês 2-3:  ████████ FASE 2 (Map-First + Filtros Avançados)
Mês 3-4:  ████████ FASE 3 (Performance + Imersão)
Mês 4-5:  ██████░░ FASE 4 (Ferramentas Financeiras)
Mês 5-6:  ████████ FASE 5 (Segurança + Compliance)
Mês 6-7:  ██████░░ FASE 6 (Multiposting + Feeds)
Mês 6-7:  ████████ FASE 7 (IA + NLP + AVM ML)
Mês 7-8:  ██████░░ FASE 8 (SEO + i18n + Observabilidade)
```

**Tempo Total:** 7-8 meses com uma equipa de 2-3 desenvolvedores fullstack dedicados.

---

## Investimento Necessário (Estimativa)

| Categoria | Custo Mensal | Nota |
|-----------|-------------|------|
| **Cloudflare Pro** | €20 | CDN, WAF, cache |
| **Mapbox** | Freemium → €50 | Depende de loads |
| **Meilisearch Cloud** | €30 | Search instantâneo |
| **KYC/AML APIs** | €0.50-2.00/verificação | Onfido/Veriff (paga por uso) |
| **OpenAI API** (NLP) | €50-200 | GPT-4-mini para busca/chat |
| **Sentry/LogRocket** | €26 | Monitorização |
| **Servidor (VPS/Cloud)** | €50-200 | Hetzner / DigitalOcean / AWS |
| **Fotos CDN** | €10-50 | Cloudflare R2 (sem egress fees) |
| **Total Mensal** | **€250-600** | Pós-lançamento estável |

---

## Perguntas Decisivas para Ti

Antes de avançarmos para a Fase 1, preciso que respondas:

1. **Queres substituir o PHP de uma vez ou fazer migração gradual?**
   - Radical: Novo projeto Next.js em `/frontend`, PHP serve apenas como API legacy temporária
   - Gradual: PHP continua, mas Novas páginas (mapa, imóvel, simulador) são React embed via micro-frontend

2. **Qual é o orçamento mensal disponível para serviços externos (cloud, APIs, KYC)?**
   - Isso determina se usamos OpenAI (€$) ou modelos locais (Mistral/Ollama).

3. **Tens já uma conta de empresa/imobiliária ou é projeto pessoal?**
   - KYC/AML e RGPD granular exigem entidade legal para contratos com Onfido/Veriff.

4. **Prioridade máxima:** O que gostarias de ter FUNCIONAL daqui a 1 mês?
   - Sugestão: Mapa interativo + Site moderno + Simulador IMT Jovem (diferenciador enorme em Portugal)

---

## Próximo Passo Imediato

Assim que responderes às perguntas acima, começamos a **FASE 1** com:
1. Setup do projeto Next.js 15 na pasta `/frontend`
2. Design System inicial com Tailwind + paleta 60/30/10
3. Página de pesquisa Map-First (mapa + cards lado a lado)

**Aguardo a tua direção.**
