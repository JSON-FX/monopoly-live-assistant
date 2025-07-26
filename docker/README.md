# Docker Development Environment

## Overview
This directory contains Docker configuration for the Monopoly Live Assistant development environment.

## Services
- **laravel**: PHP 8.3 container running Laravel application
- **frontend**: Node.js 20 container for React development
- **mysql**: MySQL 8.0 database
- **phpmyadmin**: Database management interface

## Security Configuration

⚠️ **Important**: The current docker-compose.yml contains example passwords for development convenience. For production or shared development environments:

1. Copy `docker/.env.docker` to `docker/.env.docker.local`
2. Update all passwords in the copied file
3. Modify docker-compose.yml to use environment variables from the file

## Quick Start

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Rebuild containers
docker-compose build --no-cache
```

## Service URLs
- Laravel API: http://localhost:8000
- React Frontend: http://localhost:5173
- phpMyAdmin: http://localhost:8080
- MySQL: localhost:3306

## Troubleshooting

### Container Build Issues
If you encounter build issues, try:
```bash
docker system prune -a
docker-compose build --no-cache
```

### Database Connection Issues
Ensure MySQL container is fully started before connecting:
```bash
docker-compose logs mysql
``` 
## 🚨 Laravel Valet Compatibility

**Important**: If you're using Laravel Valet (which is detected), the setup above uses **host MySQL** instead of Docker MySQL to avoid port conflicts.

### Valet + Host MySQL Setup (Current Configuration)
- Database runs on your host system (Homebrew MySQL)
- Laravel connects to `127.0.0.1:3306` (host MySQL)
- Docker MySQL is disabled to prevent conflicts
- Requires `monopoly_live` database on host MySQL

### To Use Docker MySQL Instead
1. Stop host MySQL: `brew services stop mysql`
2. Update docker-compose.yml to remove port conflicts
3. Update .env to use Docker database configuration

### Database Setup Commands
```bash
# Create database on host MySQL
mysql -u root -e "CREATE DATABASE IF NOT EXISTS monopoly_live;"

# Run Laravel migrations
php artisan migrate
```
