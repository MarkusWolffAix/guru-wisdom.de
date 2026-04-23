#!/bin/bash

# Configuration
WISDOM_DIR="/Users/markuswolff/guru-wisdom.de/web/wisdoms"
S3_BUCKET="s3://guru-wisdom"
ENDPOINT="https://fsn1.your-objectstorage.com"

# Check if a source file was provided
if [ -z "$1" ]; then
    echo "Usage: $0 <path-to-file> [org]"
    echo "Example: $0 my-video.mp4"
    echo "Example for original JPG: $0 my-image.jpg org"
    exit 1
fi

src="$1"
param2="$2"

# Check if the local source file exists
if [ ! -f "$src" ]; then
    echo "Error: The file '$src' was not found locally."
    exit 1
fi

# Extract filename, extension, and wisdom ID
filename=$(basename "$src")
extension="${filename##*.}"
wisdomid="${filename%.*}"

# Convert file extension to lowercase (for safe comparison)
ext_lower=$(echo "$extension" | tr '[:upper:]' '[:lower:]')

# 1. Validation: Does the wisdom ID exist as a Markdown file?
if [ ! -f "$WISDOM_DIR/${wisdomid}.md" ]; then
    echo "Error: Aborting! No corresponding wisdom file exists at:"
    echo "$WISDOM_DIR/${wisdomid}.md"
    exit 1
fi

# 2. Determine target directory based on file extension
target_dir=""

case "$ext_lower" in
    mp3)
        target_dir="audio/"
        ;;
    mp4|mov|avi|mkv|webm) # Add common video formats here if needed
        target_dir="video/"
        ;;
    jpg|jpeg)
        if [ "$param2" == "org" ]; then
            target_dir="images/org/"
        else
            target_dir="images/"
        fi
        ;;
    *)
        # Abort if the file type is not supported
        echo "Error: The file type '.$ext_lower' is not supported!"
        echo "Only mp3, videos (e.g., mp4), or jpg/jpeg are allowed."
        exit 1
        ;;
esac

# Construct the full S3 target path
s3_target="${S3_BUCKET}/${target_dir}${filename}"

# 3. Check if the file already exists on S3
echo "Checking S3 bucket..."
# 'aws s3 ls' returns exit code 0 if the file is found.
if aws s3 ls "$s3_target" --endpoint-url "$ENDPOINT" > /dev/null 2>&1; then
    echo "Error: Aborting! The file '$filename' already exists on S3."
    echo "Path: $s3_target"
    exit 1
fi

# 4. Execute the upload
echo "Validation successful. Starting upload..."
echo "Source: $src"
echo "Target: $s3_target"

aws s3 cp "$src" "$s3_target" --endpoint-url "$ENDPOINT"

# Check if the upload was successful
if [ $? -eq 0 ]; then
    echo "Success: Upload completed!"
else
    echo "Error: The S3 upload failed."
    exit 1
fi
