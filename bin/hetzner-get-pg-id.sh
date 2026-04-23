#!/bin/bash

# ==========================================
# KONFIGURATION
# ==========================================
# Holt den Token aus deinem Mac Schlüsselbund
HETZNER_TOKEN=$(security find-generic-password -a "markuswolff" -s "guruwisdom_HETZNER_API_TOKEN_RW" -w | tr -d '\n')

# Name der Placement Group (entweder aus dem ersten Argument oder Standard)
PG_NAME=${1:-"guru-wisdom.de"}

if [ -z "$HETZNER_TOKEN" ]; then
    echo "Fehler: HETZNER_TOKEN_RW konnte nicht aus dem Schlüsselbund gelesen werden."
    exit 1
fi

# ==========================================
# ABFRAGE
# ==========================================
echo "Suche ID für Placement Group '$PG_NAME'..."

PLACEMENT_GROUP_ID=$(curl -s -H "Authorization: Bearer $HETZNER_TOKEN" \
     "https://api.hetzner.cloud/v1/placement_groups" | \
     jq -r --arg NAME "$PG_NAME" '.placement_groups[] | select(.name==$NAME) | .id')

# ==========================================
# AUSWERTUNG
# ==========================================
if [ -z "$PLACEMENT_GROUP_ID" ] || [ "$PLACEMENT_GROUP_ID" == "null" ]; then
    echo "❌ Fehler: Placement Group '$PG_NAME' nicht gefunden!"
    exit 1
else
    echo "✅ Gefundene PG-ID: $PLACEMENT_GROUP_ID"
    # Optional: Nur die ID ausgeben, falls du das Skript in anderen Skripten nutzen willst
    # echo "$PLACEMENT_GROUP_ID"
fi