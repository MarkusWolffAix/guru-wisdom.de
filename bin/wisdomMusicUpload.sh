TMP_DIR="$HOME/Downloads/"
ID=$(basename "$1" .mp3)
ONEDRIVE_DIR="$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/S3Storage"


aws s3 cp "$TMP_DIR/${ID}.mp3" s3://guru-wisdom-first/audio/${ID}.mp3 --profile nuernberg --endpoint-url https://nbg1.your-objectstorage.com
aws s3 cp "$TMP_DIR/${ID}.mp3" s3://guru-wisdom-secound/audio/${ID}.mp3  --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
echo "copy to $ONEDRIVE_DIR"
cp  "$TMP_DIR/${ID}.mp3"  "$ONEDRIVE_DIR/audio/${ID}.mp3"
