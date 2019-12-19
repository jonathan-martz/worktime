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

function loadConfig(){
    $filename = 'config.json';
    $file = file_get_contents($filename);

    return json_decode($file, JSON_FORCE_OBJECT);
}

$config = loadConfig();

$find['phpstorm'] = file_get_contents('phpstorm-prozess.log');

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

foreach($find as $command){
    $prozesses = explode(PHP_EOL, $command);
    if(!empty($prozesses)){
        foreach ($prozesses as $prozess) {
            $prozess = trim($prozess);

            if(strpos($prozess, '~/PhpstormProjects') !== false){
                $cmd = explode(' ', $prozess);

                if(count($cmd) == 4 || count($cmd) == 2){
                    $cmd[1] = trim($cmd[1], '[');
                    $cmd[1] = trim($cmd[1], ']');
                    $name = $cmd[0];
                    $branch = exec('cd '.$cmd[1].' && git rev-parse --abbrev-ref HEAD');

                    $data[$name] = [
                        'name' => $name,
                        'branch' => $branch,
                    ];

                    if(!empty($cmd[3])){
                        $file = substr($cmd[3], 4);
                        $data[$name]['file'] = $file;
                    }
                }
            }
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
