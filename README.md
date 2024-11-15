Symfony Application
========================

Reference application created to show how to develop applications following the [Symfony Best Practices][1].

You can also learn about these practices in [the official Symfony Book][5].

TO DO LIST
------------

  * recipe single tabs : ingredients/products && recipes (accordions ingredients detailed)
  * list ingredients : list related recipes if related

  * translations
  * responsive

  * Entities Edit titles translate + structure rework

  * Entities recipes / ingredients => list filter by categories / sections

  * Check **WIP** notes


Data Schema
------------

GroceryList
- own recipes
- own ingredients
- own products
- one tab for recipes listing
- one tab for list ingredients/products by categories (list mixed types)

Data Default
------------
* User
- Admin : test@example.com
- test : test@test.com

* Ingredient/Product's Section
- Conserves
- Hygiene
- Ménage
- Vaisselle
- Cuisine
- Matin/Biscuits
- Boissons
- Desserts
- Apéritifs
- Surgelés
- Condiments/Sauces
- Épicerie
- Fruits
- Légumes
- Laitages
- Fromages
- Viandes
- Poissons
- Autres

* Recipe's Category
- Entrée
- Plat principal
- Dessert
- Apéritif
- Autre

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