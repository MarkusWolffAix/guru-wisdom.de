#!/bin/bash

# 1. Prüfen, ob ein Parameter (die ID) übergeben wurde
if [ -z "$1" ]; then
  echo "Fehler: Du hast keine ID angegeben."
  echo "Verwendung: ./update_sitemap.sh <deine_id>"
  exit 1
fi

# Variablen definieren
ID="$1"
BASE="/Users/markuswolff/guru-wisdom.de"
SITEMAP_FILE="$BASE/public/sitemap.xml"
WISDOM_FILE="$BASE/public/wisdoms/${ID}.md"

DOMAIN="https://guru-wisdom.de"

DATE=$(date -r $WISDOM_FILE  "+%Y-%m-%dT%H:%M:%SZ")
echo $DATE
# DATE=$(echo $(cat $WISDOM_FILE|grep "date:"|cut -f2 -d' ')T00:00:00Z)
# echo $DATE
# DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ") # Erzeugt das von Google geforderte ISO 8601 Format
# echo $DATE


# 2. Prüfen, ob die Sitemap-Datei existiert
if [ ! -f "$SITEMAP_FILE" ]; then
  echo "Fehler: Die Datei $SITEMAP_FILE wurde nicht gefunden."
  exit 1
fi

# 3. Das abschließende </urlset> Tag aus der Datei entfernen
# Dies löscht alle Zeilen, die exakt </urlset> enthalten
# sed -i '/<\/urlset>/d' "$SITEMAP_FILE"
# sed -i '\|</urlset>|d' "$SITEMAP_FILE"

# 3. Die Zeile direkt "in-place" aus der Originaldatei löschen
sed -i '/<\/urlset>/d' "$SITEMAP_FILE"

# 4. Anhängen
cat <<EOF >> "$SITEMAP_FILE"
  <url>
    <loc>${DOMAIN}/${ID}</loc>
    <lastmod>${DATE}</lastmod>
  </url>
</urlset>
EOF


echo "Erfolg: Die ID '${ID}' wurde erfolgreich zur $SITEMAP_FILE hinzugefügt."
