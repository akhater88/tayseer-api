#!/bin/bash

# Tayseer API Setup Script
# Run this after cloning/extracting the project

set -e

echo "🕌 Setting up Tayseer API..."

# Check PHP version
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
echo "✓ PHP Version: $PHP_VERSION"

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install

# Copy environment file
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Install Filament
echo "🎨 Installing Filament admin panel..."
php artisan filament:install --panels

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate

# Seed the database
echo "🌱 Seeding database..."
php artisan db:seed

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo ""
echo "✅ Setup complete!"
echo ""
echo "Next steps:"
echo "1. Update .env with your database credentials"
echo "2. Update .env with Infobip API credentials"
echo "3. Add firebase-credentials.json for Firebase"
echo "4. Run: php artisan make:filament-user (to create admin)"
echo "5. Run: php artisan serve"
echo ""
echo "Admin panel: http://localhost:8000/admin"
echo "API: http://localhost:8000/api/v1"
