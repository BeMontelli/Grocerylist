# GroceryList — environnement local (Windows)

Protocole pour installer, lancer, mettre à jour et remettre à zéro le projet en local, **sans Docker**.
Toutes les commandes sont à lancer dans un terminal **PowerShell** (celui de VS Code par défaut), depuis la racine du projet, sauf mention contraire.

> Après une installation d'outil (PHP, Composer, Node, ajout au PATH), **fermer et rouvrir VS Code** pour que le terminal voie le nouveau PATH.

---

## 1. Stack locale

| Outil | Version | Emplacement / installation |
|---|---|---|
| PHP | 8.4 (même version que la prod) | `winget install PHP.PHP.8.4` → `%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.4_…` |
| Composer | 2.x | `%LOCALAPPDATA%\ComposerBin\composer.phar` + `composer.bat` (dans le PATH utilisateur) |
| MySQL | 8.4 LTS, version ZIP portable | `C:\Users\User\mysql\mysql-8.4.13-winx64` (binaire), `C:\Users\User\mysql\data` (données), `C:\Users\User\mysql\my.ini` (config) |
| Node.js / npm | LTS | `winget install OpenJS.NodeJS.LTS` |
| Symfony | 7.4 LTS | via Composer |

Vérification rapide :

```powershell
php -v; composer --version; node -v; mysql --version
```

### php.ini

Fichier `php.ini` dans le dossier d'installation de PHP (`php --ini` pour le retrouver), créé à partir de `php.ini-development` avec :

