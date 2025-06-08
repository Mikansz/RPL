#!/bin/bash

echo "=========================================="
echo "  STEA Payroll System Installation"
echo "=========================================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP Version: $PHP_VERSION"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

echo "✅ Composer is installed"

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js first."
    exit 1
fi

NODE_VERSION=$(node -v)
echo "✅ Node.js Version: $NODE_VERSION"

echo ""
echo "🚀 Starting installation..."
echo ""

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Failed to install PHP dependencies"
    exit 1
fi

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ Failed to install Node.js dependencies"
    exit 1
fi

# Copy environment file
if [ ! -f .env ]; then
    echo "📝 Creating environment file..."
    cp .env.example .env
    echo "✅ Environment file created"
else
    echo "⚠️  Environment file already exists"
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Create storage directories
echo "📁 Creating storage directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p public/uploads/profiles

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public/uploads

echo ""
echo "⚠️  IMPORTANT: Please configure your database settings in .env file"
echo ""
echo "Database configuration example:"
echo "DB_CONNECTION=mysql"
echo "DB_HOST=127.0.0.1"
echo "DB_PORT=3306"
echo "DB_DATABASE=payroll_system"
echo "DB_USERNAME=root"
echo "DB_PASSWORD="
echo ""
echo "After configuring database, run:"
echo "php artisan migrate"
echo "php artisan db:seed"
echo ""
echo "To start development server:"
echo "php artisan serve"
echo ""
echo "✅ Installation completed!"
echo ""
echo "Demo accounts:"
echo "CEO: ceo.stea / password123"
echo "CFO: cfo.stea / password123"
echo "HRD: hrd.stea / password123"
echo "Personalia: personalia.stea / password123"
echo "Karyawan: john.doe / password123"
echo ""
echo "=========================================="
