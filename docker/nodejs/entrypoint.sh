#!/bin/ash
PATH_PWD=$(pwd)

# activate debugging
#set -x

if [ ! -d $PATH_PWD/node_modules ]
then
  echo "Building 'Application'"
  npm install
fi

echo "NodeJS started"

sleep infinity & wait