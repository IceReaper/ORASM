<?php

$payload = json_decode($_POST['payload'], true);
$port = intval($payload['port']);

$running = substr(trim(shell_exec('screen -list | grep "' . $port . '" && echo "1" || echo "0"')), -1);

if ($running === '1') {
    echo json_encode(['error' => 'already running']);
} else {
    $servers = json_decode(file_get_contents('../config.json'), true);

    foreach ($servers as $server) {
        if ($server['Port'] == $port) {
            exec('screen -dmS ' . $port . ' ../servers/' . $port . '.AppImage --server Server.Name="' . $server['Name'] . '" Server.ListenPort=' . $port);
        }
    }

    echo json_encode([]);
}
