#!/bin/bash

# Konfiguration
WISDOM_DIR="web/wisdoms"

# Prüfen, ob wir im Git-Repo sind
if ! git rev-parse --is-inside-work-tree > /dev/null 2>&1; then
    echo "Fehler: Dies ist kein Git-Repository."
    exit 1
fi

# Argumente verarbeiten
CSV_MODE=0
SORT_MODE=0
for arg in "$@"; do
    case $arg in
        --csv|-c)  CSV_MODE=1 ;;
        --sort|-s) SORT_MODE=1 ;;
    esac
done

# Hilfsfunktion zum Extrahieren der Tags
get_tags() {
    local file=$1
    local val=$(grep "^tags:" "$file" | head -n 1 | cut -d':' -f2-)
    # Entfernt eckige Klammern, Anführungszeichen und bereinigt Leerzeichen
    echo "$val" | sed -e 's/[\[\]]//g' -e 's/"//g' -e "s/'//g" -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//'
}

# Hilfsfunktion für das physische Erstelldatum (Cross-Platform Versuch)
get_phys_date() {
    local file=$1
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS (BSD stat)
        stat -f "%SB" -t "%Y-%m-%d" "$file"
    else
        # Linux (GNU stat) - Versucht Birth date, sonst Modification date
        local bDate=$(stat -c '%w' "$file" 2>/dev/null | cut -d' ' -f1)
        if [[ -z "$bDate" || "$bDate" == "-" ]]; then
            stat -c '%y' "$file" | cut -d' ' -f1
        else
            echo "$bDate"
        fi
    fi
}

# 1. Daten sammeln
TAB=$'\t'
RAW_DATA=""
FILES=$(git ls-files "$WISDOM_DIR/*.md")

if [[ -z "$FILES" ]]; then
    echo "Keine Dateien in $WISDOM_DIR gefunden."
    exit 0
fi

while read -r file; do
    file_id=$(basename "$file" .md)
    tags=$(get_tags "$file")
    
    # Git Datum (Letzter Push/Commit)
    git_date=$(git log -1 --format="%as" -- "$file")
    [[ -z "$git_date" ]] && git_date="0000-00-00"
    
    # Physisches Datum (Erstellung auf der Platte)
    phys_date=$(get_phys_date "$file")

    line="${file_id}${TAB}${tags}${TAB}${git_date}${TAB}${phys_date}"
    RAW_DATA="${RAW_DATA}${line}"$'\n'
done <<< "$FILES"

# 2. Sortierung (Standard: FileID, bei -s: Git-Datum)
if [[ $SORT_MODE -eq 1 ]]; then
    PROCESSED_DATA=$(echo -n "$RAW_DATA" | sort -t"$TAB" -k3,3 -k1,1)
else
    PROCESSED_DATA=$(echo -n "$RAW_DATA" | sort -t"$TAB" -k1,1)
fi

# 3. Ausgabe
if [[ $CSV_MODE -eq 1 ]]; then
    echo "FileID,Tags,GitDate,PhysDate"
    echo "$PROCESSED_DATA" | tr "$TAB" "," | sed 's/0000-00-00/Nicht committet/'
else
    # Dynamische Breiten
    W_FID=8; W_TAG=4
    while IFS="$TAB" read -r fid tag gdat pdat; do
        [[ ${#fid} -gt $W_FID ]] && W_FID=${#fid}
        [[ ${#tag} -gt $W_TAG ]] && W_TAG=${#tag}
    done <<< "$PROCESSED_DATA"

    draw_line() {
        printf "+-%s-+-%s-+------------+------------+\n" \
            "$(printf '%*s' "$W_FID" '' | tr ' ' '-')" \
            "$(printf '%*s' "$W_TAG" '' | tr ' ' '-')"
    }

    draw_line
    printf "| %-${W_FID}s | %-${W_TAG}s | %-10s | %-10s |\n" "FileID" "Tags" "Git-Date" "Phys-Date"
    draw_line

    while IFS="$TAB" read -r fid tag gdat pdat; do
        [[ "$gdat" == "0000-00-00" ]] && gdat="---       "
        printf "| %-${W_FID}s | %-${W_TAG}s | %-10s | %-10s |\n" "$fid" "$tag" "$gdat" "$pdat"
    done <<< "$PROCESSED_DATA"
    draw_line
fi