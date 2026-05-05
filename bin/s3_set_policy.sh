#!/bin/bash

# --- Configuration ---
# ngb1 korrigiert zu nbg1 (Nürnberg 1)
LOCATIONS=("nbg1" "hel1")

echo "----------------------------------------------------"
echo "🚀 Starting S3 Policy Update for all buckets..."
echo "----------------------------------------------------"

for BUCKET_PLACE in "${LOCATIONS[@]}"; do
    
    if [ "$BUCKET_PLACE" == "nbg1" ]; then
        BUCKET_NAME="guru-wisdom-first"
        PROFILE="nuernberg"
    elif [ "$BUCKET_PLACE" == "hel1" ]; then
        # Name korrigiert zu 'secound', passend zu deinem Hetzner-Bucket
        BUCKET_NAME="guru-wisdom-secound"
        PROFILE="helsinki"
    else
        echo "❌ Error: Unknown location $BUCKET_PLACE."
        continue
    fi

    ENDPOINT="https://${BUCKET_PLACE}.your-objectstorage.com"
    TEST_IMAGE_URL="https://${BUCKET_NAME}.${BUCKET_PLACE}.your-objectstorage.com/images/AbrahamLife.jpg"

    echo "🔄 Processing Location: $BUCKET_PLACE | Bucket: $BUCKET_NAME"

    # 1. Define the Policy variable (JSON)
    POLICY=$(cat <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadGetObject",
      "Effect": "Allow",
      "Principal": "*",
      "Action": [
        "s3:GetObject"
      ],
      "Resource": [
        "arn:aws:s3:::${BUCKET_NAME}/images/*",
        "arn:aws:s3:::${BUCKET_NAME}/audio/*",
        "arn:aws:s3:::${BUCKET_NAME}/video/*",
        "arn:aws:s3:::${BUCKET_NAME}/robots.txt"
      ]
    }
  ]
}
EOF
)

    # 2. Execute the command
    echo "📡 Sending policy to Hetzner S3 (Profile: $PROFILE)..."

    aws s3api put-bucket-policy \
        --bucket "$BUCKET_NAME" \
        --policy "$POLICY" \
        --profile "$PROFILE" \
        --endpoint-url "$ENDPOINT"

    # 3. Check the result
    if [ $? -eq 0 ]; then
        echo "✅ Success! The policy has been applied for $BUCKET_NAME."
        echo "🔗 Test link: $TEST_IMAGE_URL"
    else
        echo "❌ Error: Could not apply the policy for $BUCKET_NAME."
    fi
    
    echo "----------------------------------------------------"
done

echo "🎉 All updates completed!"