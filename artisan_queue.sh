#!/bin/bash

# Define log file path
LOG_FILE="/var/wwww/api/cron_log.txt"

# Check if the artisan queue worker is already running
if ! /usr/bin/pgrep -f "artisan queue:work"; then
    echo "Starting queue worker at $(date)" >> "$LOG_FILE"
    /usr/bin/php /var/wwww/api/artisan queue:work --timeout=120 --sleep=3 --tries=3 >> "$LOG_FILE" 2>&1
fi