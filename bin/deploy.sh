#!/bin/bash

# 1. Capture the first command line argument as the target host
TARGET_HOST=$1

# 2. Check if a host was provided; if not, show usage and exit
if [ -z "$TARGET_HOST" ]; then
    echo "Usage: $0 <target_host>"
    echo "Example: $0 prod30"
    exit 1
fi

# Exit immediately if any local command fails
set -e

echo "Starting deployment to: $TARGET_HOST..."

# 3. Connect via SSH and execute the commands on the remote server
# We use << 'EOF' to send a multi-line block of commands
ssh -i "$HOME/.ssh/github_actions_key" "git@$TARGET_HOST" << 'EOF'
    # The following commands run on the remote server
    set -e
    
    echo "Navigating to project directory..."
    cd /opt/guru-wisdom.de

    echo "Pulling latest changes from git..."
    git pull origin main

    echo "Setting environment variables..."
    echo "APP_ENV=prod" > .env
    chmod 600 .env

    echo "Building and restarting the 'app' container..."
    docker compose up -d --build app
    
    echo "Clearing application cache..."
    docker compose exec app rm -rf runtime/cache/*
    
    echo "Pruning unused Docker images..."
    docker image prune -f
EOF

echo "Deployment to $TARGET_HOST completed successfully!"