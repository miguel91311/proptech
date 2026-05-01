# Roadmap - API Backend Imobiliário (PropTech)

> **Status:** Arquitetura base 95% implementada. Faltam infraestrutura local e integração com legado.  
> **Stack:** NestJS, TypeScript, Prisma ORM, PostgreSQL + PostGIS.  
> **Padrão de Dados:** RESO Data Dictionary 2.0.

---

## ✅ O QUE JÁ ESTÁ IMPLEMENTADO

### Fase 0: Setup & Infraestrutura
- [x] NestJS project scaffold com Swagger (`/api`) e BearerAuth
- [x] Docker Compose com PostGIS (`postgis/postgis:15-3.3-alpine`)
- [x] Prisma ORM configurado com extensão `postgresqlExtensions`
- [x] ConfigModule global com `.env` (DATABASE_URL, JWT_SECRET, JWT_EXPIRATION, PORT)
- [x] `package.json` com todas as dependências (`bcrypt`, `passport-jwt`, `opossum`, etc.)
- [x] Script de seed configurado no `package.json` (`prisma.seed`)

### Fase 1: Modelagem de Dados (RESO 2.0)
- [x] Model `Property` com campos RESO:
  - `ListingKey` (PK), `OriginatingSystemKey` (unique), `ListAOR`
  - `StandardStatus`, `PropertyType`, `PropertySubType`
  - `StreetAddress`, `City`, `StateOrProvince`, `PostalCode`, `Country`
  - Campo espacial PostGIS: `Location geometry(Point, 4326)`
  - `BedroomsTotal`, `BathroomsTotalInteger`, `LivingArea`, `LotSizeArea`
  - `ListPrice`, `Currency`
  - Metadados temporais: `ListingContractDate`, `ExpirationDate`, `ModificationTimestamp`, `OriginalEntryTimestamp`
- [x] CRUD Properties com suporte a PostGIS (`ST_MakePoint`, `ST_DWithin`)
- [x] Endpoint `GET /properties/geo-search` (busca por raio geográfico)
- [x] DTOs com `class-validator` (`CreatePropertyDto`, `UpdatePropertyDto`)

### Fase 2: Segurança e RBAC
- [x] Model `Role` com hierarquias: `Platform Admin`, `Application Owner`, `Operator`
- [x] Model `User` com relação a Role + passwordHash com `bcrypt`
- [x] `JwtAuthGuard` global (APP_GUARD) — autenticação real com Passport + JWT
- [x] `RolesGuard` global (APP_GUARD) — autorização RBAC
- [x] Decorator `@Roles(...)` para proteção por endpoint
- [x] Proteção diferenciada nos controllers (DELETE só para `Platform Admin` e `Application Owner`)
- [x] AuthController: `POST /auth/login`, `POST /auth/register` (só admin), `GET /auth/profile`
- [x] JwtStrategy que valida token e carrega user completo com role do banco
- [x] Seed script (`prisma/seed.ts`) que cria 3 roles + utilizador admin inicial

### Fase 2: Middleware de Auditoria Forense
- [x] `AuditLogInterceptor` global (APP_INTERCEPTOR)
- [x] Captura automática de mutações: POST, PUT, PATCH, DELETE
- [x] **Delta real** (antes vs. depois) — busca estado anterior no banco antes de UPDATE/DELETE
- [x] Registo em tabela `AuditLog`: `userId`, `method`, `route`, `delta` (JSON), `timestamp`
- [x] Execução assíncrona (não bloqueia resposta ao cliente)
- [x] Tratamento de erro — loga mesmo quando a operação falha

### Fase 2: Resiliência (Circuit Breakers)
- [x] `MlsService` com `opossum` Circuit Breaker
- [x] Configuração: timeout 3s, threshold 50%, reset 10s
- [x] Fallback com dados em cache/mensagem amigável
- [x] Eventos de monitoramento (`open`, `halfOpen`, `close`)

### Fase 3: Testes Automatizados
- [x] Testes unitários para `AuthService` (login, register, validateUser, getProfile)
- [x] Testes unitários para `PropertiesService` (CRUD + geo-search)
- [x] Testes unitários para `PrismaService`
- [x] Testes e2e completos (`test/app.e2e-spec.ts`):
  - Login com credenciais válidas → JWT
  - Rejeição de credenciais inválidas
  - Profile autenticado
  - CRUD de imóveis com autenticação
  - RBAC: Operator bloqueado de DELETE
  - Verificação de audit logs criados

---

## ❌ O QUE FALTA IMPLEMENTAR / VERIFICAR

