#!/bin/sh -e
sudo su
/usr/local/bin/mjpg_streamer -i "/usr/local/lib/mjpg-streamer/input_uvc.so -n -f 25 -r 480x360" -o "/usr/local/lib/mjpg-streamer/output_http.so -p 8085 -w /usr/local/share/mjpg-streamer/www"
forever start -c php /var/www/html/robot/websocket-server/main.php