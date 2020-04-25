# List of usefull commands for dev

## Launch and stop server
```bash
php bin/console server:start
```

```bash
php bin/console server:run
```

```bash
php bin/console server:stop
```

## Clear cache

```bash
php bin/console clear:cache
```

## installation dependences

```bash
composer require XXX
```

## Launch unit tests

### All

```bash
php vendor/bin/behat
```

### Specific

```bash
php vendor/bin/behat features
```

```bash
php vendor/bin/behat features/Category.feature --name "Can add a new Category"
```

```bash
php vendor/bin/behat features/Category.feature:56
```

# Migrations

php bin/console doctrine:migrations:migrate

php bin/console doctrine:migrations:generate