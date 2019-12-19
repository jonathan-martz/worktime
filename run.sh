#!/usr/bin/zsh

export DISPLAY=:0.0
xhost +

wmctrl -l|awk '{$3=""; $2=""; $1=""; print $0}' > phpstorm-prozess.log
php worktime-log.php
