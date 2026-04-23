#!/bin/zsh

# ==========================================
# CONFIGURATION
# ==========================================

# 1. Paths
SOURCE_DIR="/Users/markuswolff/Documents/Arbeit/Development/guru-wisdom.de"
ONEDRIVE_DIR="$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/guru-wisdom-project"
LOG_FILE="$HOME/Library/Logs/guru_backup_full.log"

# 2. Network & Fritz!Box Settings
HOME_ROUTER_MAC="2c:91:ab:72:ff:b0"
FRITZ_IP="fritz.box"
FRITZ_USER="datauser"
# Passwort dynamisch und sicher aus dem macOS Schlüsselbund auslesen
FRITZ_PASS=$(security find-generic-password -a "$FRITZ_USER" -s "FritzBoxNAS_Backup" -w)
FRITZ_SHARE="FRITZ.NAS"
FRITZ_MOUNT_PATH="/Volumes/$FRITZ_SHARE"
FRITZ_TARGET_DIR="$FRITZ_MOUNT_PATH/Volume/Backup/guru-wisdom-project"

ADMIN_EMAIL="markus.wolff@mailbox.org"

# 3. Exclude List
EXCLUDES=(
    --exclude='vendor/'
    --exclude='runtime/'
    --exclude='web/assets/'
    --exclude='node_modules/'
    --exclude='.DS_Store'
)

# ==========================================
# FUNCTIONS
# ==========================================

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

send_notification() {
    local status="$1"
    local message="$2"
    local subject="Guru-Wisdom Backup: $status"
    
    # Send email
    echo "$message" | mail -s "$subject" "$ADMIN_EMAIL"
    
    # Desktop notification
    osascript -e "display notification \"$message\" with title \"$subject\""
}

# ==========================================
# SCRIPT START
# ==========================================
log_message "--- Full project backup started ---"

if [[ ! -d "$SOURCE_DIR" ]]; then
    log_message "ERROR: Source directory $SOURCE_DIR not found! Aborting."
    send_notification "⚠️ Error" "Source directory not found. Aborting."
    exit 1
fi

# ------------------------------------------
# PART 1: FRITZ!BOX BACKUP (Local NAS)
# ------------------------------------------

CURRENT_GATEWAY=$(route -n get default 2>/dev/null | awk '/gateway/ {print $2}')
CURRENT_MAC=$(arp -n "$CURRENT_GATEWAY" 2>/dev/null | awk '{print $4}')

if [[ "$CURRENT_MAC" == "$HOME_ROUTER_MAC" ]]; then
    log_message "Home Router identified ($CURRENT_MAC). Checking Fritz!NAS..."
    
    if [[ ! -d "$FRITZ_MOUNT_PATH" ]]; then
        log_message "Fritz!NAS not mounted. Attempting mount..."
        osascript -e "try" -e "mount volume \"smb://${FRITZ_USER}:${FRITZ_PASS}@${FRITZ_IP}/${FRITZ_SHARE}\"" -e "end try"
        sleep 3 
    fi

    if [[ -d "$FRITZ_MOUNT_PATH" ]]; then
        log_message "Starting rsync to Fritz!Box (Full Project)..."
        mkdir -p "$FRITZ_TARGET_DIR"
        
        if rsync -av --checksum --delete "${EXCLUDES[@]}" "$SOURCE_DIR/" "$FRITZ_TARGET_DIR/" >> "$LOG_FILE" 2>&1; then
            log_message "Fritz!Box full backup completed successfully."
            send_notification "✅ Success" "Fritz!Box Backup completed."
        else
            log_message "ERROR: Rsync to Fritz!Box failed."
            send_notification "⚠️ Error" "Rsync to Fritz!Box failed. Check logs."
        fi
    else
        log_message "ERROR: Could not mount Fritz!NAS."
        send_notification "⚠️ Error" "Could not mount Fritz!NAS."
    fi
else
    log_message "INFO: Not on Home Network (Router MAC: $CURRENT_MAC). Skipping Fritz!Box backup."
fi

# ------------------------------------------
# PART 2: ONEDRIVE BACKUP (Cloud)
# ------------------------------------------

# Fixed macOS ping timeout flag (-t instead of -W)
if ping -c 1 -t 2 8.8.8.8 &> /dev/null; then
    log_message "Internet connection OK. Checking OneDrive..."
    
    if ! pgrep -x "OneDrive" > /dev/null; then
        log_message "Starting OneDrive app..."
        open -a "OneDrive"
        sleep 5 
    fi

    if [[ -d "$ONEDRIVE_DIR" || -d "$(dirname "$ONEDRIVE_DIR")" ]]; then
        log_message "Starting rsync to local OneDrive folder (Full Project)..."
        mkdir -p "$ONEDRIVE_DIR"
        
        if rsync -av --checksum --delete "${EXCLUDES[@]}" "$SOURCE_DIR/" "$ONEDRIVE_DIR/" >> "$LOG_FILE" 2>&1; then
            log_message "Local project sync to OneDrive finished successfully."
            send_notification "✅ Success" "OneDrive Backup completed."
        else
            log_message "ERROR: Rsync to OneDrive failed."
            send_notification "⚠️ Error" "Rsync to OneDrive failed. Check logs."
        fi
    else
        log_message "ERROR: OneDrive path not found."
        send_notification "⚠️ Error" "OneDrive path not found."
    fi
else
    log_message "INFO: No internet connection. Skipping OneDrive backup."
fi

log_message "--- Full backup process finished ---"
echo "Project backup finished. Log: $LOG_FILE"