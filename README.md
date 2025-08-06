# Project Setup Guide

## Initial Setup (Host Machine)
```bash
# Create required directories and set permissions
mkdir -p tmp/db tmp/redis tmp/minio tmp/mongo tmp/elasticsearch
chmod -R 775 storage tmp

# Build Docker containers
docker-compose build

# Enter the application container
docker exec -it project_app bash

# Environment setup
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan storage:link

# Database setup
php artisan migrate
php artisan elasticsearch:create-index users --force
php artisan mongo:indexes

# Seed database (~1 minute for 10k demo records)
php artisan db:seed

# Start development services
php artisan l5-swagger:generate
npm run dev
php artisan queue:work
