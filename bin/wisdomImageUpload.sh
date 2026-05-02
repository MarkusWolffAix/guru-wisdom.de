#!/usr/bin/env zsh

best_file="$HOME/Downloads/$1.png"
ID=$(basename $best_file .png) 
ONEDRIVE_DIR="$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/S3Storage"


# Create a temporary directory for image conversion
TMP_DIR=$(mktemp -d)

# Trap: Automatically deletes the temporary directory when the script exits or is aborted
trap 'rm -rf "$TMP_DIR"' EXIT

cwebp -q 80 -resize 1280 0 "$best_file" -o "$TMP_DIR/${ID}.webp" >/dev/null 2>&1
cwebp -q 80 -resize 640  0 "$best_file" -o "$TMP_DIR/${ID}_thumb.webp" >/dev/null 2>&1
sips -Z 640 -s format jpeg -s formatOptions 80 "$best_file --out "$TMP_DIR/${ID}.jpg" >/dev/null 2>&1

aws s3 cp "$TMP_DIR/${ID}.jpg" s3://guru-wisdom-first/images/${ID}.jpg  --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}.webp" s3://guru-wisdom-first/images/${ID}.webp  --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}_thumb.webp" s3://guru-wisdom-first/images/thumb/${ID}.webp  --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com

aws s3 cp "$TMP_DIR/${ID}.jpg" s3://guru-wisdom-secound/images/${ID}.jpg  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}.webp" s3://guru-wisdom-secound/images/${ID}.webp  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}_thumb.webp" s3://guru-wisdom-secound/images/thumb/${ID}.webp  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com

cp "$TMP_DIR/${ID}.jpg" "$ONEDRIVE_DIR/images/${ID}.jpg"
cp "$TMP_DIR/${ID}.webp" "$ONEDRIVE_DIR/images/${ID}.webp"
cp "$TMP_DIR/${ID}_thumb.webp" "$ONEDRIVE_DIR/images/thumb/${ID}.webp"
