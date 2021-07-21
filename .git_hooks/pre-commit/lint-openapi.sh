#!/bin/bash

# В данном хуке выполняется приведение код-стайла в соответствие с конфигом .php_cs

EXECUTABLE_NAME=spectral
ROOT=`pwd`
ESC_SEQ="\x1b["
COL_RESET=$ESC_SEQ"39;49;00m"
COL_RED=$ESC_SEQ"0;31m"
COL_GREEN=$ESC_SEQ"0;32m"
COL_YELLOW=$ESC_SEQ"0;33m"
COL_BLUE=$ESC_SEQ"0;34m"
COL_MAGENTA=$ESC_SEQ"0;35m"
COL_CYAN=$ESC_SEQ"0;36m"

echo ""
printf "$COL_YELLOW%s$COL_RESET\n" "Running pre-commit hook: \"lint-openapi\""

# possible locations
locations=(
  $ROOT/node_modules/.bin/$EXECUTABLE_NAME
  $EXECUTABLE_NAME
)

for location in ${locations[*]}
do
  if [[ -x $location ]]; then
    EXECUTABLE=$location
    break
  fi
done

if [[ ! -x $EXECUTABLE ]]; then
  echo "executable $EXECUTABLE_NAME not found, exiting..."
  echo "if you're sure this is incorrect, make sure they're executable (chmod +x)"
  exit
fi

echo "using \"$EXECUTABLE_NAME\" located at $EXECUTABLE"

if [[ -f $ROOT/$CONFIG_FILE ]]; then
  CONFIG=$ROOT/$CONFIG_FILE
  echo "config file located at $CONFIG loaded"
fi

FILES=`git status --porcelain | grep -e '^[AM]\(.*\).yaml$' | cut -c 3-`
if [ -z "$FILES" ]; then
    echo "No yaml files changed"
else
    echo "Linting openapi files according to .spectral.yaml";
    $EXECUTABLE lint ./public/api-docs/**/index.yaml;
    if [ $? == 1 ]; then
      printf "$COL_RED%s$COL_RESET\r\n\r\n" "Please fix errors above"
    exit 1
fi
fi



echo "Okay"
exit 0
