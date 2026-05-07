#!/bin/bash

# --- Configuration ---
HETZNER_TOKEN=$(security find-generic-password -a "markuswolff" -s "guruwisdom_HETZNER_API_TOKEN_RW" -w | tr -d '\n')


# get Firewall
# curl -H "Authorization: Bearer $HETZNER_TOKEN" https://api.hetzner.cloud/v1/firewalls
# exit 0

FIREWALL_ID=2015312


echo "Starting Hetzner Firewall Sync..."

# 1. Fetch all servers that have a public IPv4 address
# jq filters the JSON response and returns a list of pure server IDs
SERVERS_WITH_IPV4=$(curl -s -H "Authorization: Bearer $HETZNER_TOKEN" "https://api.hetzner.cloud/v1/servers" | jq -r '.servers[] | select(.public_net.ipv4.ip != null) | .id')

if [ -z "$SERVERS_WITH_IPV4" ]; then
    echo "No servers with an IPv4 address found."
    exit 0
fi

# 2. Fetch servers that are ALREADY assigned to the firewall
ALREADY_IN_FW=$(curl -s -H "Authorization: Bearer $HETZNER_TOKEN" "https://api.hetzner.cloud/v1/firewalls/$FIREWALL_ID" | jq -r '.firewall.applied_to[]? | select(.type=="server") | .server.id')

# 3. Compare and filter out the missing servers
MISSING_SERVERS=()

for id in $SERVERS_WITH_IPV4; do
    # Check if the ID exists in the list of already applied servers
    if ! echo "$ALREADY_IN_FW" | grep -qw "$id"; then
        MISSING_SERVERS+=("$id")
    fi
done

# 4. If no servers are missing, exit the script
if [ ${#MISSING_SERVERS[@]} -eq 0 ]; then
    echo "Everything is up to date! All servers with an IPv4 address are already in the firewall."
    exit 0
fi

echo "Found new servers for the firewall: ${MISSING_SERVERS[*]}"

# 5. Dynamically build the JSON payload for the new servers
JSON_PAYLOAD='{"apply_to": ['

# Iterate through the array of missing servers to construct the JSON array
for i in "${!MISSING_SERVERS[@]}"; do
    JSON_PAYLOAD+='{"type": "server", "server": {"id": '${MISSING_SERVERS[$i]}' }}'
    
    # Add a comma, except for the last element
    if [ $i -lt $((${#MISSING_SERVERS[@]} - 1)) ]; then
        JSON_PAYLOAD+=','
    fi
done

JSON_PAYLOAD+=']}'

# 6. Add the new servers to the firewall
# IMPORTANT: "apply_to_resources" only adds servers; it does not delete existing assignments.
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "https://api.hetzner.cloud/v1/firewalls/$FIREWALL_ID/actions/apply_to_resources" \
     -H "Authorization: Bearer $HETZNER_TOKEN" \
     -H "Content-Type: application/json" \
     -d "$JSON_PAYLOAD")

if [ "$HTTP_STATUS" -eq 201 ] || [ "$HTTP_STATUS" -eq 200 ]; then
    echo "Success! The new servers have been added to the firewall."
else
    echo "Error adding servers. HTTP status code: $HTTP_STATUS"
fi