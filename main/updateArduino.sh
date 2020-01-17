#!/bin/sh -e
sudo pkill -f "python serialListener.py"
arduino --upload main.ino
echo "Upload ok"
sudo python serialListener.py