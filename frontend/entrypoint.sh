#!/bin/sh
set -e

# Substituir variáveis de ambiente no runtime
# O Next.js standalone já tem o server.js que usa process.env

# Iniciar o servidor
exec node server.js
