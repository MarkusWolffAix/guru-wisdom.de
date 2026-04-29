#!/usr/bin/env zsh

# ==============================================================================
# WISDOM FRONT-MATTER UPDATER (macOS / Darwin version)
# ------------------------------------------------------------------------------
# This script updates the YAML front matter of a specific markdown file 
# based on the provided CSV mapping.
#
# ------------------------------------------------------------------------------
# MASTER STRUCTURE REFERENCE:
#
# 1. MAIN CATEGORIES:
#    - Spirituality & Mysticism
#    - History & Myths
#    - Science & Nature
#    - Home & Origin
#    - Healing & Mindfulness
#    - Symbols & Patterns
#    - Love & Connection
#
# 2. UNIFIED KEYWORDS (Standardized Tags):
#    - Traditions: Hinduism, Christianity, Buddhism, Antiquity, Sufism
#    - Places: Aachen, Mesopotamia, Jerusalem, India
#    - Practices: Meditation, Mantra, Yoga, Mindfulness
#    - Concepts: Love, OM, AUM, Zero, Infinity, Elements, Creation, Fire, Sound
#    - Scientific: Biology, Mathematics, Quantum Physics, Psychology
#    - Figures: Abraham, Mary, Vishnu, Ganesha, Gaia
# ==============================================================================

CSV_FILE="$HOME/Downloads/wisdom_updates.csv"
WISDOM_DIR="."

# 1. Check if an ID was provided as a parameter
if [[ -z "$1" ]]; then
    echo "Error: No Wisdom ID provided."
    echo "Usage: ./update_wisdom.zsh <WisdomID>"
    echo "Example: ./update_wisdom.zsh Urd"
    echo ""
    echo "Available Wisdom IDs in $WISDOM_DIR:"
    ls -1 "$WISDOM_DIR"/*.md 2>/dev/null | sed 's/\.md$//' | xargs -n 1 basename
    exit 1
fi

# 2. Normalize ID (remove .md if user added it, then build file path)
INPUT_ID="${1%.md}"
TARGET_FILE="${INPUT_ID}.md"
FILEPATH="$WISDOM_DIR/$TARGET_FILE"

# 3. Check for existence of CSV and Markdown file
if [[ ! -f "$CSV_FILE" ]]; then
    echo "Error: Mapping file '$CSV_FILE' not found!"
    exit 1
fi

if [[ ! -f "$FILEPATH" ]]; then
    echo "Error: File '$FILEPATH' not found!"
    exit 1
fi

# 4. Extract the matching line from CSV and remove carriage returns (\r)
# We search for the filename (ID.md) at the start of the line
LINE=$(grep "^$TARGET_FILE;" "$CSV_FILE" | tr -d '\r')

if [[ -z "$LINE" ]]; then
    echo "Error: No entry found for '$TARGET_FILE' in $CSV_FILE."
    exit 1
fi

# 5. Extract columns using ';' as delimiter
# 1:Filename; 2:OldCat; 3:NewCat; 4:UnifiedTags; 5:SpecificKeywords
NEW_CAT=$(echo "$LINE" | cut -d';' -f3)
UNIFIED_TAGS=$(echo "$LINE" | cut -d';' -f4)
SPECIFIC_TAGS=$(echo "$LINE" | cut -d';' -f5)

echo "Processing Wisdom: $INPUT_ID..."

# ---------------------------------------------------------
# 6. FORMAT CATEGORIES INTO FLAT ARRAY
# ---------------------------------------------------------
formatted_categories=$(echo "$NEW_CAT" | awk -F',' '{
    out=""
    for(i=1;i<=NF;i++) {
        gsub(/^[ \t]+|[ \t]+$/, "", $i) # Trim whitespace
        if ($i != "") {
            out = out (out!=""?", ":"") "\"" $i "\""
        }
    }
    print out
}')

# Escape '&' for sed to prevent duplicating old content
safe_categories=${formatted_categories//&/\\&}

# ---------------------------------------------------------
# 7. FORMAT TAGS INTO FLAT ARRAY (Unified + Specific)
# ---------------------------------------------------------
all_tags="${UNIFIED_TAGS}, ${SPECIFIC_TAGS}"
formatted_tags=$(echo "$all_tags" | awk -F',' '{
    out=""
    for(i=1;i<=NF;i++) {
        gsub(/^[ \t]+|[ \t]+$/, "", $i) # Trim whitespace
        if ($i != "") {
            out = out (out!=""?", ":"") "\"" $i "\""
        }
    }
    print out
}')

# Escape '&' for sed
safe_tags=${formatted_tags//&/\\&}

# ---------------------------------------------------------
# 8. UPDATE FILE (Complete line replacement)
# ---------------------------------------------------------
# sed -i '' is the specific syntax for macOS (Darwin)
# Using ^categories:.* and ^tags:.* ensures the whole line is overwritten,
# preventing nested or partial replacements.
sed -i '' -E "s/^categories:.*/categories: [$safe_categories]/" "$FILEPATH"
sed -i '' -E "s/^tags:.*/tags: [$safe_tags]/" "$FILEPATH"

echo "✅ Successfully updated: $INPUT_ID"
