# PropTech Frontend

Nova interface Next.js 15 do portal imobiliário.

## O que está implementado

- **Homepage Map-First**: Mapa interativo (Mapbox/Carto) + lista de imóveis em split-pane
- **Design System 60/30/10**: Paleta científica (branco/cream 60%, preto/verde 30%, laranja 10%)
- **Mobile-First**: Layout responsivo, thumb-friendly, bottom navigation
- **Simulador Financeiro**:
  - IMT Jovem (regras exatas 2025/2026: isenção total até €330.539, parcial até €660.982)
  - Crédito Habitação com Euribor + Spread, gráfico de amortização
- **Página de Imóvel**: Galeria visual-first, descrição, características, CTA sticky
- **Integração API**: `src/lib/api.ts` pronto para ligar ao backend NestJS

## Como arrancar

```bash
# 1. Entrar na pasta
cd frontend

# 2. Instalar dependências (se ainda não fizeste)
npm install

# 3. Modo desenvolvimento
npm run dev
# Abre http://localhost:3000

# 4. Build de produção
npm run build
```

## Variáveis de ambiente

Cria `.env.local`:
```
NEXT_PUBLIC_API_URL=http://localhost:3001
```

## Estrutura

```
src/
  app/
    page.tsx          # Homepage Map-First
    simulador/
      page.tsx        # Simuladores IMT + Crédito
    imovel/[id]/
      page.tsx        # Ficha de imóvel
    layout.tsx        # Layout global + navegação
    globals.css       # Tokens de design 60/30/10
  components/
    MapView.tsx       # Mapbox interativo
    PropertyCard.tsx  # Card UI dos imóveis
  lib/
    api.ts            # Cliente HTTP para backend NestJS
```

## Roadmap próximo

1. Conectar ao backend real (substituir mocks por `fetchProperties()`)
2. Filtros avançados no mapa (preço, tipologia, área)
3. Polígonos de desenho livre no mapa
4. PWA + Service Worker
5. SEO (sitemap, schema.org)
