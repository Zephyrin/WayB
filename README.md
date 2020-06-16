# WayB
A new way to manage your backpack is a REST Api that manage some information about equipment. 
It include a JWT user registration. For more information about this server, please visit [VisualParadigm](https://online.visual-paradigm.com) 

# Server configuration

The server need at least **PHP 7.2**, **composer**, **php7.2-mbstring**, **php7.2-xml**

Inside the project folder:
 
```bash
curl -sS https://getcomposer.org/installer | php
php compose install
```


## Database 

For database, you can use all [ORM include in Symfony](https://symfony.com/doc/current/doctrine.html#configuring-the-database) 

The database configuration is located into **config/packages/doctrine.yaml**. 
To set information about the connection, create your own **.env.local** and set the information about your database and environment.
At least, add this line:
```bash
DATABASE_URL=pgsql://USERNAME:PASSWORD@127.0.0.1:5432/DATABASENAME
```

Then the creation of the database can be made with this command in the root of the project:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
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
