#!/bin/bash

set -e

VER=`node -e "console.log(require('../package.json').version);"`
FILE="emoji-data-imgs-${VER}.tar.gz"

echo -n "building ${FILE} ... "

cd ..
tar -czf $FILE --exclude=*.zip sheet* img-*

echo "OK"
