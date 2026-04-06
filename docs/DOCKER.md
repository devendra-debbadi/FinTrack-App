# FinTrack — Docker Guide

Run the entire FinTrack stack (MySQL + PHP Backend + Nginx Frontend) with a single command.

---

## Prerequisites

### Install Docker Desktop

1. Go to **https://www.docker.com/products/docker-desktop/**
2. Download Docker Desktop for your OS (macOS / Windows / Linux)
3. Install and launch it
4. Docker Desktop is **free for personal use**

### Verify installation

```bash
docker --version
# Docker version 24.x or higher

docker compose version
# Docker Compose version v2.x
```

### Stop local services (important!)

If you previously ran FinTrack locally, stop those services before starting Docker to avoid port conflicts:

```bash
# Stop local PHP dev server (if running)
# Go to the terminal running `php spark serve` and press Ctrl+C
# Or kill it directly:
lsof -ti :8080 | xargs kill 2>/dev/null

# Stop local MySQL (if running via Homebrew)
brew services stop mysql

# Stop local frontend dev server (if running)
# Go to the terminal running `npm run dev` and press Ctrl+C
```

> **Note:** Docker runs its own MySQL inside a container. You do NOT need a local MySQL installation when using Docker.

---

## Quick Start

From the project root (`fintrack-app/`):

```bash
# 1. Build and start all services (MySQL, Backend, Frontend)
docker compose up -d --build

# 2. Wait ~15-20 seconds for MySQL to initialize and become healthy
docker compose ps
# Verify fintrack-mysql shows "healthy" status before proceeding

# 3. Run database migrations (creates all 16 tables)
docker compose exec backend php spark migrate

# 4. Seed default data (admin user + 22 categories)
docker compose exec backend php spark db:seed DatabaseSeeder

# 5. Open the app
open http://localhost
```

Login with: `admin@fintrack.app` / `Admin@1234`

---

## Daily Usage — Stop & Start Safely

Your database data is stored in a Docker **named volume** (`mysql_data`). This volume persists independently of containers — stopping or removing containers does **not** delete your data.

### Stop the app (end of day / not needed)

```bash
docker compose stop
```

- Pauses all 3 containers (MySQL, Backend, Frontend)
- All your data remains intact in the volume
- Frees up memory and CPU on your machine

### Start the app again

```bash
docker compose start
```

- Resumes all containers from where they stopped
- No migrations or seeding needed — your data is still there
- App is available at **http://localhost** within a few seconds

### Check if the app is running

```bash
docker compose ps
```

All three services should show `running`:
```
fintrack-mysql     running (healthy)
fintrack-backend   running
fintrack-frontend  running
```

### Quick reference — what each command does to your data

| Command | Stops containers | Removes containers | Data safe? |
|---------|-----------------|-------------------|------------|
| `docker compose stop` | Yes | No | **Yes** |
| `docker compose start` | — | — | **Yes** |
| `docker compose restart` | Yes then starts | No | **Yes** |
| `docker compose up -d` | — | — | **Yes** |
| `docker compose up -d --build` | Yes then rebuilds | Yes then recreates | **Yes** |
| `docker compose down` | Yes | Yes | **Yes** (volume kept) |
| `docker compose down -v` | Yes | Yes | **NO — wipes all data** |

> Use `docker compose stop` / `start` for daily use.
> Use `docker compose up -d --build` only when you have code changes to deploy.
> **Never** run `docker compose down -v` unless you intentionally want to reset everything.

---

## What Docker Compose Creates

| Service | Container | Host Port | Internal Port | Description |
|---------|-----------|-----------|---------------|-------------|
| **mysql** | fintrack-mysql | 3307 | 3306 | MySQL 8.0 database |
| **backend** | fintrack-backend | 8081 | 80 | PHP 8.2 + Apache (CI4 REST API) |
| **frontend** | fintrack-frontend | 80 | 80 | Nginx serving Vue SPA + API proxy |

### How traffic flows

```
Browser → http://localhost (port 80, Nginx)
           ├── Static files → Vue 3 SPA (served from Nginx)
           └── /api/* → proxy to backend:80 (PHP Apache inside Docker network)
                              └── MySQL :3306 (Docker internal network)
```

- The **frontend Nginx** proxies all `/api/` requests to the backend container internally
- You access the app at **http://localhost** (port 80)
- Direct backend API access is available at **http://localhost:8081** (for debugging)
- MySQL is accessible externally at **localhost:3307** (for database tools like MySQL Workbench, DBeaver, etc.)

---

## Essential Commands

### Start / Stop / Restart

```bash
# Stop the app (safe — data is preserved)
docker compose stop

# Start the app again
docker compose start

# Restart all services
docker compose restart

# Restart a single service
docker compose restart backend

# Deploy code changes (rebuilds images, safe — data is preserved)
docker compose up -d --build
```

### Check Status

