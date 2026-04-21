#!/bin/zsh

# 1. Check if Docker is running
if ! docker info >/dev/null 2>&1; then
    echo "❌ Error: Docker Desktop is not running. Please start Docker first."
    exit 1
fi

# 2. Stop and Remove ALL containers
# This fixes the "Ghost Container" and "Port already in use" issues
CONTAINERS=$(docker ps -aq)
if [ -n "$CONTAINERS" ]; then
    echo "🛑 Stopping and removing all containers..."
    docker stop $CONTAINERS > /dev/null 2>&1
    docker rm $CONTAINERS > /dev/null 2>&1
    echo "✅ Containers cleared."
fi

# 3. Deep Clean (The "History Killer")
echo "🧹 Deep cleaning Docker system (this might take a moment)..."
docker image prune -f > /dev/null
docker network prune -f > /dev/null
# This clears the build list you saw in the Dashboard
docker builder prune -a -f > /dev/null
echo "✅ Build cache and orphaned images removed."

# 4. Start the project
DEV_FILE="docker-compose.dev.yml"

if [ -f "$DEV_FILE" ]; then
    echo "🚀 Building and starting project: $DEV_FILE"
    # We use --build to apply Dockerfile changes. 
    # No-cache isn't needed here because builder prune -a already cleared everything.
    docker compose -f $DEV_FILE up -d --build
    
    echo "\n✨ Success! Your environment is fresh and clean."
    echo "📊 Container Status:"
    docker compose -f $DEV_FILE ps
else
    echo "❌ Error: File '$DEV_FILE' not found in this folder."
    echo "Current folder content:"
    ls -F
    exit 1
fi