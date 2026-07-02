- Inertia & React (this project) version: **[github.com/nunomaduro/laravel-starter-kit-inertia-react](https://github.com/nunomaduro/laravel-starter-kit-inertia-react)**
- Blade version: **[github.com/nunomaduro/laravel-starter-kit](https://github.com/nunomaduro/laravel-starter-kit)**
- Inertia & Vue version: **[github.com/nunomaduro/laravel-starter-kit-inertia-vue](https://github.com/nunomaduro/laravel-starter-kit-inertia-vue)**

<p align="center">
    <a href="https://youtu.be/VhzP0XWGTC4" target="_blank">
        <img src="https://github.com/nunomaduro/laravel-starter-kit/blob/main/art/banner.png" alt="Overview Laravel Starter Kit" style="width:70%;">
    </a>
</p>

<p>
    <a href="https://github.com/nunomaduro/laravel-starter-kit-inertia-react/actions"><img src="https://github.com/nunomaduro/laravel-starter-kit-inertia-react/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/nunomaduro/laravel-starter-kit-inertia-react"><img src="https://img.shields.io/packagist/dt/nunomaduro/laravel-starter-kit-inertia-react" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/nunomaduro/laravel-starter-kit-inertia-react"><img src="https://img.shields.io/packagist/v/nunomaduro/laravel-starter-kit-inertia-react" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/nunomaduro/laravel-starter-kit-inertia-react"><img src="https://img.shields.io/packagist/l/nunomaduro/laravel-starter-kit-inertia-react" alt="License"></a>
    <a href="https://youtube.com/@nunomaduro?sub_confirmation=1"><img alt="YouTube Channel Subscribers" src="https://img.shields.io/youtube/channel/subscribers/UCO_hYZF2gb_CyG5sA7ArlGg?style=flat&label=youtube&color=brightgreen"></a>
</p>

**Laravel Starter Kit (Inertia & React)** is an ultra-strict, type-safe [Laravel](https://laravel.com) skeleton engineered for developers who refuse to compromise on code quality. This opinionated starter kit enforces rigorous development standards through meticulous tooling configuration and architectural decisions that prioritize type safety, immutability, and fail-fast principles.

## Why This Starter Kit?

Modern PHP has evolved into a mature, type-safe language, yet many Laravel projects still operate with loose conventions and optional typing. This starter kit changes that paradigm by enforcing:

- **Fully Actions-Oriented Architecture**: Every operation is encapsulated in a single-action class
- **Cruddy by Design**: Standardized CRUD operations for all controllers, actions, and Inertia & React pages
- **100% Type Coverage**: Every method, property, and parameter is explicitly typed
- **Zero Tolerance for Code Smells**: Rector, PHPStan, OxLint, and Oxfmt at maximum strictness catch issues before they become bugs
- **Immutable-First Architecture**: Data structures favor immutability to prevent unexpected mutations
- **Fail-Fast Philosophy**: Errors are caught at compile-time, not runtime
- **Automated Code Quality**: Pre-configured tools ensure consistent, pristine code across your entire team
- **Just Better Laravel Defaults**: Thanks to **[Essentials](https://github.com/nunomaduro/essentials)** / strict models, auto eager loading, immutable dates, and more...
- **AI Guidelines**: Integrated AI Guidelines to assist in maintaining code quality and consistency
- **Full Testing Suite**: More than 150 tests with 100% code coverage using Pest
- 
This isn't just another Laravel boilerplate—it's a statement that PHP applications can and should be built with the same rigor as strongly-typed languages like Rust or TypeScript.

## Getting Started

> **Requires [PHP 8.5+](https://php.net/releases/) and a code coverage driver like [xdebug](https://xdebug.org/docs/install)**.

Create your type-safe Laravel application using [Composer](https://getcomposer.org):

```bash
composer create-project nunomaduro/laravel-starter-kit-inertia-react --prefer-dist example-app
```

### Initial Setup

Navigate to your project and complete the setup:

```bash
cd example-app

# Setup the project
composer setup

# Start the development server
composer dev
```

### Optional: Browser Testing Setup

If you plan to use Pest's browser testing capabilities:

```bash
bun add playwright
bunx playwright install
```

### Verify Installation

Run the test suite to ensure everything is configured correctly:

```bash
composer test
```

You should see 100% test coverage and all quality checks passing.

## Docker + Horizon

This project uses [Laravel Horizon](https://laravel.com/docs/horizon) with Redis queues, [Laravel Reverb](https://laravel.com/docs/reverb) for real-time WebSockets, and [Laravel Pulse](https://laravel.com/docs/pulse) for monitoring in Docker and Dokploy deployments.

### Stack local

```bash
cp .env.example .env
# Descomente a seção Docker Compose no .env (DB_HOST=postgres, REDIS_HOST=redis, etc.)

docker compose up --build
```

Variáveis necessárias no `.env`:

- `QUEUE_CONNECTION=redis`
- `REDIS_HOST=redis` (no compose local) ou `127.0.0.1` (Redis local)
- `HORIZON_ALLOWED_EMAILS=` e `PULSE_ALLOWED_EMAILS=` **vazios em local** (dashboards abertos; em Dokploy, preencha com e-mails)
- `PULSE_DB_CONNECTION=pulse` + `PULSE_DB_DATABASE=laravel_pulse` (banco separado para métricas)
- `BROADCAST_CONNECTION=reverb` + variáveis `REVERB_*` / `REVERB_CLIENT_*` (ver `.env.example`)

### Serviços

| Serviço | Função |
|---------|--------|
| `web` | HTTP (FrankenPHP) — dashboards em `/horizon` e `/pulse` |
| `worker` | `php artisan horizon` — processa jobs Redis |
| `cron` | `php artisan schedule:work` — inclui `horizon:snapshot` a cada 5 min |
| `reverb` | `php artisan reverb:start` — WebSocket em `:8081` (local) |
| `pulse-check` | `php artisan pulse:check` — métricas Reverb no Pulse |
| `redis` | Broker de filas, scaling Reverb e métricas |
| `postgres` | Banco da aplicação (`laravel`) + init do banco Pulse (`laravel_pulse`) |

### Banco separado do Pulse

Métricas do Pulse ficam em **`laravel_pulse`**, separadas do banco da app (`laravel`). A migration `create_pulse_tables` usa a conexão `PULSE_DB_CONNECTION` (via `PulseMigration`).

- **Local (Docker):** o script `docker/postgres/init-pulse-db.sh` cria `laravel_pulse` na primeira subida do volume Postgres.
- **Migrate:** um único `php artisan migrate` no container `web` migra app + Pulse (conexões diferentes).
- **CI (GHA):** `phpunit.xml` define `PULSE_DB_CONNECTION=""` — testes usam SQLite `:memory:` sem Postgres.
- **Volume Postgres já existente:** crie o banco manualmente antes do deploy:

```sql
CREATE DATABASE laravel_pulse OWNER laravel;
```

Se o Pulse já rodou no banco `laravel`, após criar `laravel_pulse` e configurar as env vars, remova as tabelas antigas:

```sql
-- no banco laravel
DROP TABLE IF EXISTS pulse_aggregates, pulse_entries, pulse_values;
DELETE FROM migrations WHERE migration LIKE '%create_pulse_tables%';
```

### Reações de emoji (Reverb)

A página inicial (`/`) inclui reações de emoji em tempo real. Visitantes enviam `POST /reactions`; o broadcast usa Reverb no canal público `reactions`.

Localmente, após `docker compose up --build`:

- Web: [http://localhost:8080](http://localhost:8080)
- Reverb: `ws://localhost:8081/app` (configure `REVERB_CLIENT_HOST=localhost`, `REVERB_CLIENT_PORT=8081`)

### Dokploy (Reverb no mesmo domínio)

Na aba **Domains** do serviço compose, além do serviço `web` (porta 8080), adicione duas entradas no **mesmo host** apontando para o serviço `reverb` (porta 8080):

- path `/app` — WebSocket dos clientes
- path `/apps` — API interna do Reverb

Na aba **Environment**:

- `BROADCAST_CONNECTION=reverb`
- `REVERB_HOST=reverb`, `REVERB_PORT=8080`, `REVERB_SCHEME=http` (web → reverb na rede interna)
- `REVERB_CLIENT_HOST=<domínio>`, `REVERB_CLIENT_PORT=443`, `REVERB_CLIENT_SCHEME=https` (navegador → Traefik)
- `REVERB_SCALING_ENABLED=true` (Redis do ambiente)
- `PULSE_ALLOWED_EMAILS=<emails admin>`
- `PULSE_DB_CONNECTION=pulse`
- `PULSE_DB_DATABASE=laravel_pulse` (crie o database no Postgres do ambiente antes do deploy)

### Dashboard e job de teste

- Horizon: [http://localhost:8080/horizon](http://localhost:8080/horizon) — login Fortify + e-mail em `HORIZON_ALLOWED_EMAILS`
- Pulse: [http://localhost:8080/pulse](http://localhost:8080/pulse) — login Fortify + e-mail em `PULSE_ALLOWED_EMAILS` (local sem lista = aberto)
- Job de teste: `GET /job` despacha um job processado pelo container `worker`

### Verificação no container

Suite completa dentro do stack Docker:

```bash
docker compose exec web composer test
```

Alternativa equivalente ao CI (sem stack em execução):

```bash
# Suite completa (1ª vez ~2 min; depois ~1 min com cache)
./scripts/composer-test-docker.sh

# Só testes unitários + coverage (~30s)
./scripts/composer-test-docker.sh unit

# Só PHPStan + types TS
./scripts/composer-test-docker.sh types

# Rebuild da imagem CI (PHP 8.5 + xdebug + bun)
./scripts/composer-test-docker.sh rebuild-image
```

A imagem `laravel-starter-kit-ci:local` (`Dockerfile.ci`) evita reinstalar apt/pecl/xdebug a cada run. O container `web` **não** serve para testes — usa `--no-dev` (sem Pest/PHPStan).

### Lições aprendidas (Reverb + Pulse)

Checklist para evitar problemas em uma **nova instalação** ou ao replicar este stack:

#### Reverb / Echo (tempo real)

| Problema | Sintoma | Solução |
|----------|---------|---------|
| `wsPath` no Echo | WebSocket em `/app/app/...`, conexão falha | **Não** definir `wsPath` em `echo.ts` — o Pusher já usa `/app` |
| Nome do evento | `POST /reactions` retorna 204, mas outro browser não reage | Evento precisa de `broadcastAs(): 'EmojiReactionSent'` — o Echo escuta `.EmojiReactionSent`, não `App\Events\...` |
| `toOthers()` | Emoji duplica no cliente que clicou | Enviar header `X-Socket-ID` no `fetch` com `echo.socketId()` |
| JS em cache | Correção no frontend não aparece | Hard refresh (`Ctrl+Shift+R`) ou rebuild: `docker compose build web` |
| Porta errada no browser | WS tenta `:8080` em vez de Reverb | Local: `REVERB_CLIENT_HOST=localhost`, `REVERB_CLIENT_PORT=8081` (não confundir com `REVERB_HOST=reverb` interno) |

Verificação rápida no DevTools → Network → WS: deve conectar em `ws://localhost:8081/app/<REVERB_APP_KEY>` com status **101**.

#### Pulse (`/pulse`)

| Problema | Sintoma | Solução |
|----------|---------|---------|
| E-mails preenchidos em local | 403 *This action is unauthorized* | Deixe `PULSE_ALLOWED_EMAILS=` vazio com `APP_ENV=local`; preencha só em staging/production |
| Laravel 13 + cache | Erro *incomplete object* `Illuminate\Support\Collection` no poll Livewire | `config/cache.php` → `serializable_classes` deve incluir `Collection`, `CarbonImmutable`, `stdClass` |
| Sem `pulse-check` | Cards Reverb vazios ou incompletos | Subir o serviço `pulse-check` (`php artisan pulse:check`) |
| Banco Pulse inexistente | Migrate falha ou Pulse sem tabelas | Criar `laravel_pulse` (init script só roda na **primeira** subida do volume Postgres) |
| Pulse no banco errado | Tabelas `pulse_*` em `laravel` | Dropar tabelas antigas no banco app, limpar linha em `migrations`, rodar `migrate` de novo |
| CI quebra com `PULSE_DB_CONNECTION=pulse` | GHA tenta Postgres inexistente | `phpunit.xml` deve forçar `PULSE_DB_CONNECTION=""` nos testes |

O dashboard `/pulse` usa **Livewire internamente** (pacote Laravel Pulse) — isso é normal; a aplicação continua Inertia + React + Wayfinder.

#### Docker Compose

| Problema | Sintoma | Solução |
|----------|---------|---------|
| Vars `DB_*` ausentes no compose | Container `web` não sobe | Defaults `${DB_DATABASE:-laravel}` etc. no `docker-compose.yml` |
| Imagem desatualizada | Backend ok, frontend antigo | `docker compose build web && docker compose up -d web` após mudanças em JS/config |
| Extensão PHP | Reverb instável | `PHP_EXTENSIONS="sockets"` no `Dockerfile` |

#### Dokploy

- Um único `php artisan migrate` no start do `web` migra **app + Pulse** (conexões diferentes) — não precisa job separado.
- Crie `laravel_pulse` no Postgres **antes** do primeiro deploy com `PULSE_DB_CONNECTION=pulse`.
- Domains: serviço `web` (:8080) + serviço `reverb` com paths `/app` e `/apps` no mesmo host.
- `REVERB_CLIENT_*` aponta para o domínio público (443/https); `REVERB_HOST=reverb` é rede interna do stack.

## Available Tooling

### Development
- `composer dev` - Starts Laravel server, Horizon, log monitoring, and Vite dev server concurrently

### Code Quality
- `composer lint` - Runs Rector (refactoring), Pint (PHP formatting), and Oxfmt (JS/TS formatting)
- `composer test:lint` - Dry-run mode for CI/CD pipelines

### Testing
- `composer test:type-coverage` - Ensures 100% type coverage with Pest
- `composer test:types` - Runs PHPStan at level 9 (maximum strictness)
- `composer test:unit` - Runs Pest tests with 100% code coverage requirement
- `composer test` - Runs the complete test suite (type coverage, unit tests, linting, static analysis)

### Maintenance
- `composer update:requirements` - Updates all PHP and Bun dependencies to latest versions

## License

**Laravel Starter Kit Inertia React** was created by **[Nuno Maduro](https://x.com/enunomaduro)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
