#!/usr/bin/env zsh

# Load zsh/stat module for precise file timestamp comparisons
zmodload zsh/stat

# --- Configuration ---
base="/Users/markuswolff/Documents/Arbeit/Development/guru-wisdom.de/public/"
MD_DIR="$base/wisdoms"
SOURCE_MEDIA_DIR="/Users/markuswolff/Downloads"
ONEDRIVE_DIR="$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/S3Storage"

# S3 Settings
S3_BUCKET_1="s3://guru-wisdom-first"
S3_ENDPOINT_1="https://nbg1.your-objectstorage.com"
S3_PROFILE_1="nuernberg"

S3_BUCKET_2="s3://guru-wisdom-secound"
S3_ENDPOINT_2="https://hel1.your-objectstorage.com"
S3_PROFILE_2="helsinki"

# Create a temporary directory for image conversion
TMP_DIR=$(mktemp -d)
trap 'rm -rf "$TMP_DIR"' EXIT

# Extensions to search for in the Downloads folder
SOURCE_EXTS=(mp3 png mp4 mov)

# --- Colors for terminal output ---
CYAN='\033[0;36m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# --- Argument Parsing & Debug Setup ---
DEBUG=false
ID=""

# Durchlaufe alle übergebenen Argumente
while [[ $# -gt 0 ]]; do
    case "$1" in
        -d|--debug)
            DEBUG=true
            shift # Springe zum nächsten Argument
            ;;
        *)
            # Wenn es kein Schalter ist und wir noch keine ID haben, ist es die ID
            if [[ -z "$ID" ]]; then
                ID="$1"
            fi
            shift
            ;;
    esac
done

# Hilfsfunktion für saubere Debug-Ausgaben
debug_log() {
    if [[ "$DEBUG" == true ]]; then
        echo -e "${YELLOW}[DEBUG] $1${NC}" >&2
    fi
}

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}       Guru Wisdom File Processor       ${NC}"
echo -e "${CYAN}       (Cloud-Only / Direct to S3)      ${NC}"
echo -e "${CYAN}========================================${NC}\n"

# 1. Determine the ID (parameter or auto-detection)
ID_PROVIDED=false
if [[ -n "$ID" ]]; then
    ID_PROVIDED=true
    echo -e "➔ Using provided ID: ${GREEN}$ID${NC}"
    REFERENCE_FILE="$MD_DIR/${ID}.md"
