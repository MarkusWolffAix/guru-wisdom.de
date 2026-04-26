#!/usr/bin/env zsh

best_file="$HOME/Downloads/$1.png"
ID=$(basename $best_file .png) 

# Create a temporary directory for image conversion
TMP_DIR=$(mktemp -d)
# Trap: Automatically deletes the temporary directory when the script exits or is aborted
trap 'rm -rf "$TMP_DIR"' EXIT

sips --resampleWidth 1280 -s format jpeg "$best_file" --out "$TMP_DIR/${ID}.jpg" >/dev/null 2>&1
sips -s format jpeg -s formatOptions high "$best_file" --out "$TMP_DIR/${ID}_org.jpg" >/dev/null 2>&1

aws s3 cp "$TMP_DIR/${ID}.jpg" s3://guru-wisdom-first/images/${ID}.jpg  --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}_org.jpg" s3://guru-wisdom-first/images/org/${ID}.jpg  --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}.jpg" s3://guru-wisdom-secound/images/${ID}.jpg  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}_org.jpg" s3://guru-wisdom-secound/images/org/${ID}.jpg  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
