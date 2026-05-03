import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: 'standalone',
  images: {
    unoptimized: true,
  },
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: 'https://backend-api-production-2f57.up.railway.app/:path*',
      },
    ];
  },
};

export default nextConfig;