else 
    REFERENCE_FILE=$(ls -t "$MD_DIR"/*.md 2>/dev/null | head -n 1)

    if [[ -z "$REFERENCE_FILE" ]] || ! find "$REFERENCE_FILE" -mmin -180 >/dev/null 2>&1; then
        echo -e "${RED}No recent Markdown file found (newer than 3 hours).${NC}"
        echo -e "Usage: $0 [-d|--debug] <id>"
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
    local path_suffix="$2"
    
    echo -e -n "${YELLOW}   ➔ Upload to BOTH S3 buckets? ($path_suffix) [Y/n]: ${NC}"
    read confirm
    if [[ "$confirm" != [nN]* ]]; then
        
        echo -e "      Pushing to Bucket 1..."
        if aws s3 cp "$local_file" "${S3_BUCKET_1}${path_suffix}"  --profile "$S3_PROFILE_1" --endpoint-url "$S3_ENDPOINT_1" >/dev/null; then
            echo -e "      ${GREEN}✔ Bucket 1: Success.${NC}"
        else
            echo -e "      ${RED}✘ Bucket 1: Failed!${NC}"
        fi

        echo -e "      Pushing to Bucket 2..."
        if aws s3 cp "$local_file" "${S3_BUCKET_2}${path_suffix}" --profile "$S3_PROFILE_2"  --endpoint-url "$S3_ENDPOINT_2" >/dev/null; then
            echo -e "      ${GREEN}✔ Bucket 2: Success.${NC}"
        else
            echo -e "      ${RED}✘ Bucket 2: Failed!${NC}"
        fi

        local folder_name=$(echo "$path_suffix" | cut -d'/' -f2)
        mkdir -p "$ONEDRIVE_DIR/$folder_name"
        echo -e "      Copy to onedrive Backup..."
        cp "$local_file" "$ONEDRIVE_DIR/${path_suffix}"
        echo -e "      ${GREEN}✔ Local Backup: Done.${NC}"
    else
        echo -e "      ${YELLOW}⚠ S3 upload skipped.${NC}"
    fi
}

# 2. Media File Matching & Processing
if [[ -f "$REFERENCE_FILE" ]]; then
    MD_TIME=$(zstat +mtime "$REFERENCE_FILE")
    MAX_DIFF=7200 # 2 Stunden Zeitfenster (7200 Sekunden)

    debug_log "Markdown Time: $MD_TIME | Max Diff Allowed: $MAX_DIFF seconds"

    find_closest_media() {
        local ext="$1"
        local best_file=""
        local min_diff=$MAX_DIFF
        setopt localoptions nullglob nocaseglob
        
        local files=("$SOURCE_MEDIA_DIR"/*.$ext)
        debug_log "find_closest_media: Found ${#files[@]} files for extension .$ext"

        for f in "${files[@]}"; do
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

    # Ausführlicher Ordner-Check nur im Debug-Modus
    if [[ "$DEBUG" == true ]]; then
        echo -e "\n${YELLOW}[DEBUG] --- Checking Directory Access & Content ---${NC}"
        echo -e "${YELLOW}[DEBUG] Listing up to 5 newest files in $SOURCE_MEDIA_DIR:${NC}"
        ls -lht "$SOURCE_MEDIA_DIR" | head -n 6 | sed 's/^/  [DEBUG] /'
        echo -e "${YELLOW}[DEBUG] -------------------------------------------${NC}"
    fi

    echo -e "\n${CYAN}--- Processing Media Files ---${NC}"

    for ext in "${SOURCE_EXTS[@]}"; do
        best_file=""
        debug_log "Scanning for type: ${(U)ext}"
        
        setopt localoptions nocaseglob nullglob
        local exact_matches=( "$SOURCE_MEDIA_DIR/${ID}.${ext}"(N) )

        if [[ "$ID_PROVIDED" == true && ${#exact_matches[@]} -gt 0 ]]; then
            best_file="${exact_matches[1]}"
            debug_log "Exact match found: $(basename "$best_file")"
        else
            best_file=$(find_closest_media "$ext")
            if [[ -n "$best_file" ]]; then
                debug_log "Closest timestamp match found: $(basename "$best_file")"
            else
                debug_log "No suitable file found for $ext within the time window."
            fi
        fi
        
        if [[ -n "$best_file" ]]; then
            echo -e -n "➔ Found file: ${(U)ext} ($(basename "$best_file")). Process for ID ${GREEN}${ID}${NC}? [Y/n]: "
            read ans
            [[ "$ans" == [nN]* ]] && continue

            # --- CASE 1: IMAGES (Source: PNG) ---
            if [[ "${ext:l}" == "png" ]]; then
                debug_log "Converting PNG to web images webp and jpg as fallbback..."
    		
		cwebp -q 80 -resize 1280 0 "$best_file" -o "$TMP_DIR/${ID}.webp" >/dev/null 2>&1 
                confirm_s3_upload "$TMP_DIR/${ID}.webp" "/images/${ID}.webp"
			
		cwebp -q 80 -resize 640  0 "$best_file" -o "$TMP_DIR/${ID}_thumb.webp" >/dev/null 2>&1
                confirm_s3_upload "$TMP_DIR/${ID}_thumb.webp" "/images/thumb/${ID}.jpg"
    	
		sips -Z 640 -s format jpeg -s formatOptions 80 "$best_file" --out "$TMP_DIR/${ID}.jpg" >/dev/null 2>&1	
                confirm_s3_upload "$TMP_DIR/${ID}.jpg" "/images/${ID}.jpg"


            # --- CASE 2: AUDIO (MP3) ---
            elif [[ "${ext:l}" == "mp3" ]]; then
                confirm_s3_upload "$best_file" "/audio/${ID}.mp3"

            # --- CASE 3: VIDEO (MP4, MOV) ---
            elif [[ "${ext:l}" == "mp4" || "${ext:l}" == "mov" ]]; then
                confirm_s3_upload "$best_file" "/video/${ID}.${ext:l}"
            fi
        fi
    done
fi

echo -e "\n${CYAN}========================================${NC}"
echo -e "${GREEN}✔ Done! Files processed for ID: $ID${NC}"
echo -e "${CYAN}========================================${NC}\n"
