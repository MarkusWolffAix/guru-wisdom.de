#!/bin/bash

# Parameters and path
ENV=$1
PROJECT_PATH="/opt/guru-wisdom.de"
IS_LOCAL=false

# 1. Check: Was a parameter provided?
if [ -z "$ENV" ]; then
    # No parameter -> Prompt for local execution
    read -p "No parameter provided. Do you want to run the deployment LOCALLY? (y/n): " answer
    if [[ "$answer" == [yY]* ]]; then
        ENV="local"
        IS_LOCAL=true
    else
        echo "Aborted."
        echo "Usage: $0 {test|prod|replica} (or without parameter for local deployment)"
        exit 1
    fi
else
    # Parameter exists -> Validate remote environments
    case "$ENV" in
        test|prod|replica)
            SERVER="git@$ENV"
            ;;
        *)
            echo "Error: Invalid parameter '$ENV'."
            echo "Usage: $0 {test|prod|replica} (or without parameter for local deployment)"
            exit 1
            ;;
    esac
fi

echo "----------------------------------------------------"
if [ "$IS_LOCAL" = true ]; then
    echo "Starting local deployment on this machine..."
else
    echo "Starting remote deployment: Connecting to $SERVER..."
fi
echo "----------------------------------------------------"

# 2. Execute commands (Local or Remote)
if [ "$IS_LOCAL" = true ]; then
    # LOCAL EXECUTION (without SSH)
    cd "$PROJECT_PATH" || { echo "Directory $PROJECT_PATH not found"; exit 1; }
    
    echo ">>> Updating code (git pull)..."
    git pull
    
    echo ">>> Building and starting Docker container..."
    docker compose up -d --build app
    
    echo ">>> Cleaning up unused Docker images..."
    docker image prune -f
    
    # Save exit code for the success message
    STATUS=$?
else
    # REMOTE EXECUTION (via SSH)
    ssh -t "$SERVER" "
        cd $PROJECT_PATH || { echo 'Directory $PROJECT_PATH not found'; exit 1; }
        
        echo '>>> Updating code (git pull)...'
        git pull
        
        echo '>>> Building and starting Docker container...'
        docker compose up -d --build app
        
        echo '>>> Cleaning up unused Docker images...'
        docker image prune -f
    "
    STATUS=$?
fi

# 3. Final message
if [ $STATUS -eq 0 ]; then
    echo "----------------------------------------------------"
    echo "Success: Deployment to '$ENV' completed!"
else
    echo "----------------------------------------------------"
    echo "Error: Deployment to '$ENV' failed."
    exit 1
fi