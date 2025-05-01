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

mkdir -p public/js
echo "// Tailwind CDN loader" > public/js/app.js
curl -s https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4 >> public/js/app.js
HASHED_JS=$(jq -r '."resources/js/app.js".file' public/build/manifest.json)
if [ ! -f "public/build/$HASHED_JS" ]; then
  echo "❌ Compiled JS not found at public/build/$HASHED_JS"
  exit 1
fi
echo "🔁 Replacing public/js/app.js with contents of $HASHED_JS"
cat "public/build/$HASHED_JS" > public/js/app.js


cd ..
zip -r cms-$VERSION.zip build

echo "✅ Build complete: cms-$VERSION.zip"
