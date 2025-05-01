#!/bin/bash
set -e

VERSION=$1

echo "🔧 Building CMS version $VERSION..."

rm -rf build cms-$VERSION.zip

mkdir build
rsync -a . build/ \
    --exclude=node_modules \
    --exclude=build \
    --exclude=.git \
    --exclude=*.zip \
    --exclude=storage/logs \
    --exclude=.env

cd build

composer install --no-dev --optimize-autoloader

cp .env.example .env
php artisan config:cache
php artisan route:cache

cd ..
zip -r cms-$VERSION.zip build

echo "✅ Build complete: cms-$VERSION.zip"
