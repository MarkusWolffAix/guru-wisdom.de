#!/usr/bin/env zsh

# 1. Guard Clause: Check if an argument was provided
if [[ -z "$1" ]]; then
    echo "Error: Please provide a filename (without the .png extension)."
    exit 1
fi

best_file="$HOME/Downloads/$1.png"

# 2. Guard Clause: Check if the file actually exists
if [[ ! -f "$best_file" ]]; then
    echo "Error: File '$best_file' not found."
    exit 1
fi

# 3. Quote variables to safely handle filenames with spaces
ID=$(basename "$best_file" .png) 
ONEDRIVE_DIR="$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/S3Storage"

# Create a temporary directory for image conversion
TMP_DIR=$(mktemp -d)

# Trap: Automatically deletes the temporary directory when the script exits or is aborted
trap 'rm -rf "$TMP_DIR"' EXIT

echo "Converting images..."
cwebp -q 80 -resize 1280 0 "$best_file" -o "$TMP_DIR/${ID}.webp" >/dev/null 2>&1
cwebp -q 80 -resize 640  0 "$best_file" -o "$TMP_DIR/${ID}_thumb.webp" >/dev/null 2>&1
# FIX: Added the missing closing quote below
sips -Z 640 -s format jpeg -s formatOptions 80 "$best_file" --out "$TMP_DIR/${ID}.jpg" >/dev/null 2>&1

echo "Uploading to S3 (Nürnberg)..."
aws s3 cp "$TMP_DIR/${ID}.jpg" s3://guru-wisdom-first/images/${ID}.jpg  --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}.webp" s3://guru-wisdom-first/images/${ID}.webp  --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}_thumb.webp" s3://guru-wisdom-first/images/thumb/${ID}.webp  --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com

echo "Uploading to S3 (Helsinki)..."
aws s3 cp "$TMP_DIR/${ID}.jpg" s3://guru-wisdom-secound/images/${ID}.jpg  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}.webp" s3://guru-wisdom-secound/images/${ID}.webp  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}_thumb.webp" s3://guru-wisdom-secound/images/thumb/${ID}.webp  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com

echo "Backing up to OneDrive..."
# 4. Ensure destination directories exist before copying
mkdir -p "$ONEDRIVE_DIR/images/thumb"
cp "$TMP_DIR/${ID}.jpg" "$ONEDRIVE_DIR/images/${ID}.jpg"
cp "$TMP_DIR/${ID}.webp" "$ONEDRIVE_DIR/images/${ID}.webp"
cp "$TMP_DIR/${ID}_thumb.webp" "$ONEDRIVE_DIR/images/thumb/${ID}.webp"

echo "✅ Deployment complete!"