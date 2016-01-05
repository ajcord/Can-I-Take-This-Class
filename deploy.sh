#!/bin/sh

if [ "$1" == "dev" ]; then
    rsync -av -P --exclude=".*" . cordonn2_dev@dev.canitakethisclass.com:~/project
elif [ "$1" == "prod" ]; then
    rsync -av -P --exclude=".*" . cordonn2@canitakethisclass.com:~/project
else
    echo "Usage: deploy.sh dev|prod"
fi