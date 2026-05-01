import { Injectable, Logger } from '@nestjs/common';
import { HttpService } from '@nestjs/axios';
import { catchError, firstValueFrom } from 'rxjs';
import CircuitBreaker from 'opossum';

@Injectable()
export class MlsService {
  private readonly logger = new Logger(MlsService.name);
  private circuitBreaker: CircuitBreaker<[string], any>;

  constructor(private readonly httpService: HttpService) {
    const circuitBreakerOptions = {
      timeout: 3000, // Tempo máximo de espera da requisição (3 segundos)
      errorThresholdPercentage: 50, // Se 50% das requisições falharem, o circuito abre
      resetTimeout: 10000, // Tenta fechar o circuito novamente após 10 segundos
    };

    // Envolvemos a chamada externa no Circuit Breaker
    this.circuitBreaker = new CircuitBreaker(
      this.fetchFromMlsApi.bind(this),
      circuitBreakerOptions,
    );

    // Eventos de log para monitoramento
    this.circuitBreaker.on('open', () =>
      this.logger.warn(
        'Circuit Breaker ABERTO: MLS Provider instável! Redirecionando para fallback.',
      ),
    );
    this.circuitBreaker.on('halfOpen', () =>
      this.logger.log(
        'Circuit Breaker MEIO ABERTO: Testando estabilidade do MLS...',
      ),
    );
    this.circuitBreaker.on('close', () =>
      this.logger.log(
        'Circuit Breaker FECHADO: Conexão com MLS restabelecida.',
      ),
    );

    // Fallback caso a chamada falhe ou o circuito esteja aberto
    this.circuitBreaker.fallback((listingKey: string) => {
      this.logger.warn(
        `Fallback acionado para o imóvel ${listingKey}. Retornando dados em cache ou erro amigável.`,
      );
      return {
        status: 'error',
        message:
          'Serviço de MLS temporariamente indisponível. A apresentar dados armazenados em cache se disponíveis.',
        cachedData: true,
        listingKey,
      };
    });
  }

  // O método real que simula a chamada à API do MLS Externo
  private async fetchFromMlsApi(listingKey: string): Promise<any> {
    this.logger.debug(
      `Buscando imóvel ${listingKey} no provedor MLS externo...`,
    );

    // Simulação de chamada HTTP externa que pode falhar
    const { data } = await firstValueFrom(
      this.httpService
        .get(`https://api.mock-mls-provider.com/properties/${listingKey}`)
        .pipe(
          catchError((error) => {
            this.logger.error(`Falha na requisição: ${error.message}`);
            throw error; // Lança o erro para que o Circuit Breaker o intercete
          }),
        ),
    );
    return data;
  }

  // Método público utilizado pelo nosso Controller/Service
  public async syncPropertyStatus(listingKey: string) {
    return this.circuitBreaker.fire(listingKey);
  }
}
