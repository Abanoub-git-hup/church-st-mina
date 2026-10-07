#!/usr/bin/env bash
# رفع فولدر theme أو plugin لموقع Hostinger بطريقة TUS، ملف ملف.
# المفاتيح من hosting_files_generate-upload-url، وبتتبعت كـ environment variables (مابتتحفظش في الملف):
#   UP_URL=... UP_AUTH=... UP_REST=... tools/upload-wp.sh <فولدر محلي> <المسار على السيرفر>
# مثال: tools/upload-wp.sh wordpress/themes/st-mina wp-content/themes/st-mina-u2
set -euo pipefail
SRC="$1"; DEST="$2"; ok=0; fail=0
while IFS= read -r -d '' f; do
  rel="${f#"$SRC"/}"; size=$(wc -c < "$f")
  code=$(curl -s -o /dev/null -w '%{http_code}' -X POST "$UP_URL/$DEST/$rel?override=true" \
    -H "X-Auth: $UP_AUTH" -H "X-Auth-Rest: $UP_REST" -H "Tus-Resumable: 1.0.0" -H "Upload-Length: $size" -H "Upload-Offset: 0")
  off=$(curl -s -D - -o /dev/null -X PATCH "$UP_URL/$DEST/$rel?override=true" \
    -H "X-Auth: $UP_AUTH" -H "X-Auth-Rest: $UP_REST" -H "Tus-Resumable: 1.0.0" \
    -H "Content-Type: application/offset+octet-stream" -H "Upload-Offset: 0" --data-binary "@$f" \
    | tr -d '\r' | awk -F': ' 'tolower($1)=="upload-offset"{print $2}')
  if [ "$code" = 201 ] && [ "$off" = "$size" ]; then ok=$((ok+1)); else fail=$((fail+1)); echo "FAILED: $rel (create=$code offset=$off size=$size)"; fi
done < <(find "$SRC" -type f -print0)
echo "uploaded: $ok, failed: $fail"
[ "$fail" = 0 ]
