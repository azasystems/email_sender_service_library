clear
./vendor/bin/psalm --show-info=true 
./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
./codecept.sh run Unit