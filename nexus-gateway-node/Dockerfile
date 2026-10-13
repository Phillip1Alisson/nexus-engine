# === Estágio 1: Compilação (Build) ===
FROM node:22-alpine AS builder

WORKDIR /usr/src/app

# Copia os manifestos para instalar as dependências primeiro (Aproveitamento de Cache do Docker)
COPY package*.json ./

RUN npm ci

# Copia o restante do código fonte e compila o TypeScript para JavaScript nativo
COPY . .
RUN npm run lint
# Caso tenha configurado o build do NestJS: RUN npm run build

# === Estágio 2: Execução em Produção ===
FROM node:22-alpine AS runner

WORKDIR /usr/src/app

ENV NODE_ENV=production

# Copia apenas os manifestos de pacotes
COPY package*.json ./

# Instala estritamente as dependências necessárias para rodar o app, ignorando devDependencies
RUN npm ci --only=production

# Copia os arquivos compilados e estruturais do estágio anterior
COPY --from=builder /usr/src/app/src ./src
COPY --from=builder /usr/src/app/tsconfig.json ./

EXPOSE 3000

CMD ["node", "src/main.js"]
