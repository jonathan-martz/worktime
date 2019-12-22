<?php
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

function loadConfig()
{
    $filename = 'config.json';
    $file = file_get_contents($filename);

    return json_decode($file, JSON_FORCE_OBJECT);
}

function isPhpStorm($prozess,$program)
{
    if (strpos($prozess, '~/PhpstormProjects/') !== false && strpos($prozess, 'Atom') === false  && $program['name'] == 'phpstorm') {
        return true;
    }
    return false;
}

function dataPhpStorm($prozess, $program)
{
    $prozess = trim($prozess);
    if (strpos($prozess, '@') === false) {
        $cmd = explode(' ', $prozess);

        if (strpos($prozess, $program['folder']) !== false && strpos($prozess, 'Atom') === false) {
            if (count($cmd) == 4 || count($cmd) == 2) {
                $cmd[1] = trim($cmd[1], '[');
                $cmd[1] = trim($cmd[1], ']');
                $name = $cmd[0];
                $branch = exec('cd ' . $cmd[1] . ' && git rev-parse --abbrev-ref HEAD');

                $data = ['name' => $name, 'branch' => $branch, 'program' => $program['name']];

                if (!empty($cmd[3])) {
                    $file = substr($cmd[3], 4);
                    $data['file'] = $file;
                }
            }
        }
    }

    return (!empty($data)) ? $data : null;
}

function isAtom($prozess, $program)
{
    if (strpos($prozess, 'Atom') !== false && $program['name'] == 'atom') {
        return true;
    }
    return false;
}

function dataAtom($prozess, $program)
{
    $prozess = trim($prozess);
    if (strpos($prozess, '@') === false && strpos($prozess, $program['filter']) !== false) {
        $cmd = explode(' ', $prozess);
        if(count($cmd) == 5){
            $name = trim(str_replace('~/'.$program['folder'], '', $cmd[2]), '/');
            $branch = exec('cd '.$cmd[2].' && git rev-parse --abbrev-ref HEAD');

            $data = ['name' => $name, 'branch' => $branch, 'program' => $program['name']];
        }
    }

    return (!empty($data)) ? $data : null;
}

function isVsCode($prozess, $program){
    if($program['name'] == 'vscode' && strpos($prozess, $program['filter'])){
        return true;
    }

    return false;
}

function dataVsCode($prozess, $program){
    $prozess = trim($prozess);
    if (strpos($prozess, '@') === false && strpos($prozess, $program['filter']) !== false) {
        $cmd = explode(' ', $prozess);

        if(count($cmd) == 5){
            $name = trim(str_replace('~/'.$program['folder'], '', $cmd[0]), '/');
            $branch = exec('cd ~/'.$program['folder'].'/'.$cmd[0].' && git rev-parse --abbrev-ref HEAD');

            $data = [
                'name' => $name,
                'branch' => $branch,
                'program' => $program['name']
            ];
        }
        else if(count($cmd) == 7){
            $name = trim(str_replace('~/'.$program['folder'], '', $cmd[2]), '/');
            $branch = exec('cd ~/'.$program['folder'].'/'.$cmd[2].' && git rev-parse --abbrev-ref HEAD');

            $data = [
                'name' => $name,
                'branch' => $branch,
                'file' => $cmd[0],
                'program' => $program['name']
            ];
        }
    }

    return (!empty($data)) ? $data : null;
}

$config = loadConfig();

$locked = exec('gnome-screensaver-command -q | grep -i "is active"');

if (empty($locked)) {
    $locked = false;
} else {
    $locked = true;
}

$current = file_get_contents('current.log');

function isCurrent($command,$current,$program){
    if(trim($command) == trim($current)){
        return true;
    }
    return false;
}

if(!$locked){
  foreach ($config['programs'] as $key => $program) {
      $find[$program['name']] = file_get_contents('prozess.log');
      $commands = explode(PHP_EOL, $find[$program['name']]);

      foreach ($commands as $command) {
          $prozesses = explode(PHP_EOL, $command);

          if (!empty($prozesses)) {
              foreach ($prozesses as $prozess) {

                  if (isPhpStorm($prozess, $program)){
                      $new = dataPhpStorm($prozess, $program);
                      if ($new !== null) {
                          $data['program'][$program['name']][$new['name']] = $new;

                          $isCurrent = isCurrent($command,$current,$program);
                          if($isCurrent){
                              $new['program'] = $program['name'];
                              $data['current'] = $new;
                          }
                      }
                  } else if (isAtom($prozess, $program)) {
                      $new = dataAtom($prozess, $program);
                      if ($new !== null) {
                          $data['program'][$program['name']][$new['name']] = $new;

                          $isCurrent = isCurrent($command,$current,$program);
                          if($isCurrent){
                              $new['program'] = $program['name'];
                              $data['current'] = $new;
                          }
                      }
                  }
                  else if (isVsCode($prozess, $program)) {
                      $new = dataVsCode($prozess, $program);
                      if ($new !== null) {
                          $data['program'][$program['name']][$new['name']] = $new;

                          $isCurrent = isCurrent($command,$current,$program);
                          if($isCurrent){
                              $new['program'] = $program['name'];
                              $data['current'] = $new;
                          }
                      }
                  }
              }
          }
      }
  }

  $json = json_encode($data, true);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_USERAGENT, 'worktime-logger');
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_URL, $config['api']);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);
}
