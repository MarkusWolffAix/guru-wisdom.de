#!/bin/bash

# ==========================================
# KONFIGURATION (Globale Variablen)
# ==========================================
HETZNER_TOKEN="DEIN_API_TOKEN"
NETWORK_ID="123456"
ALIAS_IP="10.0.1.2"
SERVER_NAME="testserver-yii3"
LOCATION="nbg1"
SERVER_TYPE="cx11"
USER="www-data"

# ==========================================
# 1. CLOUD-INIT DEFINIEREN
# ==========================================
# Hinweis: Wir nutzen EOF ohne Anführungszeichen, damit Shell-Variablen ($ALIAS_IP) ersetzt werden.
CLOUD_INIT=$(cat << EOF
#cloud-config
timezone: Europe/Berlin
write_files:
  - path: /etc/network/interfaces.d/60-alias-ip.cfg
    permissions: '0644'
    owner: root:root
    content: |
      allow-hotplug enp7s0:0
      iface enp7s0:0 inet static
          address $ALIAS_IP
          netmask 255.255.255.255

users:
  - name: markus
    groups: [sudo, docker]
    shell: /bin/bash
    sudo: ALL=(ALL) NOPASSWD:ALL
    ssh_authorized_keys:
      - ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCBRO4EGqop11BukSLm27PhW/guswjG47Q5BogJxisasflXq9CyO2AIPwEYb3w4vakFGqW2ELVyLSK5HUfkzpjCnetPu48FHk2vl6uKhNfTblwg4DIErJQYeecftLPFunA3P5qzqDRi1BJQG1jWWkaanxN0WnA/D7fVqU4QE1WxqKWPRQMEPpclubTxpsYflo+0RZOofTFKdd4qpZdeAc2hv0ZWWVJAI66YiJYisComVsDdNFDGdQww6pmg+lzXo2a4A6ynBi+4a09IXPD1oA53kR8nbYJN4f2TlHKQDWv4z8i2TOSuUfGic6QGTwT4EdwVCxVYrqhY+Pck1B/k/rTl
  - name: www-data
    groups: [docker]
    shell: /bin/bash
    ssh_authorized_keys:
      - ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIF7/dM1dwDJOKMjtC8s51/eYRs+SYs5T0BEijwM7Pn0w github-actions-guru-wisdom
 
package_update: true
package_upgrade: true
packages: [ca-certificates, curl, gnupg, sudo, git]

runcmd:
  - ip addr add $ALIAS_IP/32 dev enp7s0 || true
  - install -m 0755 -d /etc/apt/keyrings
  - curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc
  - chmod a+r /etc/apt/keyrings/docker.asc
  - |
    tee /etc/apt/sources.list.d/docker.sources <<DOCKEREOF
    Types: deb
    URIs: https://download.docker.com/linux/debian
    Suites: \$(. /etc/os-release && echo "\$VERSION_CODENAME")
    Components: stable
    Architectures: \$(dpkg --print-architecture)
    Signed-By: /etc/apt/keyrings/docker.asc
    DOCKEREOF
  - apt-get update
  - apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  - mkdir -p /opt/guru-wisdom.de && chown $USER:$GROUP/opt/guru-wisdom.de
  - sudo -u $USER git clone https://github.com/MarkusWolffAix/t3.guru-wisdom.de.git /opt/guru-wisdom.de
  - cd /opt/guru-wisdom.de && docker compose -f docker-compose.dev.yml up -d
EOF
)

# ==========================================
# 2. PAYLOAD BAUEN (Schritt 3 & 4)
# ==========================================
jq -n \
  --arg name "$SERVER_NAME" \
  --arg type "$SERVER_TYPE" \
  --arg image "debian-12" \
  --arg loc "$LOCATION" \
  --arg net "$NETWORK_ID" \
  --arg ud "$CLOUD_INIT" \
  '{name: $name, server_type: $type, image: $image, location: $loc, networks: [($net | tonumber)], user_data: $ud}' > payload.json

# ==========================================
# 3. SERVER ERSTELLEN (Schritt 5)
# ==========================================
echo "Sende Erstellungsbefehl an Hetzner..."
RESPONSE=$(curl -s -X POST "https://api.hetzner.cloud/v1/servers" \
     -H "Authorization: Bearer $HETZNER_TOKEN" \
     -H "Content-Type: application/json" \
     -d @payload.json)

SERVER_ID=$(echo $RESPONSE | jq -r '.server.id')

if [ "$SERVER_ID" == "null" ]; then
    echo "Fehler bei der Erstellung:"
    echo $RESPONSE | jq .
    exit 1
fi

echo "Server erfolgreich erstellt! ID: $SERVER_ID"

# ==========================================
# 4. ALIAS-IP ZUWEISEN (Schritt 7)
# ==========================================
echo "Konfiguriere Alias-IP $ALIAS_IP im Hetzner-Netzwerk..."
curl -s -X POST "https://api.hetzner.cloud/v1/servers/$SERVER_ID/actions/change_alias_ips" \
     -H "Authorization: Bearer $HETZNER_TOKEN" \
     -H "Content-Type: application/json" \
     -d "{
           \"network\": $NETWORK_ID,
           \"alias_ips\": [\"$ALIAS_IP\"]
         }" | jq -r '.action.status'

echo "Fertig! Der Server fährt hoch und installiert Docker/Yii3."
rm payload.json