# Frontend Dockerfile - Static export
FROM node:22-alpine AS builder

WORKDIR /app
COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# Production image
FROM node:22-alpine

RUN npm install -g serve

COPY --from=builder /app/dist /app/dist
COPY --from=builder /app/public /app/public

EXPOSE ${PORT:-3000}

CMD ["sh", "-c", "serve /app/dist -l ${PORT:-3000} --no-clipboard"]
