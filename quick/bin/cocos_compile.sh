#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source $DIR/_setenv.sh
php "$DIR/lib/cocos_compile.php" $*