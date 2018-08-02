<?php

$servers = json_decode(file_get_contents('../config.json'), true);

foreach ($servers as $i => $server) {
    $running = substr(trim(shell_exec('screen -list | grep "' . $server['Port'] . '" && echo "1" || echo "0"')), -1);
    $servers[$i]['Running'] = $running === '1';
}

echo json_encode($servers);
