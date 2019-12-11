<?php
echo "Bonjour !";

?>

<html>
    <head>
        <title>WebSocket TEST</title>
    </head>
    <body>
        <h1>Server Time</h1>
        <strong id="time"></strong>

        <script>
            var socket = new WebSocket("ws://<?php echo $_SERVER['HTTP_HOST'] ?>:12345/");
            socket.onmessage = function(msg) {
                document.getElementById("time").innerText = msg.data;
            };
            socket.send('Coucou')
        </script>
    </body>
</html>