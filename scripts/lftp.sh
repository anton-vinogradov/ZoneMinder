#!/bin/bash
echo $1
a=$(echo $1 | awk -F/ '{print $6"---"$9"-"$8"-"$7"---"$10"-"$11"-"$12}')
echo $a
cd ${1}
tar -cvf /home/uploader/Dropbox/zoneminder/${a}.tar *
chmod 777 /home/uploader/Dropbox/zoneminder/${a}.tar
##cp -R ${1}/*capture.jpg /home/uploader/Dropbox/zoneminder/${a}

