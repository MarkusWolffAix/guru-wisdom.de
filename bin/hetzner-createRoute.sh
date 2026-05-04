# Deine Netzwerk ID
NETWORK_ID="12140000"

# Route via API hinzufügen
curl -X POST "https://api.hetzner.cloud/v1/networks/$NETWORK_ID/actions/add_route" \
     -H "Authorization: Bearer $(security find-generic-password -a "markuswolff" -s "guruwisdom_HETZNER_API_TOKEN_RW" -w | tr -d '\n')" \
     -H "Content-Type: application/json" \
     -d '{
           "destination": "0.0.0.0/0",
           "gateway": "10.0.0.254"
         }'
