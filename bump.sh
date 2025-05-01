#!/bin/bash
set -e

# Usage: ./bump.sh patch|minor|major

VERSION_FILE="VERSION"
if [ ! -f "$VERSION_FILE" ]; then
  echo "0.1.0" > $VERSION_FILE
fi

CURRENT_VERSION=$(cat $VERSION_FILE)
IFS='.' read -r MAJOR MINOR PATCH <<< "$CURRENT_VERSION"

case "$1" in
  patch)
    PATCH=$((PATCH + 1))
    ;;
  minor)
    MINOR=$((MINOR + 1))
    PATCH=0
    ;;
  major)
    MAJOR=$((MAJOR + 1))
    MINOR=0
    PATCH=0
    ;;
  *)
    echo "Usage: ./bump.sh patch|minor|major"
    exit 1
    ;;
esac

NEW_VERSION="$MAJOR.$MINOR.$PATCH"
echo "$NEW_VERSION" > $VERSION_FILE

echo "🔖 New version: $NEW_VERSION"

# Commit and tag
git add $VERSION_FILE
git commit -m "Release v$NEW_VERSION"
git tag "v$NEW_VERSION"
git push origin master --tags

echo "✅ Tagged release v$NEW_VERSION"
