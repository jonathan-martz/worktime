#!/usr/bin/zsh

wmctrl -l|awk '{$3=""; $2=""; $1=""; print $0}' > phpstorm-prozess.log
php worktime-log.php
