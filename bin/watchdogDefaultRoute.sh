#!/bin/bash

# Standard-Gateway
GATEWAY="10.0.0.1"
LOGFILE="/var/log/route-fix.log"

# Check default route and fix if necessary
if ! ip route show default | grep -q "$GATEWAY"; then
    
    # Remove existing default route 
    ip route del default 2>/dev/null

    #Set default route to the correct gateway Hetzner
    ip route add default via "$GATEWAY"
    
    # Log entry 
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Route fixed to $GATEWAY." >> "$LOGFILE"
    
fi 