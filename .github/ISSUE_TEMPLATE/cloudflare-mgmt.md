---
name: Cloudflare Management
about: Managing DNS records, Load Balancing pools, and Proxy settings.
title: '[OPS] '
labels: cloudflare, dns, infrastructure
assignees: 'Markus Wolff'
---

### 🎯 Objective
*Describe the change: e.g., Update A-Record for Yii3, Setup Load Balancer for High Availability*

### 🌐 DNS Management
- [ ] **Record Type:** (A, AAAA, CNAME, TXT, MX)
- [ ] **Name:** (e.g., api, www, @)
- [ ] **Content/Value:** (IP address or Target)
- [ ] **TTL:** (Auto or specific time)
- [ ] **Proxy Status:** Proxied (Orange Cloud) / DNS Only (Grey Cloud)

### ⚖️ Load Balancing
- [ ] **Hostname:** (The entry point for the LB)
- [ ] **Pools:**
    - [ ] Pool A (Hetzner FSN): Active/Standby
    - [ ] Pool B (Hetzner NBG): Active/Standby
- [ ] **Traffic Steering:** (Geo-steering, Random, Proximity, or Failover)
- [ ] **Health Check:** (Endpoint path, e.g., `/health`, Port, Type)

### 🛡️ Security & Rules
- [ ] **WAF:** Any custom firewall rules required?
- [ ] **Page Rules:** (e.g., Cache Level, Always Use HTTPS, Edge Cache TTL)

### 📝 Task List
- [ ] Apply changes in Cloudflare Dashboard.
- [ ] Verify DNS propagation (e.g., via `dig`).
- [ ] Check Load Balancer pool health status.
- [ ] Test the proxy behavior (SSL/TLS handshake).
