# Exemplos de log de erro

Arquivos fictícios para treinar leitura. Não são logs de uma instância real.

- `laravel-error.log` — Monolog (`storage/logs/laravel.log`)
- `php-fpm-error.log` — stdout do container `app`

## Como ler

1. Timestamp
2. Nível: `ERROR`, `WARNING`, `ALERT`
3. Mensagem curta (`SQLSTATE`, `Permission denied`, `Class ... not found`)
4. Arquivo da aplicação (`app/...`), não só `vendor/`

## Mapa rápido

| Trecho no log | Ação |
| --- | --- |
| `SQLSTATE[HY000] [2002] Connection refused` | Esperar MySQL healthy; conferir `DB_HOST=mysql` |
| `No application encryption key` | `docker compose exec app php artisan key:generate` |
| `SQLSTATE[45000]` + texto do trigger | Dado viola regra do SPEC (ex.: pedido sem caixa aberto) |
| `Permission denied` em `storage/` | Sair de `/mnt/c`; `./scripts/wsl-up.sh`; conferir `HOST_UID` |
| `Class "Redis" not found` | Rebuild da imagem PHP (`ext-redis`) |
| `FPM initialization failed` | UID/GID inválido no pool `www` |

Coleta real:

```bash
./scripts/logs-php.sh last
```
