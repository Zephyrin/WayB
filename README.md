# WayB
A new way to manage your backpack is a REST Api that manage some information about equipment. 
It include a JWT user registration. For more information about this server, please visit [VisualParadigm](https://online.visual-paradigm.com) 

# Server configuration

The server need at least PHP 7.2 and composer.
Inside the project folder:
 
```bash
curl -sS https://getcomposer.org/installer | php
php compose install
```

## Database 

For database, you can use all [ORM include in Symfony](https://symfony.com/doc/current/doctrine.html#configuring-the-database) 

Then the creation of the database can be made with this command in the root of the project:

```bash
php bin/console doctrine:database:create
php bin/console make:migration
```

## Running this application in standalone :

```bash
php bin/console server:run
```

If you are using a VM :
```bash
php bin/console server:start 0.0.0.0:8000
```

# Swagger Documentation
Once the server is launch you can find the Swagger documentation to this link:
```html
http://localhost:8000/api/doc
```
