#cloud-config

# 1. Create the user 'markus' and configure SSH/Sudo
users:
  - name: markus
    groups: [sudo, docker]
    shell: /bin/bash
    sudo: ALL=(ALL) NOPASSWD:ALL
    ssh_authorized_keys:
      - ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCBRO4EGqop11BukSLm27PhW/guswjG47Q5BogJxisasflXq9CyO2AIPwEYb3w4vakFGqW2ELVyLSK5HUfkzpjCnetPu48FHk2vl6uKhNfTblwg4DIErJQYeecftLPFunA3P5qzqDRi1BJQG1jWWkaanxN0WnA/D7fVqU4QE1WxqKWPRQMEPpclubTxpsYflo+0RZOofTFKdd4qpZdeAc2hv0ZWWVJAI66YiJYisComVsDdNFDGdQww6pmg+lzXo2a4A6ynBi+4a09IXPD1oA53kR8nbYJN4f2TlHKQDWv4z8i2TOSuUfGic6QGTwT4EdwVCxVYrqhY+Pck1B/k/rTl

# 2. Update and upgrade the system packages
package_update: true
package_upgrade: true

# 3. Install necessary base dependencies (added git)
packages:
  - ca-certificates
  - curl
  - gnupg
  - sudo
  - git

# 4. Run commands to install Docker, setup repo and start container
runcmd:
  # --- Docker Installation ---
  - install -m 0755 -d /etc/apt/keyrings
  - curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc
  - chmod a+r /etc/apt/keyrings/docker.asc
  - |
    tee /etc/apt/sources.list.d/docker.sources <<EOF
    Types: deb
    URIs: https://download.docker.com/linux/debian
    Suites: $(. /etc/os-release && echo "$VERSION_CODENAME")
    Components: stable
    Architectures: $(dpkg --print-architecture)
    Signed-By: /etc/apt/keyrings/docker.asc
    EOF
  - apt-get update
  - apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

  # --- Project Setup ---
  # Create directory and set permissions
  - mkdir -p /opt/guru-wisdom.de
  - chown markus:markus /opt/guru-wisdom.de

  # Clone the repository as user 'markus'
  - sudo -u markus git clone https://github.com/MarkusWolffAix/t3.guru-wisdom.de.git /opt/guru-wisdom.de

  # Start the Docker container
  # (Assumes there is a docker-compose.yml in the repo root)
  - cd /opt/guru-wisdom.de && docker compose -f docker-compose.dev.yml up -d