
# Projet Ecoride

Description:

La startup française "EcoRide", a pour objectif de réduire l'impact 
environnemental des déplacements en encourageant le covoiturage. EcoRide prône une 
approche écologique son ambition est de devenir la principale plateforme de covoiturage pour les 
voyageurs soucieux de l'environnement et ceux qui recherchent une solution économique 
pour leurs déplacements. 
La plateforme de covoiturage doit 
gérer uniquement les déplacements en voitures.


## Prérequis

-PHP 8 ou supérieur

-Docker / XAMP / WAMP ou autres

-MySql pour l'écriture de la base de données

-MongoDB pour base de donnée non SQL

## Installation

Cloner le projet:

```bash
git clone https://github.com/Ccel85/Ecoride
```
Installer les dépendances front :

```bash
npm install
```

### Installation de la base de donnée

Executer les scripts SQL de sql/data.sql

Configurer MongoDB Atlas : renseigner l'URI dans le fichier .env (MONGO_URI)

Adapter les infos dans le fichier .env

### Installation des dépendances

```bash
  npm install
```

### Démarrer le serveur

```bash
  npm run start
```

## Utilisation du serveur WAMP64

Pour un usage sur WAMP le projet doit être cloner dans le dossier

```bash
C:\wamp64\www
```

Lancer le serveur wamp64

puis acceder au site par le lien ci dessous:

http://localhost/Ecoride/home

## Documentation

[Documentation](https://linktodocumentation)


## Architecture

### MVC

-   Structure `app/` : `controllers/`, `models/`, `views/`, `functions/`
-   `public/` : racine propre avec `index.php` central
-   Gestion hybride : modèles SQL via PDO (MySQL) et modèle Avis (MongoDB)

### Autoload

-   Autoload via Composer (`vendor/`, `composer.json`, `dump-autoload`)
-   Chargement automatique des classes avec `require 'vendor/autoload.php'`

### Débogage

-   Whoops installé et activé (`index.php`) pour la gestion des erreurs

### Vue & Layout

-   Fonction `render()` (dans `functions/view.php`) pour charger dynamiquement les pages
-   Layout unique (`layout.php`) avec `html`, `head`, `body` centralisés et dynamiques

## Documentation technique

-   [Maquettes Figma](https://www.figma.com/design/wzlnTb3rpsE1tW39XHNRj9/Maquettage-Ecoride)
-   [Diagramme d’utilisation](https://www.figma.com/design/tDpcbYwymMGQ1bRDxAunYQ/Diagramme-d-utilisation-Ecoride)
-   [Diagramme de classe](https://www.figma.com/design/UErDXx2fShe8iPASCSTqLB/diagramme-classe-Ecoride)
-   [MCP - Modèle de Conceptualisation du Projet](https://www.figma.com/design/FiuUpMhBEJEVa6j3rrmASP/MCP-Ecoride)
-   [Diagramme de séquence](https://www.figma.com/design/p2iUH1N3JGgNAPVyS23V2m/Diagramme-sequence-Ecoride)

## Configuration

### `.env`

```.env
DB_HOST = 127.0.0.1
DB_NAME = ecoride
DB_USER = rootExemple
DB_PASS = rootExemple


APP_ENV = local
DEBUG = true
MONGO_URI = mongodb+srv://user:pass@cluster0.xxxx.mongodb.net/Ecoride
```

-   Séparation des infos sensibles
-   Adaptable pour un déploiement en production

### `config/config.php`

-   Charge `.env`
-   Active le mode `DEBUG`
-   Définit la constante `APP_ENV`
-   Gère l’absence de fichier de config

### `.htaccess`

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]

<FilesMatch "\.(ini|env|sql|log|sh|bak|htaccess)$">
    Order allow,deny
    Deny from all
</FilesMatch>

ErrorDocument 404 /404.html
ErrorDocument 403 /403.html
AddDefaultCharset UTF-8
```

-   Redirection vers `index.php` (MVC)
-   Protection des fichiers sensibles
-   Personnalisation des erreurs

## Connection à la base

```php
use App\Models\ConnexionDb;

$pdo = ConnexionDb::getPdo();
```

-   Connexion centralisée via `ConnexionDb`
-   Requêtes prêtes à l’emploi avec PDO

## Test


Le projet inclut une configuration minimale de tests unitaires avec [PHPUnit](https://phpunit.de/).

### Mise en place

-   Installation de PHPUnit via Composer :
    ```bash
    composer require --dev phpunit/phpunit
    ```
-   Lancement des tests :
    ```bash
    ./vendor/bin/phpunit
    ```
-   Resultats attendu :
    Connexion Db
    ✔ Connexion db retourne un p d o
    User
    ✔ Create user
    OK (2 tests, 2 assertions)


## Déploiement

### Hébergement O2Switch

-   L'application est déployée sur [O2Switch](https://www.o2switch.fr/).
-   PHP 8.3+ et MySQL sont nativement supportés par l’hébergement.
-   Déploiement via FTP ou Git (branche principale).

### Étapes de déploiement

1. **Préparer le projet**

    - Vérifier que `.env` contient les identifiants de la base de données de production (MySQL + MongoDB Atlas).
    - Vérifier que `APP_ENV = prod` et `DEBUG = false`.

2. **Transférer les fichiers**

    - Envoyer les fichiers du projet dans le répertoire `www/` via FTP ou Git.
    - `public/` doit être utilisé comme racine du site.

3. **Configurer la base de données**

    - Importer les fichiers SQL présents dans `/database/sql` dans phpMyAdmin (O2Switch).
    - Vérifier les accès utilisateurs MySQL.

4. **Vérifier la configuration Apache**
    - `.htaccess` redirige toutes les requêtes vers `public/index.php`.
    - Protection des fichiers sensibles activée.


### Lien utiles

-   [Documentation O2Switch](https://www.o2switch.fr/faq)
-   [phpMyAdmin O2Switch](https://phpmyadmin.o2switch.net/)

## Bonnes pratiques

-   Configuration centralisée et sécurisée
-   Séparation environnement local / prod
-   Connexion BDD fiable via PDO
-   Architecture MVC propre et maintenable
-   Sécurisation via tokens CSRF sur tous les formulaires
-   Gestion complète des crédits (transactions en attente, redistribution, remboursements)
-   Validation des avis par les employés avant publication (MongoDB)
-   Gestion des comptes suspendus (utilisateurs et employés)
-   Projet prêt pour la mise en ligne

## RESSOURCES

-   [GitHub](https://github.com/Alyaesub/Ecoride.git)
-   [Trello](https://trello.com/invite/b/674dfbcb0c1b62a2c6577364/ATTI5bbb7e636c9c9aac07b4b2c4cb037469670CFCA8/ecf-ecoride)
-   [Documentation](https://github.com/Alyaesub/Ecoride/wiki)

## Auteur

- [@Ccel85](https://www.github.com/Ccel85)

