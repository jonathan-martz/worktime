<?php

$output = exec('ps -aux | grep -i bin/phpstorm.sh');

$commands = explode(PHP_EOL, $output);

$data = [];

foreach($commands as $command){
    $command = explode(' ', $command);

    $project = explode('PhpstormProjects/', $command[29])[1];

    $data[] = [
        'project' => $project
    ];
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_POST,           1 );
curl_setopt($ch, CURLOPT_URL, "https://api.jmartz.de");
curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($data, true) );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
