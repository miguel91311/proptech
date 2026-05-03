const { createServer } = require('http');
const { parse } = require('url');
const fs = require('fs');
const path = require('path');

const hostname = process.env.HOSTNAME || '0.0.0.0';
const port = parseInt(process.env.PORT || '3000', 10);

// URL do backend
let apiUrl = process.env.API_URL || 'http://localhost:3000';
if (!apiUrl.startsWith('http://') && !apiUrl.startsWith('https://')) {
    apiUrl = 'https://' + apiUrl;
}

// Função para servir ficheiros estáticos
function serveStaticFile(res, filePath, contentType) {
    fs.readFile(filePath, (err, data) => {
        if (err) {
            res.statusCode = 404;
            res.end('Not Found');
            return;
        }
        res.setHeader('Content-Type', contentType);
        res.end(data);
    });
}

// Mapear extensões para content types
const mimeTypes = {
    '.html': 'text/html',
    '.js': 'application/javascript',
    '.css': 'text/css',
    '.json': 'application/json',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
    '.woff': 'font/woff',
    '.woff2': 'font/woff2',
    '.ttf': 'font/ttf',
};

createServer(async (req, res) => {
    try {
        const parsedUrl = parse(req.url, true);
        const { pathname } = parsedUrl;

        // Proxy /api/* para o backend
        if (pathname && pathname.startsWith('/api/')) {
            const targetUrl = new URL(pathname.replace('/api', ''), apiUrl);
            targetUrl.search = parsedUrl.search || '';

            const headers = {};
            // Copiar headers do request original
            for (const [key, value] of Object.entries(req.headers)) {
                if (value && key !== 'host') {
                    headers[key] = Array.isArray(value) ? value.join(', ') : value;
                }
            }
            headers['X-Forwarded-Host'] = req.headers.host || '';
            headers['X-Forwarded-Proto'] = 'https';

            // Ler o body se existir
            let body = null;
            if (req.method !== 'GET' && req.method !== 'HEAD') {
                const chunks = [];
                for await (const chunk of req) {
                    chunks.push(chunk);
                }
                body = Buffer.concat(chunks);
            }

            try {
                const response = await fetch(targetUrl.toString(), {
                    method: req.method,
                    headers,
                    body: body || undefined,
                });

                res.statusCode = response.status;

                // Copiar headers da resposta
                response.headers.forEach((value, key) => {
                    if (key.toLowerCase() !== 'content-encoding') {
                        res.setHeader(key, value);
                    }
                });

                const responseBody = await response.arrayBuffer();
                res.end(Buffer.from(responseBody));
                return;
            } catch (error) {
                console.error('Proxy error:', error);
                res.statusCode = 502;
                res.setHeader('Content-Type', 'application/json');
                res.end(JSON.stringify({ error: 'Bad Gateway', message: error.message }));
                return;
            }
        }

        // Servir ficheiros estáticos
        let filePath = pathname === '/' ? '/index.html' : pathname;
        const fullPath = path.join(__dirname, filePath);
        const ext = path.extname(fullPath).toLowerCase();
        const contentType = mimeTypes[ext] || 'application/octet-stream';

        // Verificar se o ficheiro existe
        fs.access(fullPath, fs.constants.F_OK, (err) => {
            if (err) {
                // Se não existir, tentar servir index.html (SPA behavior)
                const indexPath = path.join(__dirname, 'index.html');
                serveStaticFile(res, indexPath, 'text/html');
            } else {
                serveStaticFile(res, fullPath, contentType);
            }
        });
    } catch (err) {
        console.error('Error handling request:', err);
        res.statusCode = 500;
        res.end('Internal Server Error');
    }
}).listen(port, hostname, () => {
    console.log(`> Ready on http://${hostname}:${port}`);
    console.log(`> API proxy to: ${apiUrl}`);
});
