#!/bin/bash

# 1. Provide the token so the background task has permission
source /Users/markuswolff/.zshrc 

# 2. Run the command using the full path to gh, saving directly to OneDrive
/opt/homebrew/bin/gh project item-list 2 --owner MarkusWolffAix --format json > ~/OneDrive/Backup/guru-wisdom/project-backup-$(date +'%Y-%m-%d').json
