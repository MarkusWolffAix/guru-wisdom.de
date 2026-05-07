#!/bin/bash

cmd="$1"
# cmd="sudo sed -i -E 's/^#?PermitRootLogin.*/PermitRootLogin no/; s/^#?PubkeyAuthentication.*/PubkeyAuthentication yes/; s/^#?PasswordAuthentication.*/PasswordAuthentication no/; s/^#?X11Forwarding.*/X11Forwarding no/' /etc/ssh/sshd_config && sudo systemctl restart sshd"


cmd='git config --global user.email "Markus.Wolff@guru-wisdom.com" && git config --global user.name "Markus Wolff"' 
allServer=(proxy10 proxy110 test20 prod30 prod130)

# 2. Richtig über ein Bash-Array iterieren
for server in "${allServer[@]}"; do
    echo $cmd on Server $server
    ssh "$server" "$cmd"
done
