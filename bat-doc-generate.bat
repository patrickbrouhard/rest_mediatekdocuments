@echo off
echo Generation de la documentation...

php ..\phpdoc.phar run --ansi --directory src --target docs --title "Mediatekformation API"

pause