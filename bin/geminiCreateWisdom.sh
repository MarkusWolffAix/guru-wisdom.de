#!/bin/bash
# Aufruf: ./geminiCLI.zsh "Thema deiner Wahl"

# 1. API-Key Konfiguration
API_KEY="${GEMINI_API_KEY:-DEIN_API_KEY_HIER}"
API_KEY="${security find-generic-password -a "markuswolff" -s "GEMINI_API_TOKEN" -w"}"

# Prüfen auf API-Key
if [ "$API_KEY" == "DEIN_API_KEY_HIER" ] || [ -z "$API_KEY" ]; then
    echo "❌ Fehler: Bitte setze die Umgebungsvariable GEMINI_API_KEY."
    exit 1
fi

# 2. Thema aus dem ersten Parameter auslesen (Fallback: "Guru-Wisdom")
TOPIC="${1:-Guru-Wisdom}"

# Einen dateisicheren Namen generieren (Kleinbuchstaben, Leerzeichen zu Unterstrichen)
SAFE_NAME=$(echo "$TOPIC" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9' '_' | sed 's/_$//')
DATE=$(date +"%Y-%m-%d")

# Dateinamen festlegen
IMAGE_FILE="${SAFE_NAME}.jpg"
MD_FILE="${SAFE_NAME}.md"

# Modelle
MODEL_IMAGE="gemini-2.5-flash-image"
MODEL_TEXT="gemini-1.5-flash"

echo "🚀 Starte Generierung für das Thema: '$TOPIC'"
echo "---------------------------------------------------"

# 3. Bild generieren
echo "🎨 Generiere passendes Bild..."
IMAGE_PROMPT="Ein ästhetisches, hochwertiges Bild passend zum Thema: $TOPIC. Fotorealistisch, gute Beleuchtung."

RESPONSE_IMG=$(curl -s -X POST "https://generativelanguage.googleapis.com/v1beta/openai/images/generations" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_KEY" \
  -d "{
    \"model\": \"$MODEL_IMAGE\",
    \"prompt\": \"$IMAGE_PROMPT\",
    \"response_format\": \"b64_json\",
    \"n\": 1
  }")

# Fehlerprüfung und Bild speichern
if echo "$RESPONSE_IMG" | grep -q '"error"'; then
    echo "❌ Fehler bei der Bild-Generierung:"
    echo "$RESPONSE_IMG" | jq -r '.error.message'
    exit 1
fi

echo "$RESPONSE_IMG" | jq -r '.data[0].b64_json' | base64 --decode > "$IMAGE_FILE"
echo "✅ Bild gespeichert als: $IMAGE_FILE"

# 4. Text für den Markdown-Body generieren
echo "📜 Generiere Artikel-Inhalt..."
TEXT_PROMPT="Schreibe einen spannenden, gut strukturierten Blog-Artikel (ca. 200 Wörter) über das Thema '$TOPIC'. Nutze Markdown-Formatierung wie Überschriften (##) und Aufzählungen. Schreibe KEINEN Front-Matter-Header, nur den reinen Inhalt."

RESPONSE_TEXT=$(curl -s -X POST "https://generativelanguage.googleapis.com/v1beta/openai/chat/completions" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_KEY" \
  -d "{
    \"model\": \"$MODEL_TEXT\",
    \"messages\": [{\"role\": \"user\", \"content\": \"$TEXT_PROMPT\"}],
    \"temperature\": 0.7
  }")

# Inhalt extrahieren
ARTICLE_CONTENT=$(echo "$RESPONSE_TEXT" | jq -r '.choices[0].message.content')

# 5. Markdown-Datei mit Front-Matter zusammenbauen
echo "📝 Erstelle Markdown-Datei mit Front-Matter..."

cat <<EOF > "$MD_FILE"
---
id: "$TOPIC"
title: "Tiefe Einblicke in $TOPIC"
subtitle: "subtile zu $TOPIC"
description: "description zu $TOPIC"
date: $DATE
author: "Markus Wolff guru-wisdom.de"
tags: ["$SAFE_NAME", "x", "x", "x"]
categories: ["x", "x"]
---

$ARTICLE_CONTENT
EOF

echo "✅ Markdown-Datei gespeichert als: $MD_FILE"
echo "---------------------------------------------------"
echo "🎉 Komplett fertig! Deine Dateien $MD_FILE und $IMAGE_FILE liegen bereit."