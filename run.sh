#!/usr/bin/zsh

programm1 = which wmctrl &> /dev/null
if [[ $programm1 == *"not found"* ]]; then
  echo "Package wmctrl not Found!"
  exit 1;
fi

programm2 = which gnome-screensaver-command &> /dev/null
if [[ $programm2 == *"not found"* ]]; then
  echo "Package gnome-screensaver-command not Found!"
  exit 1;
fi

export DISPLAY=:0.0
xhost +

git pull

xdotool getwindowfocus getwindowname > current.log

wmctrl -l|awk '{$3=""; $2=""; $1=""; print $0}' > prozess.log
php worktime-log.php
