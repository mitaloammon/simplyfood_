# Docker para SimplyFood

## Visão geral

Esta infraestrutura substitui a execução local do backend Laravel e do banco MySQL por uma stack baseada em containers, preservando a arquitetura atual do projeto.

### Serviços disponíveis

- app: container PHP-FPM com o backend Laravel
- web: servidor Nginx para servir a aplicação
- mysql: banco de dados MySQL 8.4
- redis: cache/queue support
- node: build dos assets do frontend com Vite

## Pré-requisitos

- Docker Engine 24+
- Docker Compose v2

## Configuração inicial

1. Copie o arquivo de ambiente do backend:
   ```bash
   cp backend/.env.example backend/.env
   ```
2. Ajuste os valores conforme necessário para o ambiente Docker. Para o fluxo padrão, o compose já usa:
   - DB_HOST=mysql
   - DB_DATABASE=simplyfood
   - DB_USERNAME=simplyfood
   - DB_PASSWORD=simplyfood
   - REDIS_HOST=redis
3. Gere a chave da aplicação:
   ```bash
   docker compose run --rm app php artisan key:generate
   ```

## Inicialização

### Construir e subir a stack

```bash
docker compose up --build -d
```

### Verificar o status

```bash
docker compose ps
```

### Parar os containers

```bash
docker compose down
```

### Reiniciar os containers

```bash
docker compose restart
```

## Banco de dados

### Rodar migrations

```bash
docker compose exec app php artisan migrate
```

### Rodar seeders

```bash
docker compose exec app php artisan db:seed
```

### Criar o banco manualmente

O container MySQL já cria o banco configurado automaticamente na primeira inicialização.

## Composer

```bash
docker compose run --rm app composer install
```

## Artisan

Exemplos:

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan queue:work
docker compose exec app php artisan schedule:run
docker compose exec app php artisan test
```

## Frontend

### Instalar dependências

```bash
docker compose run --rm node npm install
```

### Build de produção

```bash
docker compose run --rm node npm run build
```

### Desenvolvimento

O fluxo padrão de desenvolvimento usa o build do frontend dentro do container. Para evoluir o frontend em modo de desenvolvimento, pode-se usar:

```bash
docker compose run --rm node npm run dev -- --host 0.0.0.0
```

## Logs

```bash
docker compose logs -f app
```

## Troubleshooting

### A aplicação não abre em http://localhost:8080

- Verifique se os containers subiram com `docker compose ps`
- Confirme se o Nginx está ouvindo a porta 8080
- Revise os logs com `docker compose logs -f`

### Erro de conexão com o banco

- Confirme se o serviço `mysql` está saudável
- Verifique as variáveis `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`

### Erro de permissões em storage/bootstrap/cache

```bash
docker compose exec app chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache
```
