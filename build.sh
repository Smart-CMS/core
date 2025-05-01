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
touch database/database.sqlite
composer install --no-dev --optimize-autoloader
php artisan vendor:publish --tag=smart_cms.resources

cd ..
zip -r cms-$VERSION.zip build

echo "✅ Build complete: cms-$VERSION.zip"
