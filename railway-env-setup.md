# Configuração de Variáveis de Ambiente no Railway

## Passos para configurar:

### 1. Obter o URL do Backend
No dashboard do Railway, clica no serviço "proptech-backend" e copia o URL público.
Deve ser algo como: `https://proptech-backend-production-XXXX.up.railway.app`

### 2. Configurar o Serviço "desirable-tranquility" (Admin)
1. Vai ao serviço "desirable-tranquility"
2. Clica em "Variables"
3. Adiciona nova variável:
   - Name: `API_URL`
   - Value: `https://proptech-backend-production-XXXX.up.railway.app` (substitui pelo URL real)
4. Clica em "Add"
5. O Railway vai fazer redeploy automaticamente

### 3. Configurar o Serviço "proptech" (Frontend)
1. Vai ao serviço "proptech"
2. Clica em "Variables"
3. Adiciona nova variável:
   - Name: `API_URL`
   - Value: `https://proptech-backend-production-XXXX.up.railway.app` (substitui pelo URL real)
4. Clica em "Add"
5. O Railway vai fazer redeploy automaticamente

### 4. Verificar
Depois do redeploy, testa:
- Frontend: https://proptech-production-XXXX.up.railway.app
- Admin: https://desirable-tranquility-production-XXXX.up.railway.app/login

O login deve funcionar agora!

## URLs esperados:
- Frontend: https://proptech-production-XXXX.up.railway.app
- Admin: https://desirable-tranquility-production-XXXX.up.railway.app
- Backend: https://proptech-backend-production-XXXX.up.railway.app

## Como funciona o proxy:
1. O browser faz POST para `/api/auth/login`
2. O middleware do Next.js intercepta `/api/*`
3. O middleware faz proxy para `${API_URL}/auth/login`
4. O backend recebe a chamada e responde
5. O middleware devolve a resposta ao browser
