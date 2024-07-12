#!/bin/bash

CPU_PERCENT=$(printf "%b" "import psutil\nprint('{}'.format(psutil.cpu_percent(interval=2)))" | python3)
HOSTNAME=$(hostname)
RAM_PERCENT=$(top -b -n 1 | grep "MiB Mem :" | awk '{print $8}')
RAM_CAPACITY=$(top -b -n 1 | grep "MiB Mem :" | awk '{print $4}')
DISK_CAPACITY=$(df -h / | grep "/" | awk '{print $2}')
DISK_USAGE=$(df -h / | grep "/" | awk '{print $3}')
LAST_USERS=$(last | awk '$2 == ":1" {gsub(" :1 ", "", $0); print}' | awk '
{
  match($0, /([a-zA-Z0-9]+)[ ]+([a-zA-Z ]+[0-9: ]+[0-9]+)(.*)/, arr);
  user = arr[1];
  date = arr[2];
  status = arr[3];
  gsub(/^[ ]+|[ ]+$/, "", user);
  gsub(/^[ ]+|[ ]+$/, "", date);
  gsub(/^[ ]+|[ ]+$/, "", status);
  print "{\"user\": \"" user "\", \"date\": \"" date "\", \"status\": \"" status "\"}";
}' | jq -s '.')

JSON_DATA=$(jq -n \
                  --arg cpu_yuzde "$CPU_PERCENT" \
                  --arg bilgisayar_adi "$HOSTNAME" \
                  --arg ram_yuzde "$RAM_PERCENT" \
                  --arg ram_kapasite "$RAM_CAPACITY" \
                  --arg disk_kapasite "$DISK_CAPACITY" \
                  --arg disk_kullanim "$DISK_USAGE" \
                  --argjson oturum_bilgisi "$LAST_USERS" \
                  '{
                    cpu_yuzde: $cpu_yuzde, 
                    bilgisayar_adi: $bilgisayar_adi, 
                    ram_yuzde: $ram_yuzde, 
                    ram_kapasite: $ram_kapasite, 
                    disk_kapasite: $disk_kapasite, 
                    disk_kullanim: $disk_kullanim,
                    oturum_bilgisi: $oturum_bilgisi
                  }')

curl --request POST \
  --url http://192.168.1.100:9827/cpurequest \
  -H 'Content-Type: application/json' \
  -d "$JSON_DATA"

