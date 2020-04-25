# List of usefull commands for dev

## Launch and stop server

php bin/console server:start

php bin/console server:run

php bin/console server:stop

## Clear cache

php bin/console clear:cache

## installation dependences

composer require XXX

## Launch unit tests

### All

php vendor/bin/behat

### Specific

php vendor/bin/behat features

php vendor/bin/behat features/Category.feature --name "Can add a new Category"

php vendor/bin/behat features/Category.feature:56