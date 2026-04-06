# FinTrack — Installation Guide (Local Development)

Complete guide to get FinTrack running on your local machine **without Docker**.

> **Prefer Docker?** See [DOCKER.md](DOCKER.md) for a single-command setup that requires no local PHP, MySQL, or Node.js installation.

---

## Prerequisites

| Requirement | Version | Check Command |
|-------------|---------|---------------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL | 8.0+ | `mysql --version` |
| Node.js | 18+ | `node -v` |
| npm | 9+ | `npm -v` |

### Required PHP Extensions

The following PHP extensions must be enabled (most are included by default):

- `intl` — internationalization
- `mysqli` / `pdo_mysql` — MySQL connectivity
- `zip` — for Composer operations
- `mbstring` — multibyte string support
- `json` — JSON handling

Check with: `php -m | grep -i "intl\|mysqli\|pdo_mysql\|zip\|mbstring\|json"`

---

## Step 1: MySQL Setup

### Option A: Install MySQL natively (macOS)

```bash
# Install via Homebrew
brew install mysql

# Start MySQL service
brew services start mysql

# Secure the installation (set root password)
mysql_secure_installation
```

### Option B: Install MySQL natively (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install mysql-server
sudo systemctl start mysql
sudo mysql_secure_installation
```

### Option C: Use Docker for MySQL only

If you have Docker but want to run PHP and Node.js locally:

```bash
docker run -d \
  --name fintrack-mysql \
  -e MYSQL_ROOT_PASSWORD=root123 \
  -e MYSQL_DATABASE=fintrack_app \
  -e MYSQL_USER=fintrack \
  -e MYSQL_PASSWORD=fintrack_pass \
  -p 3306:3306 \
  mysql:8.0
```

### Create the database

Skip this step if you used Option C (Docker creates the database automatically).

```bash
# Connect to MySQL
mysql -u root -p

# Run these SQL commands:
CREATE DATABASE IF NOT EXISTS fintrack_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# (Optional) Create a dedicated user instead of using root:
CREATE USER 'fintrack'@'localhost' IDENTIFIED BY 'your_password_here';
GRANT ALL PRIVILEGES ON fintrack_app.* TO 'fintrack'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

### Update backend .env with your credentials

Open `backend/.env` and update the database section:

```env
database.default.hostname = localhost
database.default.database = fintrack_app
database.default.username = root
database.default.password = your_root_password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

If you created a dedicated user, use those credentials instead of root.

---

## Step 2: Backend Setup

```bash
cd backend

# Install PHP dependencies
composer install

# Run database migrations (creates all 14 tables)
php spark migrate

# Seed default data (admin user + 22 default categories)
php spark db:seed DatabaseSeeder

# Start the backend development server
php spark serve --port 8080
```

You should see: `CodeIgniter development server started on http://localhost:8080`

### Verify backend is running

Open a new terminal and run:

```bash
curl http://localhost:8080/api/v1/auth/login
# Should return a JSON response (even if it's an error about missing credentials)
```

> **Keep this terminal open** — the backend server needs to stay running.

---

## Step 3: Frontend Setup

Open a **new terminal** (keep the backend running in the first one):

```bash
cd frontend

# Install JavaScript dependencies
npm install

# Start the Vite development server
npm run dev
```

You should see: `Local: http://localhost:5173/`

The Vite dev server automatically proxies `/api` requests to `http://localhost:8080` (the backend).

---

## Step 4: Access the Application

1. Open **http://localhost:5173** in your browser
2. You'll be redirected to the login page
3. Login with the default admin account:
   - **Email:** `admin@fintrack.app`
   - **Password:** `Admin@1234`
4. You'll be prompted to change the password on first login

---

## Frontend Production Build

To create an optimized production build:

```bash
cd frontend
npm run build
```

The production-ready files will be in `frontend/dist/`. Serve them with any static file server (Nginx, Apache, etc.) and proxy `/api` requests to the PHP backend.

---

## Common Issues

### "Access denied for user 'root'@'localhost'"
Your MySQL root password in `backend/.env` doesn't match. Update `database.default.password`.

### "Table doesn't exist" errors
Run migrations: `cd backend && php spark migrate`

### Frontend shows "Network Error"
Make sure the backend is running on port 8080. The frontend Vite dev server proxies `/api` requests to `http://localhost:8080`.

### "Address already in use" on port 8080
Another process is using port 8080. Either stop it or use a different port:
```bash
php spark serve --port 8090
```
Then update `frontend/vite.config.js` proxy target to match.

### "Address already in use" on port 5173
Another Vite server is already running. Either stop it or:
```bash
npm run dev -- --port 5174
```

### MySQL "Can't connect to local MySQL server"
MySQL service isn't running:
```bash
# macOS
brew services start mysql

# Linux
sudo systemctl start mysql
```

### PHP extension missing errors
Install the required extension. For example on macOS:
```bash
# If using Homebrew PHP
brew install php@8.2
# Extensions like intl, zip are typically included

# On Ubuntu/Debian
sudo apt install php8.2-intl php8.2-zip php8.2-mysql php8.2-mbstring
```
