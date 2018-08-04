<?php

$payload = json_decode($_POST['payload'], true);
$port = intval($payload['Port']);

exec('screen -S ' . $port . ' -X quit');

echo json_encode([]);
