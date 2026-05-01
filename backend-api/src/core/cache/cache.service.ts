import { Injectable } from '@nestjs/common';

interface CacheEntry<T> {
    value: T;
    expiry: number;
}

@Injectable()
export class CacheService {
    private store = new Map<string, CacheEntry<any>>();

    get<T>(key: string): T | undefined {
        const entry = this.store.get(key);
        if (!entry) return undefined;
        if (Date.now() > entry.expiry) {
            this.store.delete(key);
            return undefined;
        }
        return entry.value as T;
    }

    set<T>(key: string, value: T, ttlSeconds = 300): void {
        this.store.set(key, { value, expiry: Date.now() + ttlSeconds * 1000 });
    }

    del(key: string): void {
        this.store.delete(key);
    }

    reset(): void {
        this.store.clear();
    }

    keys(): string[] {
        return Array.from(this.store.keys());
    }
}
