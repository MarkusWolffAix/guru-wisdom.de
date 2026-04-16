---
name: Hetzner Infrastructure
about: Managing Hetzner Cloud VMs, High Availability, and S3 Object Storage.
title: '[OPS] '
labels: infrastructure, hetzner
assignees: 'MarkusWolffAix'
---

### 🎯 Objective
### 🖥 VM Resources
- [ ] **Server Type:** (e.g., CPX11, CX21, Dedicated)
- [ ] **Location:** (e.g., fsn1, nbg1, hel1)
- [ ] **Backups:** Enable daily backups? (Yes/No)
- [ ] **Scaling:** Vertical (Resize) or Horizontal (Add Node)?

### ⚖️ High Availability (HA)
- [ ] **Load Balancer:** Setup/Update Hetzner Load Balancer.
- [ ] **Floating IP:** Assign/Reassign Floating IP for failover.
- [ ] **Health Checks:** Define target port and protocol.
- [ ] **SSL/TLS:** Certificate management (Let's Encrypt via Hetzner or manual).

### 📦 S3 Object Storage
- [ ] **Bucket Name:** - [ ] **Access Control:** Public vs. Private.
- [ ] **Credentials:** Generate/Rotate Access & Secret Keys.
- [ ] **App Integration:** Update `config/` in Yii3 to use S3 (e.g., via AWS SDK or Flysystem).

### 📝 Task List
- [ ] Provision resources in Hetzner Cloud Console / via `hcloud` CLI.
- [ ] Update Firewall / Security Groups.
- [ ] Verify connectivity from VM to S3.
- [ ] Test failover (if HA is involved).
- [ ] Document architecture changes.
