#!/bin/bash

SERVER=$1

ssh $SERVER git clone git@github.com:MarkusWolffAix/env.git  
sudo cp env/conf/server/etc/ssh/sshd_config /etc/ssh/sshd_config && sudo systemctl restart sshd.service 
sudo cp -r env/conf/server/etc/letencrypt /etc
sudo cp -r env/conf/server/etc/nginx /etc && sudo systemcel restart nginx.service 
