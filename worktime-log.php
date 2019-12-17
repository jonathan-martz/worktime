<?php

$filename = 'config.json';
$file = file_get_contents($filename);

$config = json_decode($file, JSON_FORCE_OBJECT);

$phpstorm = exec('ps -aux | grep -i '.$config['filter']);
/**
 * @todo add as requirement to README.md
 */
$locked = exec('gnome-screensaver-command -q | grep "is active"');

if(empty($locked)){
    $locked = false;
}
else{
    $locked = true;
}

$commands = explode(PHP_EOL, $phpstorm);

$data = [
    'locked' => $locked
];

foreach($commands as $command){
    $command = explode(' ', $command);

    if(!empty($command[29])){
        $project = explode($config['folder']. '/', $command[29])[1];

        $branch = exec('cd '. $command[29].' && git rev-parse --abbrev-ref HEAD');

        $data[] = [
            'project' => $project,
            'branch' => $branch
        ];
    }
}

$json = json_encode($data, true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_USERAGENT, 'worktime-logger');
curl_setopt($ch, CURLOPT_POST,           1 );
curl_setopt($ch, CURLOPT_URL, $config['api']);
curl_setopt($ch, CURLOPT_POSTFIELDS,     $json );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);

var_dump($json);