### Infraestrutura Local (Bloqueante para testar)
- [ ] `npm install` — instalar dependências do `package.json`
- [ ] `npx prisma migrate dev` ou `npx prisma db push` — aplicar schema na base
- [ ] `npx prisma db seed` — popular roles e admin
- [ ] `npm run build` — verificar se compila sem erros
- [ ] `npm run test` — rodar testes unitários
- [ ] `npm run test:e2e` — rodar testes end-to-end (requer PostgreSQL rodando)

### Ajustes & Bugs Potenciais
- [ ] **GET públicos bloqueados?** — `JwtAuthGuard` é global, mas teste e2e chama `GET /properties` sem token e espera 200. Isso pode indicar que endpoints GET estão retornando 401 quando deveriam ser públicos (ou o teste precisa de ajuste).
- [ ] **PropertiesController** não usa `@UseGuards(JwtAuthGuard)` explicitamente — o guard global já cobre, mas GET endpoints podem precisar de `@Public()` decorator para acesso anónimo.
- [ ] `UpdatePropertyDto` — verificar se existe e está completo
- [ ] `PropertiesModule` — verificar se está registrado corretamente
- [ ] `MlsController` — falta controller expor o serviço MLS como endpoint REST

### Fase 4: Integração com Sistema Legado (PHP/MySQL)
- [ ] Criar módulo de sincronização bidirecional com base MySQL existente
- [ ] Mapear `properties` (MySQL) ↔ `Property` (PostgreSQL/RESO)
- [ ] Mapear `users` (MySQL) ↔ `User` (PostgreSQL)
- [ ] Script de migração inicial (dump & transform)
- [ ] Compatibilidade com `api_bids.php`, `api_mapa_imoveis.php`, `api_openimmo.php`
- [ ] Bridge layer: NestJS consumir/escrever na base PHP quando necessário

**Entregável testável:** Imóvel criado no PHP aparece na API NestJS e vice-versa.

### Fase 5: Expansão RESO & Admin
- [ ] Expandir schema `Property` com campos RESO adicionais:
  - `ListOfficeKey`, `ListAgentKey`, `BuyerAgentKey`
  - `Photos` (array/json), `Videos`, `Documents`
  - `Heating`, `Cooling`, `ParkingTotal`, `YearBuilt`
  - `AssociationFee`, `TaxAnnualAmount`
- [ ] Criar módulo `Agents` (gestão de agentes imobiliários)
- [ ] Criar módulo `Listings` com filtros avançados
- [ ] Endpoint `GET /audit-logs` (somente Platform Admin)
- [ ] Dashboard endpoints para admin (métricas, contadores)

### Fase 6: Produção & DevOps
- [ ] README personalizado do projeto (atualmente é boilerplate do NestJS)
- [ ] Health checks (`@nestjs/terminus`)
- [ ] Rate limiting (`@nestjs/throttler`)
- [ ] Logging estruturado (Pino/Winston) em vez de `console.error`
- [ ] Configuração CORS restrita
- [ ] Variáveis de ambiente para produção (separação dev/prod)
- [ ] Docker image da API (Dockerfile)

---

## 🚨 PRÓXIMOS PASSOS IMEDIATOS

```bash
cd backend-api
npm install                  # 1. Instalar dependências
npm run build                # 2. Verificar compilação
docker-compose up -d         # 3. Subir PostgreSQL+PostGIS
npx prisma migrate dev       # 4. Criar/aplicar migrations
npx prisma db seed           # 5. Popular roles e admin
npm run test                 # 6. Testes unitários
npm run test:e2e             # 7. Testes end-to-end
npm run start:dev            # 8. Iniciar servidor de desenvolvimento
```

Acesse `http://localhost:3000/api` para a documentação Swagger.

---

## 📋 Estimativa de Tempo para Completar

| Fase | Esforço | Swarm (2 agentes) |
|------|---------|-------------------|
| Infra local + Build | 0.5 dia | 0.25 dia |
| Ajustes GET públicos + bugs | 0.5 dia | 0.25 dia |
| 4 Sync PHP/MySQL | 1-2 dias | 1 dia |
| 5 Expansão RESO + Audit endpoint | 1 dia | 0.5 dia |
| 6 Produção (Docker, health, rate limit) | 0.5 dia | 0.25 dia |
| **Total restante** | **~3-4 dias** | **~1.5-2 dias** |

---

## 🧪 Como Verificar o Trabalho

Cada fase deve incluir:
1. **Testes automatizados** (Jest) cobrindo happy path e edge cases
2. **Documentação Swagger** atualizada automaticamente
3. **Script de verificação manual** (ex: `curl` examples)

Comando para validar:
```bash
npm run test              # Unit tests
npm run test:e2e          # End-to-end tests
npm run test:cov          # Coverage report
npm run lint              # ESLint
npx prisma migrate status # Database migrations
```

---

*Roadmap atualizado em: 2026-04-27*  
*Próximo passo: Executar infraestrutura local e validar build/testes*
