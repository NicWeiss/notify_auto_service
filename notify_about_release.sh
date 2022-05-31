#!/bin/bash

RELEASE_LINE=$(grep -nE "^[a-z_0-9]+\.[0-9]+\.[0-9_a-z]+" CHANGES.log | head -1 | cut -f1 -d:)
PREVIOUS_RELEASE_LINE=$(grep -nE "^[a-z_0-9]+\.[0-9]+\.[0-9_a-z]+" CHANGES.log | head -2 | tail -1 | cut -f1 -d:)
RELEASE_CONTEXT=$(head -$(($PREVIOUS_RELEASE_LINE - 1)) CHANGES.log | tail -$(($PREVIOUS_RELEASE_LINE - $RELEASE_LINE)))

ESCAPED_RELEASE_CONTEXT=$(echo "$RELEASE_CONTEXT" | sed 's/\\/\\\\/g; s/\"/\\\"/g')

CHAT_ID=$1
TOKEN=$2

read -r -d '' MESSAGE <<-EOM
  {
    "chat_id": $CHAT_ID,
    "text": "Обновление сервиса\n $ESCAPED_RELEASE_CONTEXT"
  }
EOM

URL='https://api.telegram.org/bot'$TOKEN'/sendMessage'

curl -X POST -H 'Content-type: application/json' --data "$MESSAGE" $URL
