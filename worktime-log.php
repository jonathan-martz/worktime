<?php

$phpstorm = exec('ps -aux | grep -i bin/phpstorm.sh');
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
        $project = explode('PhpstormProjects/', $command[29])[1];

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
curl_setopt($ch, CURLOPT_URL, "https://api.jmartz.de");
curl_setopt($ch, CURLOPT_POSTFIELDS,     $json );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