- extensions activées : `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `mysqli`, `openssl`, `pdo_mysql`, `sodium`, `zip` ;
- `extension_dir` en chemin absolu vers `ext\` ;
- `memory_limit = 512M`, `upload_max_filesize = 20M`, `post_max_size = 25M` ;
- `error_reporting = E_ALL & ~E_DEPRECATED`.

La variable d'environnement utilisateur `OPENSSL_CONF` pointe vers `extras\ssl\openssl.cnf` du dossier PHP (nécessaire pour générer les clés JWT sous Windows).

### MySQL (`C:\Users\User\mysql\my.ini`)

```ini
[mysqld]
basedir=C:/Users/User/mysql/mysql-8.4.13-winx64
datadir=C:/Users/User/mysql/data
port=3306
bind-address=127.0.0.1
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
max_allowed_packet=64M
[client]
port=3306
default-character-set=utf8mb4
```

Compte `root` **sans mot de passe**, accessible uniquement depuis `127.0.0.1`.

> ⚠️ Dans PowerShell, toujours écrire `--host=127.0.0.1` (et non `-h127.0.0.1`, mal découpé par PowerShell).

### Fichier `.env` (non versionné)

Le `.env` local ne contient **aucune donnée de prod**. Variables attendues :

```dotenv
APP_ENV=dev
APP_SECRET=<32 caractères hex aléatoires>
DATABASE_URL="mysql://root:@127.0.0.1:3306/grocerylist?serverVersion=8.4.13&charset=utf8mb4"
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
MAILER_DSN=null://null
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=<32 caractères hex aléatoires>
VAR_DUMPER_SERVER=127.0.0.1:9912
PAGINATION_PER_PAGE=2
```

Générer une valeur aléatoire : `php -r "echo bin2hex(random_bytes(16));"`

---

## 2. Lancement quotidien

Deux terminaux (ou trois si on travaille le SCSS/JS Encore).

**Terminal 1 — MySQL** (laisser ouvert) :

```powershell
mysqld --defaults-file=C:\Users\User\mysql\my.ini --console
```

Variante sans fenêtre : `Start-Process mysqld -ArgumentList '--defaults-file=C:\Users\User\mysql\my.ini' -WindowStyle Hidden`

**Terminal 2 — serveur PHP** :

```powershell
php -S 127.0.0.1:8000 -t public
```

**Terminal 3 (optionnel) — assets Encore en mode watch** :

```powershell
npm run watch
```

Ouvrir http://127.0.0.1:8000

### Arrêt

- Serveur PHP : `Ctrl+C` dans son terminal.
- MySQL : `mysqladmin -uroot --host=127.0.0.1 shutdown` (ou `Ctrl+C` dans son terminal).

---

## 3. Mise à jour après un `git pull`

```powershell
composer install
php bin/console doctrine:migrations:migrate -n
npm install
npm run build                # ou npm run dev
php bin/console cache:clear
```

Puis vider le cache du navigateur si l'affichage est incohérent (voir § 7).

---

## 4. Rafraîchir la base avec un nouveau dump de prod

1. Exporter la base depuis phpMyAdmin (prod), placer le fichier dans `Downloads` (ex. `montelgdb.sql`).
2. Recréer la base locale et importer :

```powershell
mysql -uroot --host=127.0.0.1 -e "DROP DATABASE IF EXISTS grocerylist; CREATE DATABASE grocerylist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -uroot --host=127.0.0.1 --default-character-set=utf8mb4 grocerylist -e "source C:/Users/User/Downloads/montelgdb.sql"
```

3. Vérifier l'état du schéma par rapport au code :

```powershell
php bin/console doctrine:migrations:status
php bin/console doctrine:schema:update --dump-sql
```

- Si `migrations:status` annonce des migrations **New** et que `schema:update --dump-sql` ne montre pas les colonnes correspondantes, c'est que la prod les a déjà : marquer ces migrations comme exécutées **en local** sans les jouer :
  `php bin/console doctrine:migrations:version "DoctrineMigrations\VersionXXXXXXXXXXXXXX" --add -n`
- Sinon, jouer les migrations : `php bin/console doctrine:migrations:migrate -n`.

> Écarts connus entre la prod et le code (sans impact) : colonne `user.apitoken` présente en prod mais plus mappée, et index de `messenger_messages` optimisé par Symfony 7.4.

> Le dump contient de vraies données (e-mails, mots de passe hashés) : ne pas le committer, ne pas le partager. On se connecte avec ses identifiants de prod.

> Les fichiers uploadés (images de recettes, `public/images/…`) ne sont pas dans le dump : en local, ces images apparaissent cassées tant qu'elles ne sont pas copiées depuis le serveur.

---

## 5. Base propre (sans données de prod) avec les fixtures

```powershell
php bin/console doctrine:database:drop --force --if-exists
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
```

Résultat : 2 utilisateurs (admin + utilisateur standard, identifiants dans `src/DataFixtures/UserFixtures.php`), ~270 ingrédients, rayons, catégories et listes d'exemple.

Pour tester sans toucher à la base principale, préfixer avec une autre base :

```powershell
$env:DATABASE_URL="mysql://root:@127.0.0.1:3306/grocerylist_scratch?serverVersion=8.4.13&charset=utf8mb4"
# … mêmes commandes …
Remove-Item Env:DATABASE_URL
```

### Base de test (PHPUnit)

```powershell
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:schema:create --env=test
php vendor/bin/phpunit
```

---

## 6. Relance « clean »

À faire quand quelque chose semble incohérent (après un changement de branche, une mise à jour de dépendances, etc.) :

```powershell
# 1. Arrêter le serveur PHP (Ctrl+C)
# 2. Dépendances à l'identique du lock
composer install
npm ci
# 3. Caches Symfony
Remove-Item -Recurse -Force var\cache
php bin/console cache:clear
# 4. Assets
npm run build
# 5. Vérifications
php bin/console lint:container
php bin/console doctrine:schema:validate
# 6. Relancer
php -S 127.0.0.1:8000 -t public
```

Puis vider le cache du navigateur (§ 7).

> Ne pas lancer `php bin/console asset-map:compile` en local : il crée `public/assets/` qui court-circuite AssetMapper en dev. Si ce dossier existe, le supprimer.

---

## 7. Dépannage

| Symptôme | Cause / solution |
|---|---|
| `php` / `composer` / `mysql` « non reconnu » | PATH pas rechargé : fermer et rouvrir VS Code. |
| Image ou JS cassé (`Unexpected token '<'` dans la console) alors que le serveur répond 200 | Le navigateur a gardé en cache une version corrompue (les fichiers `/assets/*` sont servis en `immutable` 7 jours). F12 → clic droit sur Actualiser → **« Vider le cache et effectuer une actualisation forcée »**, ou `Ctrl+Maj+Suppr` → « Images et fichiers en cache ». Un simple `Ctrl+F5` ne suffit pas. |
| Messages `Deprecated: …` en haut des pages | Dépendance non compatible PHP 8.4 : vérifier `error_reporting` dans `php.ini` et que Symfony est bien en 7.4 (`php bin/console about`). |
| `SQLSTATE[HY000] [2002]` / connexion refusée | MySQL n'est pas démarré (§ 2). |
| `Unknown database 'grocerylist_test'` dans les tests | Créer la base de test (§ 5). |
| `lexik:jwt:generate-keypair` → `error:80000003` | `OPENSSL_CONF` absent : le définir vers `extras\ssl\openssl.cnf` du dossier PHP. Clés générées dans `config/jwt/` (non versionnées). |
| Port 8000 déjà pris | `php -S 127.0.0.1:8001 -t public`, ou trouver le process : `Get-NetTCPConnection -LocalPort 8000`. |
| « Invalid CSRF token » à la connexion | Une recette Symfony a recréé `config/packages/csrf.yaml` (CSRF stateless) : le supprimer, le projet n'a pas le contrôleur JS associé. |
| `config/reference.php` apparaît modifié dans git | Régénéré par Symfony à chaque `cache:clear` (fins de ligne) : `git checkout -- config/reference.php`. |

---

## 8. Mettre à jour les dépendances

```powershell
git switch -c chore/deps-update
composer update --with-all-dependencies
composer audit
php bin/console cache:clear
php bin/console lint:container
php vendor/bin/phpunit
npm update && npm run build
```

Points d'attention :

- Symfony est contraint à `7.4.*` dans `composer.json` (champ `require` et `extra.symfony.require`). Passer à une autre version mineure/majeure = remplacer toutes ces occurrences.
- Après `composer update`, relire les fichiers créés par les recettes (`git status`) — en particulier `config/packages/csrf.yaml` (voir § 7).
- `doctrine/data-fixtures` 2.x : `getReference()` exige la classe en 2ᵉ argument (`$this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class)`).
- Recharger les fixtures sur une base jetable (§ 5) pour vérifier qu'elles passent toujours.
