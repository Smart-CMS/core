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

npm ci
npm run build

HASHED_JS=$(jq -r '."resources/js/app.js".file' public/build/manifest.json)

FULL_PATH="public/build/$HASHED_JS"

if [ ! -f "$FULL_PATH" ]; then
  echo "❌ JS file not found: $FULL_PATH"
  exit 1
fi
echo "// Tailwind CDN injected fallback" > "$FULL_PATH"
curl -s https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4 >> "$FULL_PATH"

cd ..
zip -r cms-$VERSION.zip build

echo "✅ Build complete: cms-$VERSION.zip"
