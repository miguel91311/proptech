const { createServer } = require('http');
const { parse } = require('url');
const next = require('next');

const dev = process.env.NODE_ENV !== 'production';
const hostname = process.env.HOSTNAME || '0.0.0.0';
const port = parseInt(process.env.PORT || '3000', 10);

const app = next({ dev, hostname, port });
const handle = app.getRequestHandler();

// URL do backend
let apiUrl = process.env.API_URL || 'http://localhost:3000';
if (!apiUrl.startsWith('http://') && !apiUrl.startsWith('https://')) {
    apiUrl = 'https://' + apiUrl;
}

app.prepare().then(() => {
    createServer(async (req, res) => {
        try {
            const parsedUrl = parse(req.url, true);
            const { pathname } = parsedUrl;

            // Proxy /api/* para o backend
            if (pathname && pathname.startsWith('/api/')) {
                const targetUrl = new URL(pathname.replace('/api', ''), apiUrl);
                targetUrl.search = parsedUrl.search || '';

                const headers = new Headers();
                // Copiar headers do request original
                for (const [key, value] of Object.entries(req.headers)) {
                    if (value && key !== 'host') {
                        headers.set(key, Array.isArray(value) ? value.join(', ') : value);
                    }
                }
                headers.set('X-Forwarded-Host', req.headers.host || '');
                headers.set('X-Forwarded-Proto', 'https');

                try {
                    const response = await fetch(targetUrl.toString(), {
                        method: req.method,
                        headers,
                        body: req.method !== 'GET' && req.method !== 'HEAD' ? req : undefined,
                    });

                    res.statusCode = response.status;
                    res.statusMessage = response.statusText;

                    // Copiar headers da resposta
                    response.headers.forEach((value, key) => {
                        if (key.toLowerCase() !== 'content-encoding') {
                            res.setHeader(key, value);
                        }
                    });

                    const body = await response.arrayBuffer();
                    res.end(Buffer.from(body));
                    return;
                } catch (error) {
                    console.error('Proxy error:', error);
                    res.statusCode = 502;
                    res.end(JSON.stringify({ error: 'Bad Gateway', message: error.message }));
                    return;
                }
            }

            // Todos os outros requests vão para o Next.js
            await handle(req, res, parsedUrl);
        } catch (err) {
            console.error('Error handling request:', err);
            res.statusCode = 500;
            res.end('Internal Server Error');
        }
    }).listen(port, hostname, () => {
        console.log(`> Ready on http://${hostname}:${port}`);
        console.log(`> API proxy to: ${apiUrl}`);
    });
});
