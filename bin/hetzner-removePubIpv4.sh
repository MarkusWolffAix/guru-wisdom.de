#!/bin/bash

# ==========================================
# CONFIGURATION
# ==========================================
HETZNER_TOKEN=$(security find-generic-password -a "markuswolff" -s "guruwisdom_HETZNER_API_TOKEN_RW" -w | tr -d '\n')
SERVER_NAME="test"

# ==========================================
# 1. FETCH IDs
# ==========================================
echo "Fetching data from Hetzner API..."
SERVER_JSON=$(curl -s -H "Authorization: Bearer $HETZNER_TOKEN" \
  "https://api.hetzner.cloud/v1/servers?name=$SERVER_NAME")

SERVER_ID=$(echo "$SERVER_JSON" | jq -r '.servers[0].id // empty')
PRIMARY_IP_ID=$(echo "$SERVER_JSON" | jq -r '.servers[0].public_net.ipv4.id // empty')

if [ -z "$SERVER_ID" ] || [ -z "$PRIMARY_IP_ID" ] || [ "$PRIMARY_IP_ID" == "null" ]; then
    echo "Error: Server not found, or it already has no IPv4 address assigned!"
    exit 1
fi

echo "Server ID: $SERVER_ID"
echo "IPv4 ID: $PRIMARY_IP_ID"

# ==========================================
# 2. POWER OFF SERVER
# ==========================================
echo "Powering off the server (required to unassign IP)..."
curl -s -X POST -H "Authorization: Bearer $HETZNER_TOKEN" \
  "https://api.hetzner.cloud/v1/servers/$SERVER_ID/actions/poweroff" > /dev/null

# Give the Hetzner infrastructure a few seconds to register the power state
sleep 5

# ==========================================
# 3. UNASSIGN IP
# ==========================================
echo "Unassigning the IPv4 address from the server..."
curl -s -X POST -H "Authorization: Bearer $HETZNER_TOKEN" \
  "https://api.hetzner.cloud/v1/primary_ips/$PRIMARY_IP_ID/actions/unassign" > /dev/null

sleep 2

# ==========================================
# 4. DELETE IP (Prevent billing)
# ==========================================
echo "Permanently deleting the unassigned IPv4 address to prevent extra costs..."
curl -s -X DELETE -H "Authorization: Bearer $HETZNER_TOKEN" \
  "https://api.hetzner.cloud/v1/primary_ips/$PRIMARY_IP_ID" > /dev/null

# ==========================================
# 5. POWER ON SERVER
# ==========================================
echo "Powering the server back on..."
curl -s -X POST -H "Authorization: Bearer $HETZNER_TOKEN" \
  "https://api.hetzner.cloud/v1/servers/$SERVER_ID/actions/poweron" > /dev/null

echo "Done! The server is now booting up as a pure stealth node (private network only)."
