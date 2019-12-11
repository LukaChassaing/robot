<?php

require_once("vendor/autoload.php");

shell_exec('espeak -p 75 -s 150 -v mb/mb-fr1 "Lancement de la séquence de démarrage" --stdout |aplay');

$loop = \React\EventLoop\Factory::create();

// Create a logger which writes everything to the STDOUT
$logger = new \Zend\Log\Logger();
$writer = new Zend\Log\Writer\Stream("php://output");
$logger->addWriter($writer);


class GestionnaireReconnaissance{
    private $personnesDetectees = array();
    public function getPersonnesDetectees(){
        return $this->personnesDetectees;
    }
    public function addPersonneToPersonnesDetectees($personne){
        array_push($this->personnesDetectees, $personne);
    }
}




$gp = new GestionnaireReconnaissance();
// Create a WebSocket server using SSL
$server = new \Devristo\Phpws\Server\WebSocketServer("tcp://127.0.0.1:12345", $loop, $logger);
shell_exec('espeak -p 75 -s 150 -v mb/mb-fr1 "Serveur websockette ok" --stdout |aplay');

shell_exec('espeak -p 75 -s 150 -v mb/mb-fr1 "En attente de connexion des neuronnes" --stdout |aplay');
$server->on("connect", function () use ($server, $logger) {
    $logger->notice("Connected!");
    foreach ($server->getConnections() as $client) {
        $client->sendString("Nouvelle connexion !");
    }
});

$server->on("message", function ($user, $message) use ($server, $logger, $gp) {
    $logger->notice("Nouveau message : " . $message->getData() . " De la part de : " . $user->getId());

    $messageDecode = json_decode($message->getData(), true);
    var_dump($messageDecode);
    /**
     * Type de message
     * 1 => Reconnaissance d'une personne connue
     */
    if($messageDecode['type'] == 1){
        if(!in_array($messageDecode['nomPersonneDetectee'], $gp->getPersonnesDetectees())){ // Si la personne n'a pas encore été reconnue alors on parle
            shell_exec('espeak -p 75 -s 150 -v mb/mb-fr1 "Bonjour ' . $messageDecode['nomPersonneDetectee'] . '" --stdout |aplay');
            $gp->addPersonneToPersonnesDetectees($messageDecode['nomPersonneDetectee']);
        }
        
    }

    foreach ($server->getConnections() as $client) {
        $client->sendString("Message reçu !");
    }
});

shell_exec('espeak -p 75 -s 150 -v mb/mb-fr1 "Lancement des timers périodiques" --stdout |aplay');
$tension = shell_exec("cd /var/www/html/robot/UUGear/RaspberryPi/bin;sudo python VoltageMeasurement.py 2>&1");
$tension = explode(' V',$tension)[0]." Volt";
shell_exec('espeak -p 75 -s 150 -v mb/mb-fr1 "La tension actuelle est ' . $tension . '" --stdout |aplay');
$loop->addPeriodicTimer(10, function () use ($server, $logger) {
    $time = new DateTime();
    $tension = shell_exec("cd /var/www/html/robot/UUGear/RaspberryPi/bin;sudo python VoltageMeasurement.py 2>&1");
    $tension = explode(' V',$tension)[0]." Volt";
    shell_exec('espeak -p 75 -s 150 -v mb/mb-fr1 "La tension actuelle est ' . $tension . '" --stdout |aplay');
    $string = $time->format("Y-m-d H:i:s");
    $logger->notice("Broadcasting time to all clients: $string");
    foreach ($server->getConnections() as $client)
        $client->sendString($string);
});


// Bind the server
$server->bind();

// Start the event loop
$loop->run();