```bash
# See running containers and their health
docker compose ps

# Expected output:
# fintrack-mysql     mysql:8.0          running (healthy)   0.0.0.0:3307->3306/tcp
# fintrack-backend   fintrack-app-...   running             0.0.0.0:8081->80/tcp
# fintrack-frontend  fintrack-app-...   running             0.0.0.0:80->80/tcp
```

### View Logs

```bash
# All services (follow mode)
docker compose logs -f

# Specific service
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f mysql

# Last 50 lines of a service
docker compose logs --tail 50 backend
```

### Database Commands

```bash
# Run migrations
docker compose exec backend php spark migrate

# Run seeders
docker compose exec backend php spark db:seed DatabaseSeeder

# Rollback last migration batch
docker compose exec backend php spark migrate:rollback

# Check migration status
docker compose exec backend php spark migrate:status

# Access PHP Spark CLI
docker compose exec backend php spark

# Access MySQL CLI directly
docker compose exec mysql mysql -u fintrack -pfintrack_pass fintrack_app

# Run a SQL query directly
docker compose exec mysql mysql -u fintrack -pfintrack_pass fintrack_app -e "SELECT COUNT(*) FROM users;"
```

### Connect to Docker MySQL

```bash
# Via Docker CLI (recommended)
docker compose exec mysql mysql -u fintrack -pfintrack_pass fintrack_app

# Example: check tables
docker compose exec mysql mysql -u fintrack -pfintrack_pass fintrack_app -e "SHOW TABLES;"

# Example: check users
docker compose exec mysql mysql -u fintrack -pfintrack_pass fintrack_app -e "SELECT id, email, name, role FROM users;"
```

To connect via **external tools** (MySQL Workbench, DBeaver, TablePlus, etc.):

| Setting | Value |
|---------|-------|
| Host | `127.0.0.1` |
| Port | `3307` |
| Username | `fintrack` (or your `MYSQL_USER` from root `.env`) |
| Password | `fintrack_pass` (or your `MYSQL_PASSWORD` from root `.env`) |
| Database | `fintrack_app` (or your `MYSQL_DATABASE` from root `.env`) |

### Rebuild After Code Changes

The Docker images are **self-contained** (no volume mounts). Any code change requires a rebuild:

```bash
# Rebuild and restart — use this for all code updates
docker compose up -d --build

# Force clean rebuild (if you suspect Docker cache issues)
docker compose build --no-cache
docker compose up -d
```

> **Important:** Do NOT run `docker compose down` before a code rebuild. It removes containers unnecessarily and can disrupt your database volume. `up --build` safely stops, rebuilds, and restarts only what changed.

### Restore Default Categories (if lost)

The default categories belong to the `admin@fintrack.app` user. If you need to restore them:

```bash
# Only if your admin account email is admin@fintrack.app
docker compose exec backend php spark db:seed DefaultCategoriesSeeder
```

> **Note:** This seeder inserts without checking for duplicates. Only run it if the categories are missing — not if you already have categories.

### Full Reset (wipes all data)

```bash
# WARNING: deletes all users, transactions, and database data
docker compose down -v
docker compose up -d --build
docker compose exec backend php spark migrate
docker compose exec backend php spark db:seed DatabaseSeeder
```

After reset, login with: `admin@fintrack.app` / `Admin@1234`

---

## Configuration

### Environment Variables (Single Config File)

All Docker configuration is managed through **one file**: the root `.env` file.

```bash
# Copy the template
cp .env.example .env
```

```env
# Root .env — single source of truth for all Docker services
MYSQL_ROOT_PASSWORD=fintrack_root
MYSQL_DATABASE=fintrack_app
MYSQL_USER=fintrack
MYSQL_PASSWORD=fintrack_pass
JWT_SECRET=your-secret-here  # generate: openssl rand -hex 32
CORS_ALLOWED_ORIGINS=http://localhost
```

### Security — JWT Secret (Required Before Production)

The `.env.example` ships with a placeholder for `JWT_SECRET`. You **must** replace it with a real secret before exposing the app to the internet or sharing access.

Generate one with:

```bash
openssl rand -hex 32
```

Paste the output into your root `.env`:

```env
JWT_SECRET=<paste-your-generated-value-here>
```

> **Why this matters:** The JWT secret signs authentication tokens. If someone knows your secret, they can forge tokens and bypass login entirely. The placeholder is intentionally obvious — replace it before going live.

**For local development only** (without Docker), set the same secret in `backend/.env`:

```env
jwt.secretKey = 'your-generated-secret-here'
```

**How it works:**
```
root/.env → docker-compose.yml → Docker env vars
                                    ├── MySQL service (creates DB + user)
                                    └── Backend entrypoint → auto-generates backend/.env inside container
```

