# taxCalc

Tax calculator application built with Laravel + Inertia + Vue.

## Recommended Runtime: Laravel Sail

Use Sail to avoid local PHP extension/version issues (for example missing `mbstring`).

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. Install PHP dependencies (if `vendor/` is missing):

```bash
composer install
```

2. Install frontend dependencies (if `node_modules/` is missing):

```bash
npm install
```

3. Copy env file (first time only):

```bash
cp .env.example .env
```

4. Start Sail:

```bash
./vendor/bin/sail up -d
```

5. Generate app key (first time only):

```bash
./vendor/bin/sail artisan key:generate
```

6. Run migrations:

```bash
./vendor/bin/sail artisan migrate
```

## Run Development Servers

Backend (Laravel via Sail):

```bash
# containers must be up
./vendor/bin/sail up -d
```

Frontend (Vite via Sail):

```bash
./vendor/bin/sail npm run dev
```

## Local URLs

- Laravel app: `http://localhost:8000`
- Vite dev server (Sail): `http://localhost:5174`

## Common Sail Commands

Start containers:

```bash
./vendor/bin/sail up -d
```

Stop containers:

```bash
./vendor/bin/sail down
```

Check running containers:

```bash
./vendor/bin/sail ps
```

Run Artisan commands:

```bash
./vendor/bin/sail artisan <command>
```

Run Composer commands:

```bash
./vendor/bin/sail composer <command>
```

Run NPM commands:

```bash
./vendor/bin/sail npm <command>
```

## Troubleshooting

If you see errors like:

- `Call to undefined function ... mb_split()`

Do not run with host PHP. Start and use Sail commands instead.
