#!/bin/bash

# 1. Capture the first command line argument
INPUT=$1

# 2. Check if an argument was provided
if [ -z "$INPUT" ]; then
    echo "Usage: $0 <environment_or_host>"
    echo "Environments: test, prod"
    echo "Hosts:        test20, prod30, prod130"
    echo "Example:      $0 prod    (Deploys to all prod servers)"
    echo "Example:      $0 prod30  (Deploys ONLY to prod30)"
    exit 1
fi

# 3. Determine target hosts and the environment variable based on input
case "$INPUT" in
    test|test20)
        ENVIRONMENT="test"
        TARGET_HOSTS=("test20")
        ;;
    prod)
        ENVIRONMENT="prod"
        TARGET_HOSTS=("prod30" "prod130")
        ;;
    prod30)
        ENVIRONMENT="prod"
        TARGET_HOSTS=("prod30")
        ;;
    prod130)
        ENVIRONMENT="prod"
        TARGET_HOSTS=("prod130")
        ;;
    *)
        echo "Error: Invalid target '$INPUT'."
        echo "Allowed values: test, prod, test20, prod30, prod130."
        exit 1
        ;;
esac

# Exit immediately if any local command fails
set -e

echo "Starting deployment for: $INPUT (Environment: $ENVIRONMENT)..."

# 4. Loop through the target hosts and execute the deployment
for HOST in "${TARGET_HOSTS[@]}"; do
    echo "------------------------------------------------"
    echo "Deploying to host: $HOST..."
    echo "------------------------------------------------"

    # Connect via SSH and execute the commands on the remote server.
    ssh -i "$HOME/.ssh/github_actions_key" "git@$HOST" << EOF
        # The following commands run on the remote server
        set -e
        
        echo "Navigating to project directory..."
        cd /opt/guru-wisdom.de

        echo "Pulling latest changes from git..."
        git pull origin main

        echo "Setting environment variables..."
        echo "APP_ENV=$ENVIRONMENT" > .env
        chmod 600 .env

        echo "Building and restarting the 'app' container..."
        docker compose up -d --build app
        
        echo "Clearing application cache..."
        docker compose exec app rm -rf runtime/cache/*
        
        echo "Pruning unused Docker images..."
        docker image prune -f
EOF

    echo "Deployment to $HOST completed successfully!"
done

echo "================================================"
echo "All deployments for '$INPUT' finished successfully!"