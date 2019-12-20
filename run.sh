#!/usr/bin/zsh

export DISPLAY=:0.0
xhost +

git pull

wmctrl -l|awk '{$3=""; $2=""; $1=""; print $0}' > prozess.log
php worktime-log.php
