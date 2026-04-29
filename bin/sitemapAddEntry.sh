#!/bin/bash

ID="$1"
BASE="/Users/markuswolff/guru-wisdom.de"
SITEMAP_FILE="$BASE/public/sitemap.xml"
WISDOM_FILE="$BASE/public/wisdoms/${ID}.md"
DOMAIN="https://guru-wisdom.de"


if [ -z "$ID" ]; then
  echo "Error: No ID provided."
  echo "Usage: ./update_sitemap.sh <your_id>"
  exit 1
fi

if [ ! -f "$WISDOM_FILE" ]; then
  echo "Error: The file $WISDOM_FILE was not found."
  exit 1
fi

if [ ! -f "$SITEMAP_FILE" ]; then
  echo "Error: The file $SITEMAP_FILE was not found."
  exit 1
fi

grep "<loc>${DOMAIN}/${ID}</loc>" "$SITEMAP_FILE" > /dev/null 
if [ $? -eq 0 ]; then
  echo "Info: The ID '${ID}' is already present in the sitemap."
  exit 1
fi

OS=$(uname -s)

if [ "$OS" = "Darwin" ]; then
  # Mac (BSD sed)
  sed -i '' '/<\/urlset>/d' "$SITEMAP_FILE"
else
  # Linux (GNU sed)
  sed -i '/<\/urlset>/d' "$SITEMAP_FILE"
fi

# DATE=$(echo $(cat $WISDOM_FILE|grep "date:"|cut -f2 -d' ')T00:00:00Z)
# DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ") # Format Google ISO 8601 UTC 
DATE=$(date -r $WISDOM_FILE  "+%Y-%m-%dT%H:%M:%SZ")

cat <<EOF >> "$SITEMAP_FILE"
  <url>
    <loc>${DOMAIN}/${ID}</loc>
    <lastmod>${DATE}</lastmod>
  </url>
</urlset>
EOF

echo "Info: The ID '${ID}' has been added to the sitemap $SITEMAP_FILE  with lastmod date ${DATE}."
