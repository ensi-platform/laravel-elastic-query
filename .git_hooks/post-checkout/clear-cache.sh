#!/bin/bash

# В данном хуке выполняется сброс кэша при переключении веток

ESC_SEQ="\x1b["
COL_RESET=$ESC_SEQ"39;49;00m"
COL_RED=$ESC_SEQ"0;31m"
COL_GREEN=$ESC_SEQ"0;32m"
COL_YELLOW=$ESC_SEQ"0;33m"

php artisan optimize:clear
exit 0
