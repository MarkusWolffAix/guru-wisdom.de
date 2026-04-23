aws s3 sync "$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/S3Storage/images/" s3://guru-wisdom-secound/images/ --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
aws s3 sync "$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/S3Storage/audio/" s3://guru-wisdom-secound/audio/ --profile helsinki --endpoint-url https://hel1.your-objectstorage.com
aws s3 sync "$HOME/Library/CloudStorage/OneDrive-Persönlich/Backup/S3Storage/video/" s3://guru-wisdom-secound/video/ --profile helsinki --endpoint-url https://hel1.your-objectstorage.com

