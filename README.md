Symfony Application
========================

Reference application created to show how to develop applications following the [Symfony Best Practices][1].

You can also learn about these practices in [the official Symfony Book][5].

TO DO LIST
------------

  * entity Product
  * entity products Group
  * remove index group and redirect to edit

  * Add ingredients list (checkboxes) in recipes singles
  * Add list select in recipes singles
  * Add process add recipe and ingredients removed to list selected

  * recipe single tabs : ingredients/products && recipes (accordions ingredients detailed)

  * list entity add field relationship ingredient_recipe_deactivated (temp name) fields recipe,ingredient,user

  * favicon front/admin
  * translations
  * responsive

Requirements
------------

  * PHP 8.2.0 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][2].

Installation
------------

There are 3 different ways of installing this project depending on your needs:

**Option 1.** [Download Symfony CLI][4] and use the `symfony` binary installed
on your computer to run this command:

```bash
symfony new --demo my_project
```

**Option 2.** [Download Composer][6] and use the `composer` binary installed
on your computer to run these commands:

```bash
# you can create a new project based on the Symfony Demo project...
composer create-project symfony/symfony-demo my_project

# ...or you can clone the code repository and install its dependencies
git clone https://github.com/symfony/demo.git my_project
cd my_project/
composer install
```

Usage
-----

There's no need to configure anything before running the application. There are
2 different ways of running this application depending on your needs:

**Option 1.** [Download Symfony CLI][4] and run this command:

```bash
cd my_project/
symfony serve
```

Then access the application in your browser at the given URL (<https://localhost:8000> by default).

**Option 2.** Use a web server like Nginx or Apache to run the application
(read the documentation about [configuring a web server for Symfony][3]).

On your local machine, you can run this command to use the built-in PHP web server:

```bash
cd my_project/
php -S localhost:8000 -t public/
```

Tests
-----

Execute this command to run tests:

```bash
cd my_project/
./bin/phpunit
```

[1]: https://symfony.com/doc/current/best_practices.html
[2]: https://symfony.com/doc/current/setup.html#technical-requirements
[3]: https://symfony.com/doc/current/setup/web_server_configuration.html
[4]: https://symfony.com/download
[5]: https://symfony.com/book
[6]: https://getcomposer.org/