#!/bin/zsh

echo "🚀 Starte die Bild-Generierung (Optimierte Version)..."
echo "------------------------------------------------------"

# Schleife über alle PNG-Dateien im aktuellen Ordner
for img in *.jpg; do
    # Falls keine PNGs da sind, brich ab
    [[ -e "$img" ]] || { echo "Keine PNG-Dateien gefunden!"; break; }

    # Dateinamen ohne Endung extrahieren
    filename="${img%.*}"

    echo "Verarbeite: $img"

    # 1. Großes WebP für die Detailseite (Maximal 1280px, 80% Qualität)
    cwebp -q 80 -resize 1280 0 "$img" -o "webp/${filename}.webp" > /dev/null

    # 2. Thumbnail WebP für die Übersicht (Maximal 640px, 80% Qualität)
    cwebp -q 80 -resize 640  0 "$img" -o "thumb/${filename}.webp" > /dev/null

    # 3. Universal-Fallback JPG für Uralt-Geräte (Maximal 640px, 80% Qualität)
    sips -Z 640 -s format jpeg -s formatOptions 80 "$img" --out "jpg/${filename}.jpg" > /dev/null

    echo "✅ $filename fertig!"
done

echo "------------------------------------------------------"
echo "🎉 Alle Bilder wurden erfolgreich konvertiert!"
