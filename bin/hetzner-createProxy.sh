#!/bin/bash

# ==========================================
# CONFIGURATION (Global Variables)
# ==========================================
HETZNER_TOKEN=$(security find-generic-password -a "markuswolff" -s "guruwisdom_HETZNER_API_TOKEN_RW" -w | tr -d '\n')

# --- PROD ENVIRONMENT ---
SERVER_NAME="proy10"
ALIAS_IP_1="10.0.0.10"
ALIAS_IP_2="10.0.1.10"
USER="git"
LOCATION="nbg1"

# --- GLOBAL SETTINGS ---
NETWORK_ID="12140000"
PLACEMENT_GROUP_NAME="guru-wisdom.de"
PLACEMENT_GROUP_ID="1572343" 
SERVER_TYPE="cx33"
IMAGE="debian-13" 

# ==========================================
# 1. DEFINE CLOUD-INIT
# ==========================================
CLOUD_INIT=$(cat << EOF
#cloud-config
timezone: Europe/Berlin

write_files:
  - path: /etc/network/interfaces.d/60-alias-ip.cfg
    permissions: '0644'
    owner: root:root
    content: |
      auto enp7s0:0
      iface enp7s0:0 inet static
        address $ALIAS_IP_1
        netmask 255.255.255.255

      auto enp7s0:1
      iface enp7s0:1 inet static
          address $ALIAS_IP_2
          netmask 255.255.255.255
          
  - path: /etc/profile.d/99-custom-aliases.sh
    permissions: '0644'
    owner: root:root
    content: |
      alias sgit='sudo su - git'

users:
  - name: markus
    groups: [sudo, docker]
    shell: /bin/bash
    sudo: ALL=(ALL) NOPASSWD:ALL
    lock_passwd: false
    ssh_authorized_keys:
      - ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCBRO4EGqop11BukSLm27PhW/guswjG47Q5BogJxisasflXq9CyO2AIPwEYb3w4vakFGqW2ELVyLSK5HUfkzpjCnetPu48FHk2vl6uKhNfTblwg4DIErJQYeecftLPFunA3P5qzqDRi1BJQG1jWWkaanxN0WnA/D7fVqU4QE1WxqKWPRQMEPpclubTxpsYflo+0RZOofTFKdd4qpZdeAc2hv0ZWWVJAI66YiJYisComVsDdNFDGdQww6pmg+lzXo2a4A6ynBi+4a09IXPD1oA53kR8nbYJN4f2TlHKQDWv4z8i2TOSuUfGic6QGTwT4EdwVCxVYrqhY+Pck1B/k/rTl
  - name: git
    groups: [www-data, docker]
    shell: /bin/bash
    lock_passwd: false
    ssh_authorized_keys:
      - ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIF7/dM1dwDJOKMjtC8s51/eYRs+SYs5T0BEijwM7Pn0w github-actions-guru-wisdom
 
package_update: true
package_upgrade: true
packages: [ca-certificates, curl, gnupg, sudo, git, lynx]

runcmd:
  # --- Netzwerk & Cron ---
  - (crontab -l 2>/dev/null; echo "@reboot sleep 5 && /sbin/ip route add default via 10.0.0.1") | crontab -
  - mkdir -p /etc/resolvconf/resolv.conf.d
  - echo "nameserver 1.1.1.1" >> /etc/resolvconf/resolv.conf.d/head
  - echo "nameserver 8.8.8.8" >> /etc/resolvconf/resolv.conf.d/head  
  - resolvconf -u || true  
  - ip route del default || true
  - ip route add default via 10.0.0.1 dev enp7s0
  - ip addr add $ALIAS_IP_1/32 dev enp7s0 || true
  - ip addr add $ALIAS_IP_2/32 dev enp7s0 || true

  # --- System Updates & Tools ---
  - apt update
  - DEBIAN_FRONTEND=noninteractive apt upgrade -y
  - apt install -y ca-certificates curl gnupg lsb-release sudo git lynx

  # --- Docker Installation ---
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
  - apt update
  - apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

  # --- App Setup & Deployment ---
  - mkdir -p /opt/guru-wisdom.de
  - chown $USER:www-data /opt/guru-wisdom.de
  - runuser -u $USER -- git clone https://github.com/MarkusWolffAix/guru-wisdom.de.git /opt/guru-wisdom.de
  
  # Alles ab hier läuft als zusammenhängendes Skript in dem Ordner ab
  - |
      cd /opt/guru-wisdom.de

      # .env Datei anlegen
      cat <<ENV_EOF > .env
      APP_ENV=$ENV
      ENV_EOF
      
      # Rechte für die .env an den User übergeben
      chown $USER:www-data .env

      # sed als User ausführen
      runuser -u $USER -- bash -c "sed 's|SERVER_NAME=.*|SERVER_NAME=http://${ALIAS_IP_2}|' docker-compose.prod.yml > docker-compose.yml"

      # Docker Compose als User starten
      runuser -u $USER -- bash -c "docker compose -f docker-compose.yml up -d"

EOF
)

# ==========================================
# 2. BUILD PAYLOAD
# ==========================================

jq -n -c \
  --arg name "$SERVER_NAME" \
  --arg type "$SERVER_TYPE" \
  --arg img "$IMAGE" \
  --arg loc "$LOCATION" \
  --argjson net "$NETWORK_ID" \
  --argjson pg "$PLACEMENT_GROUP_ID" \
  --arg env "$ENV" \
  --arg ud "$CLOUD_INIT" \
  '{
    name: $name, 
    server_type: $type, 
    image: $img, 
    location: $loc,
    networks: [$net],
    placement_group: $pg,
    labels: {
      "env": $env,
      "project": "guru-wisdom",
      "managed_by": "bash-script"
    },
    public_net: {
      enable_ipv4: false,
      enable_ipv6: false
    }, 
    user_data: $ud
  }' > /tmp/payload.json

# DEBUG: Zeig uns das JSON
echo "Kontrolle der Network-ID im JSON:"
jq '.networks' /tmp/payload.json

# ==========================================
# 3. CREATE SERVER
# ==========================================
echo "Sending creation command to Hetzner..."
RESPONSE=$(curl -s -X POST "https://api.hetzner.cloud/v1/servers" \
     -H "Authorization: Bearer $HETZNER_TOKEN" \
     -H "Content-Type: application/json" \
     --data-binary @/tmp/payload.json)

SERVER_ID=$(echo "$RESPONSE" | jq -r '.server.id // empty')

if [ -z "$SERVER_ID" ]; then
    echo "Error during creation:"
    echo "$RESPONSE" | jq .
    exit 1
fi

echo "Server successfully created! ID: $SERVER_ID"

# ==========================================
# 4. ASSIGN ALIAS IPs
# ==========================================
echo "Waiting briefly for server initialization before assigning Alias IPs..."
sleep 5
echo "Configuring alias IPs $ALIAS_IP_1 and $ALIAS_IP_2 in the Hetzner network..."
curl -s -X POST "https://api.hetzner.cloud/v1/servers/$SERVER_ID/actions/change_alias_ips" \
     -H "Authorization: Bearer $HETZNER_TOKEN" \
     -H "Content-Type: application/json" \
     -d "{
           \"network\": $NETWORK_ID,
           \"alias_ips\": [\"$ALIAS_IP_1\", \"$ALIAS_IP_2\"]
         }" | jq -r '.action.status'

echo "Finish! The server is provisioning in the background. It will take 1-3 minutes until Docker/Yii3 is fully running."
rm /tmp/payload.json
