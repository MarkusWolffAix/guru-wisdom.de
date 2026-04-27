#!/bin/bash

# 1. Define paths (Enter your actual paths here!)
DIR_MD="$1"
DIR_MP3="$2"

echo "Searching for missing MP3s..."
echo "---------------------------"

# 2. Loop through all .md files
for md_file in "$DIR_MD"/*.md; do
    
    # 3. Extract the ID
    id=$(basename "$md_file" .md)
    
    # 4. Check if the MP3 is missing
    if [ ! -f "$DIR_MP3/$id.mp3" ]; then
        echo "Missing: $id"
    fi
    
done

echo "---------------------------"
echo "Search complete."
