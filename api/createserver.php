<?php

$payload = json_decode($_POST['payload'], true);
$port = intval($payload['Port']);
$name = trim($payload['Name']);
$servers = json_decode(file_get_contents('../config.json'), true);

$exists = false;
foreach ($servers as $server) {
    $exists = $exists || $server['Port'] === $port;
}

if ($port <= 0 || $port > 0xffff) {
    echo json_encode(['error' => 'invalid port']);
} else if (is_resource($fp = @fsockopen('localhost', $port))) {
    echo json_encode(['error' => 'port in use']);
} else if ($exists) {
    echo json_encode(['error' => 'port in use']);
} else if ($name === "") {
    echo json_encode(['error' => 'invalid server name']);
} else if (strpos($payload['Repository'], '/') === -1) {
    echo json_encode(['error' => 'invalid repository']);
} else {
    $ids = array_map('intval', explode(',', $payload['Asset']));

    $releases = json_decode(file_get_contents(
        'https://api.github.com/repos/' . $payload['Repository'] . '/releases',
        false,
        stream_context_create(['http' => ['method' => 'GET', 'header' => ['User-Agent: ORASM - OpenRA Server Manager']]])
    ), true);

    if ($releases === null) {
        echo json_encode(['error' => 'invalid repository']);
    } else {
        $appImageLink = null;

        foreach ($releases as $release) {
            if ($release['id'] === $ids[0]) {
                foreach ($release['assets'] as $asset) {
                    if ($asset['id'] === $ids[1]) {
                        $appImageLink = $asset['browser_download_url'];
                    }
                }
            }
        }

        if ($appImageLink === null) {
            echo json_encode(['error' => '.AppImage not found']);
        } else {
            $serverPath = '../servers/';
            if (!is_dir($serverPath)) {
                mkdir($serverPath);
            }

            $appImage = file_get_contents(
                $appImageLink,
                false,
                stream_context_create(['http' => ['method' => 'GET', 'header' => ['User-Agent: ORASM - OpenRA Server Manager']]])
            );
            $targetFile = $serverPath . $port . '.AppImage';
            file_put_contents($targetFile, $appImage);
            chmod($targetFile, 0755);

            $servers[] = [
                'Port' => $port,
                'Name' => $name,
                'Repository' => $payload['Repository'],
                'Release' => $payload['Release']
            ];

            file_put_contents('../config.json', json_encode($servers));

            echo json_encode([]);
        }
    }
}
