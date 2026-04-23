#!/bin/bash

# --- Configuration ---
# Change these values if you create new buckets in the future
BUCKET_NAME="guru-wisdom"
ENDPOINT="https://fsn1.your-objectstorage.com"
TEST_IMAGE_URL="https://${BUCKET_NAME}.fsn1.your-objectstorage.com/images/AbrahamLife.jpg"

echo "----------------------------------------------------"
echo "🚀 Starting S3 Policy Update for: $BUCKET_NAME"
echo "----------------------------------------------------"

# 1. Define the Policy variable (JSON)
# This policy allows public read access specifically for the /images/ folder
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
	"arn:aws:s3:::${BUCKET_NAME}/audio/*"
      ]
    }
  ]
}
EOF
)

# 2. Execute the command
# We pass the JSON string directly to the aws-cli
echo "📡 Sending policy to Hetzner S3..."

aws s3api put-bucket-policy \
    --bucket "$BUCKET_NAME" \
    --policy "$POLICY" \
    --endpoint-url "$ENDPOINT"

# 3. Check the result
if [ $? -eq 0 ]; then
    echo "✅ Success! The policy has been applied."
    echo ""
    echo "🔗 You can now test your link in the browser:"
    echo "👉 $TEST_IMAGE_URL"
else
    echo "❌ Error: Could not apply the policy."
    echo "💡 Tip: Make sure the 'Public Access Block' (Protection) is disabled in the Hetzner Console."
fi

echo "----------------------------------------------------"
