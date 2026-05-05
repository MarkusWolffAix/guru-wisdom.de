#!/bin/bash

cmd="$1"
# cmd="sudo sed -i -E 's/^#?PermitRootLogin.*/PermitRootLogin no/; s/^#?PubkeyAuthentication.*/PubkeyAuthentication yes/; s/^#?PasswordAuthentication.*/PasswordAuthentication no/; s/^#?X11Forwarding.*/X11Forwarding no/' /etc/ssh/sshd_config && sudo systemctl restart sshd"
# 1. Tippfehler bei proxy10 korrigiert
allServer=(proxy10 proxy110 test20 prod30 prod130)

# 2. Richtig über ein Bash-Array iterieren
for server in "${allServer[@]}"; do
    echo $cmd on Server $server
    ssh "$server" "$cmd"
done
