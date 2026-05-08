#!/usr/bin/env zsh

# Exit immediately if a command exits with a non-zero status
set -e

echo "Starting translation script..."

# 1. Load the API Key from the .env file (located in the project root)
ENV_FILE="/Users/markuswolff/guru-wisdom.de//.env"
if [[ -f "$ENV_FILE" ]]; then
    export $(grep -v '^#' "$ENV_FILE" | xargs)
fi

API_KEY="${GOOGLE_TRANSLATE_API_KEY}"

if [[ -z "$API_KEY" ]]; then
    echo "❌ Error: GOOGLE_TRANSLATE_API_KEY not found!"
    exit 1
fi

# Define directory paths
SOURCE_DIR="/Users/markuswolff/guru-wisdom.de/public/wisdoms"
TARGET_DIR="${SOURCE_DIR}/en"

mkdir -p "$TARGET_DIR"

# 2. Helper function to call the Google Cloud API
translate_text() {
    local text="$1"
    
    if [[ -z "$text" ]]; then
        return
    fi

    # [FIX 1] Use jq -Rs to safely read massive text blocks from stdin
    local json_payload=$(printf "%s" "$text" | jq -Rs '{q: [.], source: "de", target: "en", format: "text"}')

    # [FIX 2] Pipe the payload into curl using -d @- to preserve all exact bytes and formatting
    local response=$(printf "%s" "$json_payload" | curl -s -X POST \
        -H "Content-Type: application/json" \
        -d @- \
        "https://translation.googleapis.com/language/translate/v2?key=${API_KEY}")

    # [FIX 3] Use printf "%s" instead of echo to prevent ZSH from expanding \n into real newlines
    local error_msg=$(printf "%s" "$response" | jq -r '.error.message // empty')
    if [[ -n "$error_msg" ]]; then
        printf "API Error: %s\n" "$error_msg" >&2
        return 1
    fi

    # Extract the translated text safely
    local translated=$(printf "%s" "$response" | jq -r '.data.translations[0].translatedText // empty')

    # Decode HTML entities injected by Google
    translated=$(printf "%s" "$translated" | sed "s/&#39;/'/g; s/&quot;/\"/g; s/&amp;/\&/g; s/&lt;/</g; s/&gt;/>/g")

    printf "%s" "$translated"
}

# 3. Process all .md files
for file in "$SOURCE_DIR"/*.md; do
    filename=$(basename "$file")
    target_file="$TARGET_DIR/$filename"

    if [[ -f "$target_file" ]]; then
        echo "⏭️  Skipping $filename (already exists)"
        continue
    fi

    echo "⏳ Translating $filename..."

    # Split file into Frontmatter (YAML) and Body (Markdown)
    yaml_content=$(awk 'BEGIN {p=0} /^---$/ {p++; if(p==2) exit; next} p==1 {print}' "$file")
    markdown_body=$(awk 'BEGIN {p=0} /^---$/ {p++; next} p>=2 {print}' "$file")

    # 4. Safely translate specific YAML keys without string concatenation bugs
    new_yaml=$(printf "%s\n" "$yaml_content" | while IFS= read -r line; do
        if [[ "$line" =~ ^(title|subtitle|description):\ *(.*)$ ]]; then
            key=$match[1]
            val=$(printf "%s" "$match[2]" | sed 's/^"//;s/"$//')
            
            translated_val=$(translate_text "$val")
            printf "%s: \"%s\"\n" "$key" "$translated_val"
        else
            printf "%s\n" "$line"
        fi
    done)

    # 5. Translate the main Markdown body
    translated_body=$(translate_text "$markdown_body")

    # 6. Reassemble the file using printf for strict formatting control
    {
        echo "---"
        printf "%s\n" "$new_yaml"
        echo "---"
        printf "%s\n" "$translated_body"
    } > "$target_file"

    echo "✅ Done: $filename"
done

echo "🎉 All translations completed successfully!"