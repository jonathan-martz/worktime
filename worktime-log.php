<?php

$output = exec('ps -aux | grep -i bin/phpstorm.sh');

$commands = explode(PHP_EOL, $output);

foreach($commands as $command){
    var_dump($command);
}
