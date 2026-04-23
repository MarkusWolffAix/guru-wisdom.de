#!/usr/bin/env zsh

# Load zsh/stat module for precise file timestamp comparisons
zmodload zsh/stat

# --- Configuration ---
# 'base' is only needed to find the markdown (.md) files
base="/Users/markuswolff/Documents/Arbeit/GuruWisdom/guru-wisdom.de/web"
MD_DIR="$base/wisdoms"
SOURCE_MEDIA_DIR="/Users/markuswolff/Downloads"
ONEDRIVE_DIR="$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/S3Storage"

# S3 Settings
S3_BUCKET="s3://guru-wisdom"
S3_ENDPOINT="https://fsn1.your-objectstorage.com"

# Create a temporary directory for image conversion
TMP_DIR=$(mktemp -d)
# Trap: Automatically deletes the temporary directory when the script exits or is aborted
trap 'rm -rf "$TMP_DIR"' EXIT

# Extensions to search for in the Downloads folder
SOURCE_EXTS=(mp3 png mp4 mov)

# --- Colors for terminal output ---
CYAN='\033[0;36m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}       Guru Wisdom File Processor       ${NC}"
echo -e "${CYAN}       (Cloud-Only / Direct to S3)      ${NC}"
echo -e "${CYAN}========================================${NC}\n"

# 1. Determine the ID (parameter or auto-detection)
ID_PROVIDED=false
if [[ -n "$1" ]]; then
    ID="$1"
    ID_PROVIDED=true
    echo -e "➔ Using provided ID: ${GREEN}$ID${NC}"
    REFERENCE_FILE="$MD_DIR/${ID}.md"
    cp "$REFERENCE_FILE" "/Users/markuswolff/Development/t3.guru-wisdom.de/public/wisdoms/${ID}.md"
else
    REFERENCE_FILE=$(ls -t "$MD_DIR"/*.md 2>/dev/null | head -n 1)

    if [[ -z "$REFERENCE_FILE" ]] || ! find "$REFERENCE_FILE" -mmin -180 >/dev/null 2>&1; then
        echo -e "${RED}No recent Markdown file found (newer than 3 hours).${NC}"
        echo -e "Usage: $0 <id>"
        exit 1
    fi

    SUGGESTED_ID=$(basename "$REFERENCE_FILE" .md)
    echo -e -n "➔ Recent MD file found. Use ID '${GREEN}${SUGGESTED_ID}${NC}'? [Y/n]: "
    read choice
    [[ "$choice" == [nN]* ]] && exit 1
    ID="$SUGGESTED_ID"
fi

# Helper function for S3 confirmation and checking
confirm_s3_upload() {
    local local_file="$1"
    local s3_path="$2"
    
    # Check if file already exists on S3
    if aws s3 ls "$s3_path" --endpoint-url "$S3_ENDPOINT" > /dev/null 2>&1; then
        echo -e -n "${RED}   ➔ File already exists on S3: $(basename "$s3_path"). ${NC}"
        echo 
        # read choice
        # [[ "$choice" == [yY]* ]] && return 0 
    fi

    echo -e -n "${YELLOW}   ➔ Upload to S3 at $s3_path? [Y/n]: ${NC}"
    read confirm
    if [[ "$confirm" != [nN]* ]]; then
        if aws s3 cp "$local_file" "$s3_path" --endpoint-url "$S3_ENDPOINT" >/dev/null; then
            echo -e "      ${GREEN}✔ Upload successful.${NC}"
            backupfile=$(echo $s3_path|cut -f4 -d '/'); 
            cp "$local_file" "$ONEDRIVE_DIR/$backupfile"
        else
            echo -e "      ${RED}✘ Upload failed!${NC}"
        fi
    else
        echo -e "      ${YELLOW}⚠ S3 upload skipped.${NC}"
    fi
}

# 2. Media File Matching & Processing
if [[ -f "$REFERENCE_FILE" ]]; then
    MD_TIME=$(zstat +mtime "$REFERENCE_FILE")
    MAX_DIFF=1800 # 30 minute window

    find_closest_media() {
        local ext="$1"
        local best_file=""
        local min_diff=$MAX_DIFF
        setopt localoptions nullglob
        
        for f in "$SOURCE_MEDIA_DIR"/*.$ext; do
            local f_time=$(zstat +mtime "$f")
            local diff=$(( f_time - MD_TIME ))
            (( diff < 0 )) && diff=$(( -diff ))

            if (( diff <= min_diff )); then
                min_diff=$diff
                best_file="$f"
            fi
        done
        echo "$best_file"
    }

    echo -e "\n${CYAN}--- Processing Media Files ---${NC}"

    for ext in "${SOURCE_EXTS[@]}"; do
        best_file=""
        
        # Logic: If ID is provided, look for <ID>.<ext> first
        if [[ "$ID_PROVIDED" == true && -f "$SOURCE_MEDIA_DIR/${ID}.${ext}" ]]; then
            best_file="$SOURCE_MEDIA_DIR/${ID}.${ext}"
        else
            # Otherwise (or if not found), look for the closest timestamp
            best_file=$(find_closest_media "$ext")
        fi
        
        if [[ -n "$best_file" ]]; then
            echo -e -n "➔ Found file: ${(U)ext} ($(basename "$best_file")). Process for ID ${GREEN}${ID}${NC}? [Y/n]: "
            read ans
            [[ "$ans" == [nN]* ]] && continue

            # --- CASE 1: IMAGES (Source: PNG) ---
            if [[ "$ext" == "png" ]]; then
                # Convert to Web-JPG (in Temp folder)
                sips --resampleWidth 640 -s format jpeg "$best_file" --out "$TMP_DIR/${ID}.jpg" >/dev/null 2>&1
                confirm_s3_upload "$TMP_DIR/${ID}.jpg" "$S3_BUCKET/images/${ID}.jpg"

                # Convert to High-Res Org-JPG (in Temp folder)
                sips -s format jpeg -s formatOptions high "$best_file" --out "$TMP_DIR/${ID}_org.jpg" >/dev/null 2>&1
                confirm_s3_upload "$TMP_DIR/${ID}_org.jpg" "$S3_BUCKET/images/org/${ID}.jpg"

                # Note: The original PNG remains untouched in the Downloads folder.

            # --- CASE 2: AUDIO (MP3) ---
            elif [[ "$ext" == "mp3" ]]; then
                # Direct upload from Downloads folder, original stays untouched
                confirm_s3_upload "$best_file" "$S3_BUCKET/audio/${ID}.mp3"

            # --- CASE 3: VIDEO (MP4, MOV) ---
            elif [[ "$ext" == "mp4" || "$ext" == "mov" ]]; then
                # Direct upload from Downloads folder, original stays untouched
                confirm_s3_upload "$best_file" "$S3_BUCKET/video/${ID}.${ext}"
            fi
        fi
    done
fi

echo -e "\n${CYAN}========================================${NC}"
echo -e "${GREEN}✔ Done! Files processed for ID: $ID${NC}"
echo -e "${CYAN}========================================${NC}\n"
