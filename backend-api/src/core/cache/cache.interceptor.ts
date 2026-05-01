import {
    Injectable,
    NestInterceptor,
    ExecutionContext,
    CallHandler,
} from '@nestjs/common';
import { Observable, of } from 'rxjs';
import { tap } from 'rxjs/operators';
import { CacheService } from './cache.service';

@Injectable()
export class CacheInterceptor implements NestInterceptor {
    constructor(private readonly cache: CacheService) { }

    intercept(context: ExecutionContext, next: CallHandler): Observable<any> {
        const request = context.switchToHttp().getRequest();
        const key = `cache:${request.method}:${request.url}`;

        const cached = this.cache.get<any>(key);
        if (cached) {
            return of(cached);
        }

        return next.handle().pipe(
            tap((response) => {
                this.cache.set(key, response, 60); // 60s cache para GETs
            }),
        );
    }
}
