#!/usr/bin/env zsh

# Exit immediately if a command exits with a non-zero status
set -e

echo "Starting translation script..."

# 1. Load the API Key from the .env file (located in the project root)
ENV_FILE="/Users/markuswolff/guru-wisdom.de//.env"
if [[ -f "$ENV_FILE" ]]; then
    # Export variables, ignoring commented lines
    export $(grep -v '^#' "$ENV_FILE" | xargs)
fi

API_KEY="${GOOGLE_TRANSLATE_API_KEY}"

if [[ -z "$API_KEY" ]]; then
    echo "❌ Error: GOOGLE_TRANSLATE_API_KEY not found!"
    echo "Please ensure the .env file exists and contains the key."
    exit 1
fi

# Define directory paths
SOURCE_DIR="$(dirname $0)/../public/wisdoms"
TARGET_DIR="${SOURCE_DIR}/en"

mkdir -p "$TARGET_DIR"

# 2. Helper function to call the Google Cloud API
translate_text() {
    local text="$1"
    
    # Do nothing if the string is empty
    if [[ -z "$text" ]]; then
        echo ""
        return
    fi

    # Safely build the JSON payload using jq (prevents issues with quotes in the text)
    local json_payload=$(jq -n --arg q "$text" '{q: [$q], source: "de", target: "en", format: "text"}')

    # Send POST request via cURL
    local response=$(curl -s -X POST \
        -H "Content-Type: application/json" \
        -d "$json_payload" \
        "https://translation.googleapis.com/language/translate/v2?key=${API_KEY}")

    # Error handling: Check if Google returned an error (e.g., invalid key)
    local error_msg=$(echo "$response" | jq -r '.error.message // empty')
    if [[ -n "$error_msg" ]]; then
        echo "API Error: $error_msg" >&2
        return 1
    fi

    # Extract the translated text from the JSON response
    local translated=$(echo "$response" | jq -r '.data.translations[0].translatedText // empty')

    # Google sometimes returns HTML entities (like &#39; for '), let's decode the common ones:
    translated=$(echo "$translated" | sed "s/&#39;/'/g; s/&quot;/\"/g; s/&amp;/\&/g; s/&lt;/</g; s/&gt;/>/g")

    echo "$translated"
}

# 3. Process all .md files
for file in "$SOURCE_DIR"/*.md; do
    filename=$(basename "$file")
    target_file="$TARGET_DIR/$filename"

    # Skip files that have already been translated
    if [[ -f "$target_file" ]]; then
        echo "⏭️  Skipping $filename (already exists)"
        continue
    fi

    echo "⏳ Translating $filename..."

    # Split file into Frontmatter (YAML) and Body (Markdown) using awk
    yaml_content=$(awk 'BEGIN {p=0} /^---$/ {p++; if(p==2) exit; next} p==1 {print}' "$file")
    markdown_body=$(awk 'BEGIN {p=0} /^---$/ {p++; next} p>=2 {print}' "$file")

    # 4. Process YAML Frontmatter line by line to translate specific keys
    new_yaml=""
    echo "$yaml_content" | while IFS= read -r line; do
        # Use ZSH Regex to match title:, subtitle:, or description:
        if [[ "$line" =~ ^(title|subtitle|description):\ *(.*)$ ]]; then
            key=$match[1]
            # Remove leading/trailing quotes if present
            val=$(echo "$match[2]" | sed 's/^"//;s/"$//')
            
            translated_val=$(translate_text "$val")
            new_yaml+="${key}: \"${translated_val}\"\n"
        else
            # Keep other lines (like id, tags, date) untouched
            new_yaml+="${line}\n"
        fi
    done

    # 5. Translate the main Markdown body
    translated_body=$(translate_text "$markdown_body")

    # 6. Reassemble the file and save it
    {
        echo "---"
        echo -e -n "$new_yaml"
        echo "---"
        echo "$translated_body"
    } > "$target_file"

    echo "✅ Done: $filename"
done

echo "🎉 All translations completed successfully!"