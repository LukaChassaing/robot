<a href="neuronne-detection-reconnaissance-objets/index.php">Neuronne detection et reconnaissance objets</a>
<a href="neuronne-detection-reconnaissance-faciale/index.php">Neuronne detection et reconnaissance faciale</a>

<?php

echo "<h1>Superviseur</h1>";

print shell_exec("cd /var/www/html/robot/UUGear/RaspberryPi/bin;sudo python VoltageMeasurement.py 2>&1");