#!/bin/bash

# 1. API-Key Konfiguration
# Das Skript nutzt entweder die Umgebungsvariable $GEMINI_API_KEY oder du trägst ihn direkt hier ein.
API_KEY="${security find-generic-password -a "markuswolff" -s "GEMINI_API_TOKEN" -w"}"

# 2. Einstellungen
PROMPT="Ein fotorealistisches Bild von einem alten Weisen mit langem Bart, der unter einem alten Baum sitzt und ein strahlendes, goldenes Buch öffnet, aus dem Symbole von Wissen und Licht emporsteigen, Thema: Guru-Wisdom"
OUTPUT_FILE="guru_wisdom.jpg"
MODEL="gemini-2.5-flash-image"

# Prüfen, ob ein API-Key gesetzt ist
if [ "$API_KEY" == "DEIN_API_KEY_HIER" ] || [ -z "$API_KEY" ]; then
    echo "❌ Fehler: Bitte setze die Umgebungsvariable GEMINI_API_KEY oder trage den Key direkt ins Skript ein."
    echo "Beispiel: export GEMINI_API_KEY=\"AIzaSy...\""
    exit 1
fi

echo "⏳ Generiere Bild über das Modell $MODEL..."
echo "🎨 Prompt: $PROMPT"

# 3. API-Aufruf mit curl
# Wir nutzen den openai-kompatiblen Endpunkt der Generative Language API
RESPONSE=$(curl -s -X POST "https://generativelanguage.googleapis.com/v1beta/openai/images/generations" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_KEY" \
  -d "{
    \"model\": \"$MODEL\",
    \"prompt\": \"$PROMPT\",
    \"response_format\": \"b64_json\",
    \"n\": 1
  }")

# 4. Fehlerprüfung
if echo "$RESPONSE" | grep -q '"error"'; then
    echo "❌ Fehler bei der API-Anfrage:"
    # Fehlermeldung formatiert ausgeben
    echo "$RESPONSE" | jq -r '.error.message'
    exit 1
fi

# 5. Base64-Daten extrahieren und in eine JPG-Datei umwandeln
# Wir nutzen jq um das Feld b64_json zu lesen und base64 um es zu decodieren
echo "$RESPONSE" | jq -r '.data[0].b64_json' | base64 --decode > "$OUTPUT_FILE"

# Prüfen, ob die Datei erfolgreich erstellt wurde
if [ $? -eq 0 ] && [ -s "$OUTPUT_FILE" ]; then
    echo "✅ Erfolg! Das Bild wurde lokal gespeichert als: $OUTPUT_FILE"
else
    echo "❌ Fehler beim Dekodieren oder Speichern des Bildes."
    exit 1
fi
