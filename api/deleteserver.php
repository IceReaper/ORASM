<?php

$payload = json_decode($_POST['payload'], true);
$port = intval($payload['port']);

$running = trim(shell_exec('screen -list | grep "' . $port . '" && echo "true" || echo "false"'));

if ($running === 'true') {
    echo json_encode(['error' => 'still running']);
} else {
    unlink('../servers/' . $port . '.AppImage');

    $servers = json_decode(file_get_contents('../config.json'), true);
    $newServers = [];

    foreach ($servers as $server) {
        if ($server['Port'] !== $port){
            $newServers[] = $server;
        }
    }

    file_put_contents('../config.json', json_encode($newServers));

    echo json_encode([]);
}