- `docker-compose.yml` reads variables from root `.env` (Docker Compose does this automatically)
- The same credentials flow to both MySQL (to create the database/user) and backend (to connect)
- The backend `docker-entrypoint.sh` generates `backend/.env` inside the container at startup — this is a standard Docker pattern used by WordPress, Laravel Sail, etc.
- You do NOT need to edit `backend/.env` for Docker — it's only used for local development

> **Important:** `backend/.env` is for **local development only** (when running without Docker). Docker ignores it via `.dockerignore`.

### Changing Ports

Edit `docker-compose.yml` if you need different ports:

```yaml
services:
  frontend:
    ports:
      - "3000:80"    # Change 80 to your preferred port
  backend:
    ports:
      - "9090:80"    # Change 8081 to your preferred port
  mysql:
    ports:
      - "3308:3306"  # Change 3307 to your preferred port
```

If you change the frontend port, update `CORS_ALLOWED_ORIGINS` in your root `.env` to match (e.g., `CORS_ALLOWED_ORIGINS=http://localhost:3000`).

---

## Docker Cheatsheet

### Daily Workflow

| Action | Command |
|--------|---------|
| Start the app | `docker compose up -d` |
| Stop the app | `docker compose stop` |
| Restart the app | `docker compose restart` |
| View status | `docker compose ps` |
| View logs | `docker compose logs -f` |
| Open the app | `open http://localhost` |

### After Code Changes

| Changed | Command |
|---------|---------|
| Backend (PHP) code | `docker compose up -d --build backend` |
| Frontend (Vue/JS) code | `docker compose up -d --build frontend` |
| docker-compose.yml | `docker compose up -d` |
| Everything (clean) | `docker compose build --no-cache && docker compose up -d` |

### Database Operations

| Action | Command |
|--------|---------|
| Run migrations | `docker compose exec backend php spark migrate` |
| Seed data | `docker compose exec backend php spark db:seed DatabaseSeeder` |
| Rollback migrations | `docker compose exec backend php spark migrate:rollback` |
| MySQL CLI | `docker compose exec mysql mysql -u fintrack -pfintrack_pass fintrack_app` |

### Cleanup

| Action | Command |
|--------|---------|
| Remove containers (keep data) | `docker compose down` |
| Remove containers + data | `docker compose down -v` |
| Remove unused images | `docker image prune` |
| Remove everything unused | `docker system prune -a` |

---

## Development vs Production

### For Development (faster iteration)

Use the **local setup** (no Docker) for hot-reloading:
- Backend: `php spark serve --port 8080` (restart to pick up changes)
- Frontend: `npm run dev` (hot module replacement, instant updates)

See [INSTALL.md](INSTALL.md) for local setup instructions.

### For Production / Demo

Use Docker:
```bash
docker compose up -d --build
```

The frontend is pre-built and served via Nginx with gzip compression and static asset caching (1 year for JS/CSS/images).

---

## Troubleshooting

### "Cannot connect to the Docker daemon"
Docker Desktop isn't running. Open it from your Applications folder.

### "port is already allocated"
Another service is using the port. Find and stop it:

```bash
# Find what's using port 80
lsof -i :80

# Find what's using port 8081
lsof -i :8081

# Find what's using port 3307
lsof -i :3307
```

Common culprit: local MySQL running on port 3306 (Docker MySQL uses 3307 to avoid this).

### MySQL container keeps restarting
```bash
docker compose logs mysql
```
Common causes: insufficient disk space, permission issues, or corrupted data volume. Try:
```bash
docker compose down -v   # removes the data volume
docker compose up -d     # fresh start
```

### Backend returns 500 errors
```bash
# Check backend logs for PHP errors
docker compose logs backend

# Common fix: migrations haven't been run
docker compose exec backend php spark migrate
```

### Frontend shows blank page
```bash
# Check frontend logs
docker compose logs frontend

# Rebuild the frontend
docker compose build frontend
docker compose up -d frontend
```

### "Table doesn't exist" errors
Migrations haven't been run yet:
```bash
docker compose exec backend php spark migrate
docker compose exec backend php spark db:seed DatabaseSeeder
```

### "Unable to connect to server" on login
The backend cannot reach MySQL, or the `.env` file is misconfigured. Check:
```bash
# Verify the entrypoint generated .env correctly
docker compose exec backend cat /var/www/html/.env

# Verify MySQL is healthy
docker compose ps

# Rebuild backend if needed (picks up entrypoint changes)
docker compose up -d --build backend
```

### CORS errors in browser console
The `cors.allowedOrigins` environment variable in `docker-compose.yml` must match the URL you're accessing the app from. Default is `http://localhost`.

### Reset everything and start fresh

```bash
docker compose down -v
docker compose build --no-cache
docker compose up -d
# Wait for MySQL to be healthy:
docker compose ps
# Then run migrations and seed:
docker compose exec backend php spark migrate
docker compose exec backend php spark db:seed DatabaseSeeder
```
