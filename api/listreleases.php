<?php

$payload = json_decode($_POST['payload'], true);

if (strpos($payload['Repository'], '/') === -1) {
    echo json_encode(['error' => 'invalid repository']);
} else {
    $releases = json_decode(file_get_contents(
        'https://api.github.com/repos/' . $payload['Repository'] . '/releases',
        false,
        stream_context_create(['http' => ['method' => 'GET', 'header' => ['User-Agent: ORASM - OpenRA Server Manager']]])
    ), true);

    if ($releases === null) {
        echo json_encode(['error' => 'invalid repository']);
    } else {
        $availableReleases = [];

        foreach ($releases as $release) {
            foreach ($release['assets'] as $asset) {
                if (substr($asset['name'], -9) === '.AppImage') {
                    $availableReleases[] = ['Release' => $release['id'], 'Asset' => $asset['id'], 'Label' => $release['name'] . ' / ' .  $asset['name']];
                }
            }
        }

        echo json_encode(['releases' => $availableReleases]);
    }
}
