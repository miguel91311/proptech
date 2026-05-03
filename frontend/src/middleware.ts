import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
    const { pathname } = request.nextUrl;

    // Proxy /api/* para o backend
    if (pathname.startsWith('/api/')) {
        let apiUrl = process.env.API_URL || 'http://localhost:3000';
        // Adicionar https:// se não tiver protocolo
        if (!apiUrl.startsWith('http://') && !apiUrl.startsWith('https://')) {
            apiUrl = 'https://' + apiUrl;
        }
        const newUrl = new URL(pathname.replace('/api', ''), apiUrl);

        // Copiar query params
        newUrl.search = request.nextUrl.search;

        // Criar headers com forwarding
        const headers = new Headers(request.headers);
        headers.set('X-Forwarded-Host', request.headers.get('host') || '');
        headers.set('X-Forwarded-Proto', 'https');

        return fetch(newUrl, {
            method: request.method,
            headers,
            body: request.body,
        });
    }

    return NextResponse.next();
}

export const config = {
    matcher: '/api/:path*',
};
