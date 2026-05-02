#!/bin/sh
set -e

# Substituir localhost:3000 pela URL real da API nos ficheiros compilados
find /app -type f -name "*.
cd "C:\xampp\htdocs\credito\frontend"

$entrypoint = @'
#!/bin/sh
set -e

# Substituir localhost:3000 pela URL real da API nos ficheiros compilados
find /app -type f -name "*.js" -exec sed -i "s|http://localhost:3000|${NEXT_PUBLIC_API_URL}|g" {} +
find /app -type f -name "*.html" -exec sed -i "s|http://localhost:3000|${NEXT_PUBLIC_API_URL}|g" {} +

# Iniciar o servidor
exec node server.js