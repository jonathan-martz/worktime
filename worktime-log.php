<?php
/**
 * @todo add support for vscode
 */

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

$filename = 'config.json';
$file = file_get_contents($filename);

$config = json_decode($file, JSON_FORCE_OBJECT);

$command['phpstorm'] = 'ps -aux | grep -i '.$config['filter'];
$find['phpstorm'] = exec($command['phpstorm']);

/**
 * @todo add as requirement to README.md
 * @todo fix prozess finder
 */
$locked = exec('gnome-screensaver-command -q | grep -i "is active"');

if(empty($locked)){
    $locked = false;
}
else{
    $locked = true;
}

$commands = explode(PHP_EOL, $find['phpstorm']);

$data = [
    'locked' => $locked
];

foreach($commands as $command){
    $command = explode(' ', $command);

    if(!empty($command[29])){
        $project = explode($config['folder']. '/', $command[29])[1];

        if(!endsWith($command[29], 'phpstorm.sh')){
            $branch = exec('cd '. $command[29].' && git rev-parse --abbrev-ref HEAD');

            $data[] = [
                'project' => $project,
                'branch' => $branch
            ];
        }
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
