#!/bin/bash
set -e

# Generate .env file from Docker environment variables
# This ensures Apache/PHP can read CI4 configuration
cat > /var/www/html/.env <<EOF
CI_ENVIRONMENT = ${CI_ENVIRONMENT:-production}

database.default.hostname = ${database_default_hostname:-mysql}
database.default.database = ${database_default_database:-fintrack_app}
database.default.username = ${database_default_username:-fintrack}
database.default.password = ${database_default_password:-fintrack_pass}
database.default.DBDriver = ${database_default_DBDriver:-MySQLi}
database.default.port = ${database_default_port:-3306}

jwt.secretKey = ${jwt_secretKey:-FinTrack_S3cur3_JWT_K3y_2026_Ch4ng3_Th1s_1n_Pr0d}
cors.allowedOrigins = ${cors_allowedOrigins:-http://localhost}
EOF

chown www-data:www-data /var/www/html/.env

exec apache2-foreground
