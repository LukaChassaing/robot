<?php

echo "<h1>Superviseur</h1>";

print shell_exec("cd /var/www/html/robot/UUGear/RaspberryPi/bin;sudo python VoltageMeasurement.py 2>&1");