#!/bin/bash

# ==========================================
# CONFIGURATION (Global Variables)
# ==========================================
HETZNER_TOKEN=$(security find-generic-password -a "markuswolff" -s "guruwisdom_HETZNER_API_TOKEN_RW" -w | tr -d '\n')


# get Firewall
# curl -H "Authorization: Bearer $HETZNER_TOKEN" https://api.hetzner.cloud/v1/firewalls
# exit 0

FIREWALL_ID=2015312


MY_IP=$(curl -s4 https://ifconfig.me)

if [ -z "$MY_IP" ]; then
    echo "Error: Could not retrieve the own IP address."
    exit 1
fi

echo "Current IP is: ${MY_IP}. Updating Hetzner Firewall..."

JSON_PAYLOAD=$(cat <<EOF
{
  "rules": [
    {
      "direction": "in",
      "protocol": "tcp",
      "port": "22",
      "source_ips": [
        "${MY_IP}/32"
      ],
      "description": "dynamic Home-IP "
    },

    {
      "direction": "in",
      "protocol": "tcp",
      "port": "443",
      "source_ips": ["0.0.0.0/0", "::/0"],
      "description": "HTTPS Global"
    }
  ]
}
EOF
)

curl -X POST "https://api.hetzner.cloud/v1/firewalls/${FIREWALL_ID}/actions/set_rules" \
     -H "Authorization: Bearer ${HETZNER_TOKEN}" \
     -H "Content-Type: application/json" \
     -d "$JSON_PAYLOAD" \
     -s -o /dev/null -w "HTTP Status: %{http_code}\n"


