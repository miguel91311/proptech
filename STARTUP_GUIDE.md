# 🚀 Guia de Arranque — PropTech Portugal

> Como colocar tudo a funcionar localmente e ver o mapa com imóveis reais.

---

## Pré-requisitos

- **Node.js** ≥ 20 (tem v22.12.0 ✅)
- **npm** ≥ 10 (tem v10.9.0 ✅)
- **Docker Desktop** (para PostgreSQL + PostGIS)
- **Git** (opcional)

---

## 1. Subir a Base de Dados (PostgreSQL + PostGIS)

```bash
cd backend-api
docker-compose up -d
```

Verifica se está a correr:
```bash
docker ps
# Deves ver: credito_db   postgis/postgis:15-3.3-alpine
```

---

## 2. Instalar Dependências do Backend

```bash
cd backend-api
npm install
```

> Já está feito ✅ — `node_modules` existe e build passa.

---

## 3. Aplicar Schema Prisma + Seed de Imóveis

```bash
cd backend-api
npx prisma migrate dev --name init
npx prisma db seed
```

Isso cria:
- 3 roles (Platform Admin, Application Owner, Operator)
- 1 utilizador admin (`admin@proptech.local` / `Admin123!`)
- **6 imóveis reais** em Portugal (Lisboa, Cascais, Porto, Sintra, Albufeira, Coimbra)

---

## 4. Arrancar o Backend

```bash
cd backend-api
npm run start:dev
```

A API fica disponível em: `http://localhost:3000`

Documentação Swagger: `http://localhost:3000/api`

Health check: `http://localhost:3000/health`

---

## 5. Instalar Dependências do Frontend

```bash
cd frontend
npm install
```

> Já está feito ✅

---

## 6. Arrancar o Frontend

```bash
cd frontend
npm run dev
```

O frontend fica em: `http://localhost:3001`

---

## 7. Ver o Mapa com Imóveis Reais 🗺️

Abre `http://localhost:3001` no browser.

Deves ver:
- **Mapa interativo** com 6 pins em Portugal
- **Lista de imóveis** à direita com preços reais
- **Filtros** de preço, tipologia (T0–T5+) e tipo de imóvel
- **Ordenação** por preço/área/data

---

## 8. Testar a API com curl

```bash
# Listar imóveis
curl "http://localhost:3000/properties?withCoords=true"

# Geo-search (raio de 10km de Lisboa)
curl "http://localhost:3000/properties/geo-search?lat=38.7139&lng=-9.1394&radius=10"

# Login (obter JWT)
curl -X POST http://localhost:3000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@proptech.local","password":"Admin123!"}'

# Health check
curl http://localhost:3000/health
```

---

## 9. Rodar Testes Automatizados

```bash
cd backend-api

# Unit tests
npm run test

# End-to-end (requer PostgreSQL a correr)
npm run test:e2e

# Coverage
npm run test:cov
```

---

## 10. Build de Produção

### Backend (Docker)
```bash
cd backend-api
docker build -t proptech-backend .
```

### Frontend (Static Export)
```bash
cd frontend
npm run build
# Output em frontend/dist/ — pronto para Cloudflare Pages
```

---

## 🔧 Troubleshooting

| Problema | Solução |
|----------|---------|
| `docker-compose` não encontrado | Instala Docker Desktop |
| `prisma migrate dev` falha | Verifica se PostgreSQL está a correr: `docker ps` |
| Frontend mostra "Nenhum imóvel encontrado" | Verifica se backend está em `localhost:3000` |
| Erro CORS | Confirma `CORS_ORIGINS` no `.env` do backend |
| Login bloqueado | Rate limit: 5 tentativas/minuto. Aguarda 1 minuto. |

---

## 📁 Estrutura do Projeto

```
credito/
├── backend-api/          # NestJS + Prisma + PostGIS
│   ├── src/
│   │   ├── properties/   # CRUD + geo-search
│   │   ├── auth/         # JWT + RBAC
│   │   ├── core/         # Guards + AuditLog
│   │   └── ...
│   ├── prisma/
│   │   ├── schema.prisma
│   │   └── seed.ts       # 6 imóveis de Portugal
│   ├── Dockerfile
│   └── docker-compose.yml
├── frontend/             # Next.js 16 + Mapbox
│   ├── src/app/          # App Router
│   ├── src/components/   # MapView, FilterSheet, PropertyCard
│   └── public/sw.js      # PWA offline
└── STARTUP_GUIDE.md      # Este ficheiro
```

---

*Última atualização: 2026-04-27*
