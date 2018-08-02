<?php

$payload = json_decode($_POST['payload'], true);
$port = intval($payload['port']);
$name = trim($payload['name']);
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
} else if (strpos($payload['repository'], '/') === -1) {
    echo json_encode(['error' => 'invalid repository']);
} else {
    $releases = json_decode(file_get_contents(
        'https://api.github.com/repos/' . $payload['repository'] . '/releases',
        false,
        stream_context_create(['http' => ['method' => 'GET', 'header' => ['User-Agent: ORASM - OpenRA Server Manager']]])
    ), true);

    if ($releases === null) {
        echo json_encode(['error' => 'invalid repository']);
    } else {
        $appImageLink = null;

        foreach ($releases as $release) {
            if ($release['tag_name'] === $payload['release']) {
                foreach ($release['assets'] as $asset) {
                    if (substr($asset['name'], -9) === '.AppImage') {
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
                'Repository' => $payload['repository'],
                'Release' => $payload['release']
            ];

            file_put_contents('../config.json', json_encode($servers));

            echo json_encode([]);
        }
    }
}
